<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobOpening;
use App\Models\Facility;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CareersController extends Controller
{
// Admin view for managing job openings and applications    
public function indexAll(Request $request)
    {
        $facilities = Facility::orderBy('name')->get();
        $facilityId = $request->query('facility_id');
        $jobOpenings = collect([]);
        
        if ($facilityId) {
            $facility = Facility::find($facilityId);
            if ($facility) {
                $jobOpenings = $facility->jobOpenings()->latest()->get();
            }
        }
        
        return view('admin.facilities.webcontents.careers', compact('facilities', 'facilityId', 'jobOpenings'));
    }

    public function templates()
    {
        return view('admin.facilities.webcontents.careers-templates');
    }

    public function getTemplatesForTitle(Request $request)
    {
        $title = $request->input('title');
        $templates = \App\Models\JobDescriptionTemplate::where('title', $title)->orWhereNull('title')->get();
        return response()->json($templates);
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
        ]);
        $data['created_by'] = $request->user() ? $request->user()->id : null;
        $template = \App\Models\JobDescriptionTemplate::create($data);
        return response()->json($template);
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = \App\Models\JobDescriptionTemplate::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
        ]);
        $template->update($data);
        return response()->json($template);
    }

    public function destroyTemplate($id)
    {
        $template = \App\Models\JobDescriptionTemplate::findOrFail($id);
        $template->delete();
        return response()->json(['success' => true]);
    }

    public function getJobDescriptionsByPosition($positionId)
    {
        $descriptions = \App\Models\JobDescription::where('position_id', $positionId)->get(['id', 'title', 'description']);
        return response()->json($descriptions);
    }

    public function index(Request $request)
    {
        $facilities = Facility::all();
        $facilityId = $request->get('facility_id');
        $jobOpenings = $facilityId ? JobOpening::where('facility_id', $facilityId)->get() : collect();
        
        // Format job openings for JavaScript with proper date formatting
        $formattedJobOpenings = $jobOpenings->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'detailed_description' => $job->detailed_description,
                'department' => $job->department,
                'employment_type' => $job->employment_type,
                'posted_at' => $job->posted_at ? Carbon::parse($job->posted_at)->format('Y-m-d') : null,
                'expires_at' => $job->expires_at ? Carbon::parse($job->expires_at)->format('Y-m-d') : null,
                'active' => (bool) $job->active,
            ];
        });
        
        return view('admin.facilities.webcontents.careers', compact('facilities', 'facilityId', 'jobOpenings', 'formattedJobOpenings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'detailed_description' => 'nullable|string',
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
            'detailed_description' => 'nullable|string',
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

    // Download resume file for job application
    public function downloadResume(JobOpening $jobOpening, JobApplication $jobApplication)
    {
        if (!$jobApplication->resume_path || !Storage::disk('public')->exists($jobApplication->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }

        $filePath = Storage::disk('public')->path($jobApplication->resume_path);
        $fileName = strtolower($jobApplication->first_name) . '_' . strtolower($jobApplication->last_name) . '_resume.pdf';
        
        return response()->download($filePath, $fileName);
    }

    // Preview resume file for job application
    public function previewResume(JobOpening $jobOpening, JobApplication $jobApplication)
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
}
