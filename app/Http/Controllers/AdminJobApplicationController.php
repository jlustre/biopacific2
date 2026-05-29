<?php

namespace App\Http\Controllers;

use App\Mail\PreEmploymentMail;
use App\Models\EmployeeChecklist;
use App\Models\Facility;
use App\Models\JobApplication;
use App\Models\RegistrationCode;
use App\Models\User;
use App\Support\RegistrationCodeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminJobApplicationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = $request->user();
        $statuses = ['pending', 'reviewed', 'interview', 'pre-employment', 'hired', 'rejected'];
        $isGlobalAdmin = $user->hasRole(['admin', User::superAdminRoleName(), 'rdhr']);
        $scopedFacilityId = (! $isGlobalAdmin && $user->facility_id)
            ? (int) $user->facility_id
            : null;
        $canFilterFacilities = $isGlobalAdmin;

        $query = JobApplication::with(['jobOpening.facility', 'user'])->orderByDesc('created_at');

        if ($scopedFacilityId) {
            $query->whereHas('jobOpening', function ($builder) use ($scopedFacilityId) {
                $builder->where('facility_id', $scopedFacilityId);
            });
        } elseif ($request->filled('facility')) {
            $query->whereHas('jobOpening', function ($builder) use ($request) {
                $builder->where('facility_id', $request->facility);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('jobOpening', function ($jobQuery) use ($search) {
                        $jobQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $jobApplications = $query->paginate(15)->withQueryString();

        $facilities = $canFilterFacilities
            ? Facility::orderBy('name')->get()
            : collect();

        $scopedFacility = $scopedFacilityId
            ? Facility::find($scopedFacilityId)
            : null;

        $registrationCodeService = app(RegistrationCodeService::class);
        $canGenerateRegistrationCodes = $registrationCodeService->canGenerateCodes($user);

        $activeRegistrationCodes = collect();
        if ($canGenerateRegistrationCodes && $jobApplications->isNotEmpty()) {
            $activeRegistrationCodes = RegistrationCode::query()
                ->whereIn('job_application_id', $jobApplications->pluck('id'))
                ->whereNull('used_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderByDesc('created_at')
                ->get()
                ->unique('job_application_id')
                ->keyBy('job_application_id');
        }

        return view('admin.job-applications.index', compact(
            'jobApplications',
            'facilities',
            'statuses',
            'canFilterFacilities',
            'scopedFacility',
            'canGenerateRegistrationCodes',
            'activeRegistrationCodes',
            'registrationCodeService',
        ));
    }

    public function show(JobApplication $jobApplication)
    {
        $this->authorize('view', $jobApplication);

        $applicantUser = User::where('email', $jobApplication->email)->first();
        $checklistItems = collect();

        if ($applicantUser) {
            $checklistItems = EmployeeChecklist::where('user_id', $applicantUser->id)
                ->orderBy('id')
                ->get();
        }

        $registrationCodeService = app(RegistrationCodeService::class);
        $canGenerateRegistrationCodes = $registrationCodeService->canGenerateCodes(auth()->user());
        $hasPortalUser = $registrationCodeService->applicantHasPortalUser($jobApplication);
        $pendingRegistrationCode = RegistrationCode::query()
            ->where('job_application_id', $jobApplication->id)
            ->whereNull('used_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->first();

        return view('admin.job-applications.show', compact(
            'jobApplication',
            'applicantUser',
            'checklistItems',
            'canGenerateRegistrationCodes',
            'hasPortalUser',
            'pendingRegistrationCode',
            'registrationCodeService',
        ));
    }

    public function generateRegistrationCode(Request $request, JobApplication $jobApplication)
    {
        $this->authorize('update', $jobApplication);

        $registrationCodeService = app(RegistrationCodeService::class);

        if (! $registrationCodeService->canGenerateCodes($request->user())) {
            abort(403, 'You are not authorized to generate registration codes.');
        }

        try {
            $registrationCode = $registrationCodeService->issueApplicantRegistrationCode(
                $jobApplication,
                $request->user()
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            Log::error('Failed to generate applicant registration code', [
                'job_application_id' => $jobApplication->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to generate the registration code. Please try again.');
        }

        return redirect()->back()->with(
            'success',
            'Registration code ' . $registrationCode->code . ' was emailed to ' . $registrationCode->email . '.'
        );
    }

    public function destroy(JobApplication $jobApplication)
    {
        $this->authorize('delete', $jobApplication);

        if ($jobApplication->resume_path && Storage::disk('public')->exists($jobApplication->resume_path)) {
            Storage::disk('public')->delete($jobApplication->resume_path);
        }

        $jobApplication->delete();

        return redirect()
            ->route('admin.job-applications.index')
            ->with('success', 'Job application deleted successfully.');
    }

    public function updateStatus(Request $request, JobApplication $jobApplication)
    {
        $this->authorize('update', $jobApplication);

        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,interview,pre-employment,hired,rejected',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'pre-employment' && empty($jobApplication->applicant_code)) {
            $updateData['applicant_code'] = $this->generateApplicantCode();
        }

        $jobApplication->update($updateData);
        $jobApplication->refresh();

        if ($validated['status'] === 'pre-employment') {
            if (empty($jobApplication->applicant_code)) {
                $jobApplication->applicant_code = $this->generateApplicantCode();
                $jobApplication->save();
            }

            $registrationCodeService = app(RegistrationCodeService::class);
            $registrationCode = null;

            if (! $registrationCodeService->applicantHasPortalUser($jobApplication)
                && $registrationCodeService->canGenerateCodes($request->user())) {
                try {
                    $registrationCode = $registrationCodeService->generateForApplicant(
                        $jobApplication,
                        $request->user()
                    );
                } catch (\Throwable $exception) {
                    Log::warning('Could not auto-issue applicant registration code', [
                        'job_application_id' => $jobApplication->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            Mail::to($jobApplication->email)->send(new PreEmploymentMail($jobApplication, $registrationCode));
        }

        return redirect()
            ->route('admin.job-applications.show', $jobApplication)
            ->with('success', 'Application status updated.');
    }

    public function downloadResume(JobApplication $jobApplication)
    {
        $this->authorize('view', $jobApplication);

        if (! $jobApplication->resume_path) {
            abort(404, 'Resume not found.');
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($jobApplication->resume_path)) {
            abort(404, 'Resume not found.');
        }

        $path = $disk->path($jobApplication->resume_path);
        $fileName = strtolower($jobApplication->first_name) . '_' . strtolower($jobApplication->last_name) . '_resume.pdf';

        return response()->download($path, $fileName);
    }

    public function previewResume(JobApplication $jobApplication)
    {
        $this->authorize('view', $jobApplication);

        if (! $jobApplication->resume_path) {
            abort(404, 'Resume not found.');
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($jobApplication->resume_path)) {
            abort(404, 'Resume not found.');
        }

        $path = $disk->path($jobApplication->resume_path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . basename($jobApplication->resume_path) . '"',
        ]);
    }

    private function generateApplicantCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (JobApplication::where('applicant_code', $code)->exists());

        return $code;
    }
}
