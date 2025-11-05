<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Facility;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JobApplication::with(['jobOpening.facility'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhereHas('jobOpening', function ($jobQuery) use ($request) {
                      $jobQuery->where('title', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('facility')) {
            $query->whereHas('jobOpening', function ($q) use ($request) {
                $q->where('facility_id', $request->facility);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobApplications = $query->paginate(15)->appends($request->query());
        $facilities = Facility::all();
        $statuses = ['pending', 'reviewed', 'interview', 'hired', 'rejected'];

        return view('admin.job-applications.index', compact('jobApplications', 'facilities', 'statuses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(JobApplication $jobApplication)
    {
        return view('admin.job-applications.show', compact('jobApplication'));
    }

    /**
     * Update the status of the specified resource.
     */
    public function updateStatus(Request $request, JobApplication $jobApplication)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,interview,hired,rejected',
        ]);

        $jobApplication->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Application status updated successfully.');
    }

    /**
     * Download the resume file.
     */
    public function downloadResume(JobApplication $jobApplication)
    {
        if (!$jobApplication->resume_path || !Storage::disk('public')->exists($jobApplication->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }

        $filePath = Storage::disk('public')->path($jobApplication->resume_path);
        $fileName = $jobApplication->first_name . '_' . $jobApplication->last_name . '_Resume_' . pathinfo($jobApplication->resume_path, PATHINFO_EXTENSION);
        
        return response()->download($filePath, $fileName);
    }

    /**
     * Preview the resume file in browser.
     */
    public function previewResume(JobApplication $jobApplication)
    {
        if (!$jobApplication->resume_path || !Storage::disk('public')->exists($jobApplication->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }

        $filePath = Storage::disk('public')->path($jobApplication->resume_path);
        $extension = pathinfo($jobApplication->resume_path, PATHINFO_EXTENSION);
        
        // Determine mime type based on extension
        $mimeType = match(strtolower($extension)) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream'
        };
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($jobApplication->resume_path) . '"'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobApplication $jobApplication)
    {
        // Delete resume file if it exists
        if ($jobApplication->resume_path && Storage::exists($jobApplication->resume_path)) {
            Storage::delete($jobApplication->resume_path);
        }

        $jobApplication->delete();
        return redirect()->route('admin.job-applications.index')->with('success', 'Job application deleted successfully.');
    }
}