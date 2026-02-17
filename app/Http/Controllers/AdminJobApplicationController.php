<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Facility;
use Illuminate\Support\Facades\Storage;

class AdminJobApplicationController extends Controller
{
    public function index()
    {
        $statuses = ['pending', 'reviewed', 'interview', 'hired', 'rejected'];
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
            $query->where(function ($builder) use ($search) {
                $builder->where('desired_position', 'like', "%{$search}%")
                    ->orWhereHas('jobOpening', function ($jobQuery) use ($search) {
                        $jobQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $jobApplications = $query->paginate(15)->withQueryString();

        return view('admin.job-applications.index', compact('jobApplications', 'facilities', 'statuses'));
    }

    public function show(JobApplication $jobApplication)
    {
        // Return a view for showing a job application
        return view('admin.job-applications.show', compact('jobApplication'));
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
            'status' => 'required|in:pending,reviewed,interview,hired,rejected',
        ]);

        $jobApplication->update([
            'status' => $validated['status'],
        ]);

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
}
