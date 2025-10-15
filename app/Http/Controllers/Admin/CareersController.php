<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobOpening;
use App\Models\Facility;
use App\Models\JobApplication;

class CareersController extends Controller
{
    public function index(Request $request)
    {
        $facilities = Facility::all();
        $facilityId = $request->get('facility_id');
        $jobOpenings = $facilityId ? JobOpening::where('facility_id', $facilityId)->get() : collect();
        return view('admin.facilities.webcontents.careers', compact('facilities', 'facilityId', 'jobOpenings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);
        JobOpening::create($data);
        return back()->with('success', 'Job opening created successfully.');
    }

    public function update(Request $request, JobOpening $jobOpening)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);
        $jobOpening->update($data);
        return back()->with('success', 'Job opening updated successfully.');
    }

    public function destroy(JobOpening $jobOpening)
    {
        $jobOpening->delete();
        return back()->with('success', 'Job opening deleted successfully.');
    }

    // Job applications listing for a job opening
    public function applications(JobOpening $jobOpening)
    {
        $applications = $jobOpening->applications;
        return view('admin.facilities.webcontents.career_applications', compact('jobOpening', 'applications'));
    }

    // Update job application status
    public function updateApplication(Request $request, JobOpening $jobOpening, JobApplication $jobApplication)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected',
        ]);
        $jobApplication->update(['status' => $data['status']]);
        return back()->with('success', 'Application status updated.');
    }

    // Delete job application
    public function destroyApplication(JobOpening $jobOpening, JobApplication $jobApplication)
    {
        $jobApplication->delete();
        return back()->with('success', 'Application deleted.');
    }

    // Serve job application details for modal (AJAX)
    public function applicationDetails(JobApplication $jobApplication)
    {
        return view('admin.facilities.webcontents.partials.application_details', compact('jobApplication'));
    }
}
