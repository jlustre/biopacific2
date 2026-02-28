<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeChecklist;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\Facility;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Mail\PreEmploymentMail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AdminJobApplicationController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $statuses = ['pending', 'reviewed', 'interview', 'pre-employment', 'hired', 'rejected'];
        $facilities = Facility::orderBy('name')->get();

        $query = JobApplication::with(['jobOpening.facility'])->orderByDesc('created_at');

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('facility')) {
            $facilityId = request('facility');
            $query->whereHas('jobOpening', function ($builder) use ($facilityId) {
                $builder->where('facility_id', $facilityId);
            });
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->whereHas('jobOpening', function ($jobQuery) use ($search) {
                $jobQuery->where('title', 'like', "%{$search}%");
            });
        }

        $jobApplications = $query->paginate(15)->withQueryString();

        return view('admin.job-applications.index', compact('jobApplications', 'facilities', 'statuses'));
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
        // Handle deleting a job application
        // Example: $jobApplication->delete();
        return redirect()->route('admin.job-applications.index');
    }

    public function updateStatus(Request $request, JobApplication $jobApplication)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,interview,pre-employment,hired,rejected',
        ]);

        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,interview,pre-employment,hired,rejected',
        ]);

        $updateData = ['status' => $validated['status']];

        // Always ensure applicant_code is set for pre-employment
        if ($validated['status'] === 'pre-employment' && empty($jobApplication->applicant_code)) {
            $updateData['applicant_code'] = $this->generateApplicantCode();
        }

        $jobApplication->update($updateData);

        // Reload the jobApplication to get the latest applicant_code
        $jobApplication->refresh();

        if ($validated['status'] === 'pre-employment') {
            // If applicant_code is still missing, generate and save it
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
        if (!$jobApplication->resume_path) {
            abort(404, 'Resume not found.');
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($jobApplication->resume_path)) {
            abort(404, 'Resume not found.');
        }

        $path = $disk->path($jobApplication->resume_path);
        $fileName = strtolower($jobApplication->first_name) . '_' . strtolower($jobApplication->last_name) . '_resume.pdf';
        return response()->download($path, $fileName);
    }

    public function previewResume(JobApplication $jobApplication)
    {
        if (!$jobApplication->resume_path) {
            abort(404, 'Resume not found.');
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($jobApplication->resume_path)) {
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
