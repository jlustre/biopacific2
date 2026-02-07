<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminJobApplicationController extends Controller
{
    public function index()
    {
        // Return a view or JSON listing job applications
        return view('admin-job-applications.index');
    }

    public function show($jobApplication)
    {
        // Return a view for showing a job application
        return view('admin-job-applications.show', compact('jobApplication'));
    }

    public function destroy($jobApplication)
    {
        // Handle deleting a job application
        // Example: $jobApplication->delete();
        return redirect()->route('job-applications.index');
    }

    public function updateStatus(Request $request, $jobApplication)
    {
        // Handle updating the status of a job application
        // Example: $jobApplication->update(['status' => $request->status]);
        return redirect()->route('job-applications.index');
    }

    public function downloadResume($jobApplication)
    {
        // Handle downloading a resume for a job application
        // Example: return response()->download($jobApplication->resume_path);
        return response('Resume download');
    }

    public function previewResume($jobApplication)
    {
        // Handle previewing a resume for a job application
        // Example: return response()->file($jobApplication->resume_path);
        return response('Resume preview');
    }
}
