<?php

namespace App\Http\Controllers;

use App\Mail\PreEmploymentMail;
use App\Models\EmployeeChecklist;
use App\Models\Facility;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
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
        $isGlobalAdmin = $user->hasRole('admin');
        $scopedFacilityId = (! $isGlobalAdmin && $user->facility_id)
            ? (int) $user->facility_id
            : null;
        $canFilterFacilities = $isGlobalAdmin;

        $query = JobApplication::with(['jobOpening.facility'])->orderByDesc('created_at');

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

        return view('admin.job-applications.index', compact(
            'jobApplications',
            'facilities',
            'statuses',
            'canFilterFacilities',
            'scopedFacility'
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

        return view('admin.job-applications.show', compact('jobApplication', 'applicantUser', 'checklistItems'));
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
            Mail::to($jobApplication->email)->send(new PreEmploymentMail($jobApplication));
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
