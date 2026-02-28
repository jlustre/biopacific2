<?php

namespace App\Http\Controllers\Admin;

use \Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\JobOpening;
use App\Models\Position;

class JobOpeningController extends Controller
{
    public function index(Facility $facility)
    {
        $jobs = $facility->jobOpenings()->latest()->get();
        return view('admin.facilities.job_openings', compact('facility', 'jobs'));
    }

    public function store(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'title_other' => 'nullable|string|max:255',
            'employment_type' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'reporting_to' => 'required|string|max:255',
            'posted_at' => 'required|date',
            'status' => 'required|string|in:open,closed,filled',
            'description' => 'required|string',
            'active' => 'nullable|boolean',
        ]);
        // If 'Other' is selected, use the value from title_other
        if ($data['title'] === 'Other' && !empty($data['title_other'])) {
            $data['title'] = $data['title_other'];
        }
        unset($data['title_other']);
        $data['active'] = $request->input('active', 0) == '1';
        $data['created_by'] = $request->user() ? $request->user()->id : null;
        $facility->jobOpenings()->create($data);
        return redirect()->back()->with('success', 'Job opening created successfully.');
    }

    public function update(Request $request, Facility $facility, JobOpening $jobOpening)
    {
        $data = $request->validate([
              'title' => 'required|string|max:255',
            'reporting_to' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);
        $jobOpening->update($data);
        return redirect()->back()->with('success', 'Job opening updated.');
    }

    public function destroy(Facility $facility, JobOpening $jobOpening)
    {
        $jobOpening->delete();
        return redirect()->back()->with('success', 'Job opening deleted.');
    }

    public function edit(Facility $facility, JobOpening $jobOpening)
    {
        // Load reporting_to positions from database
        $reportingToPositions = Position::select('title')
            ->distinct()
            ->orderBy('title')
            ->pluck('title')
            ->toArray();
        
        // Add common reporting titles
        $commonTitles = ['Administrator', 'Director of Nursing', 'Charge Nurse', 'Medical Director', 'Social Services Director'];
        foreach ($commonTitles as $title) {
            if (!in_array($title, $reportingToPositions)) {
                $reportingToPositions[] = $title;
            }
        }
        
        // Add "Other" option
        if (!in_array('Other', $reportingToPositions)) {
            $reportingToPositions[] = 'Other';
        }
        
        sort($reportingToPositions);
        
        return view('admin.facilities.edit_job_opening', compact('facility', 'jobOpening', 'reportingToPositions'));
    }

    public function show(Facility $facility, JobOpening $jobOpening)
    {
        return view('admin.facilities.show_job_opening', compact('facility', 'jobOpening'));
    }

    public function getTemplatesForTitle(Request $request)
    {
        $title = trim($request->input('title'));
        if ($title === '' || $title === null) {
            // Show all templates if filter is empty
            $templates = \App\Models\JobDescriptionTemplate::all();
        } else {
            // Case-insensitive, trimmed match
            $templates = \App\Models\JobDescriptionTemplate::whereRaw('LOWER(TRIM(title)) = ?', [strtolower($title)])
                ->get();
        }
        // Debug: log all template titles being returned
        logger()->info('Template titles returned:', $templates->pluck('title')->toArray());
        return response()->json($templates);
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:job_description_templates,name',
            'title' => 'required|string|max:255',
            'contents' => 'nullable|string',
        ]);
        $data['created_by'] = $request->user() ? $request->user()->id : null;
        $template = \App\Models\JobDescriptionTemplate::create($data);
        return response()->json($template);
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = \App\Models\JobDescriptionTemplate::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:job_description_templates,name,' . $id,
            'title' => 'required|string|max:255',
            'contents' => 'nullable|string',
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

    public function toggleActive(Request $request, Facility $facility)
    {
        $jobId = $request->input('job_id');
        $job = $facility->jobOpenings()->findOrFail($jobId);
        $job->update(['active' => !$job->active]);
        return redirect()->back()->with('success', $job->active ? 'Job activated successfully.' : 'Job deactivated successfully.');
    }

    public function changeStatus(Request $request, Facility $facility)
    {
        $jobId = $request->input('job_id');
        $job = $facility->jobOpenings()->findOrFail($jobId);
        $newStatus = $job->status === 'open' ? 'closed' : 'open';
        $job->update(['status' => $newStatus]);
        return redirect()->back()->with('success', 'Job status changed to ' . ucfirst($newStatus) . '.');
    }

    public function deleteJobOpening(Request $request, Facility $facility)
    {
        $jobId = $request->input('job_id');
        $job = $facility->jobOpenings()->findOrFail($jobId);
        $title = $job->title;
        $job->delete();
        return redirect()->back()->with('success', "Job '$title' deleted successfully.");
    }

    public function deleteTemplateViaForm(Request $request, Facility $facility)
    {
        $templateId = $request->input('template_id');
        $template = \App\Models\JobDescriptionTemplate::findOrFail($templateId);
        
        // Check if user is the creator
        if ($template->created_by !== $request->user()->id) {
            return redirect()->back()->with('error', 'Only the template creator can delete this template.');
        }
        
        $name = $template->name;
        $template->delete();
        return redirect()->back()->with('success', "Template '$name' deleted successfully.");
    }

    public function updateTemplateViaForm(Request $request, Facility $facility)
    {
        $templateId = $request->input('template_id');
        $template = \App\Models\JobDescriptionTemplate::findOrFail($templateId);
        // Check if user is the creator
        if ($template->created_by !== $request->user()->id) {
            return redirect()->back()->with('error', 'Only the template creator can update this template.');
        }
        $data = $request->validate([
            'template_name' => 'required|string|max:255|unique:job_description_templates,name,' . $templateId,
            'template_position_title' => 'required|string|max:255',
            'template_contents' => 'nullable|string',
        ]);
        $positionId = Position::where('title', $data['template_position_title'])->value('id');
        if (!$positionId) {
            return redirect()->back()->with('error', 'Template position is required. Please select a valid job title.');
        }
        $template->update([
            'name' => $data['template_name'],
            'position_id' => $positionId,
            'contents' => $data['template_contents'] ?? '',
        ]);
        return redirect()->back()->with('success', "Template '{$template->name}' updated successfully.");
    }

    public function saveTemplateViaForm(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'template_name' => 'required|string|max:255|unique:job_description_templates,name',
            'template_position_title' => 'required|string|max:255',
            'template_contents' => 'nullable|string',
        ]);

        $positionId = Position::where('title', $data['template_position_title'])->value('id');
        if (!$positionId) {
            return redirect()->back()->with('error', 'Template position is required. Please select a valid job title.');
        }

        $template = \App\Models\JobDescriptionTemplate::create([
            'name' => $data['template_name'],
            'position_id' => $positionId,
            'contents' => $data['template_contents'] ?? '',
            'created_by' => $request->user() ? $request->user()->id : null,
        ]);

        return redirect()->back()->with('success', "Template '{$template->name}' saved successfully.");
    }

    // Get job data as JSON for editing
    public function getEditData(Facility $facility, JobOpening $jobOpening)
    {
        return response()->json([
            'id' => $jobOpening->id,
            'title' => $jobOpening->title,
            'employment_type' => $jobOpening->employment_type,
            'department' => $jobOpening->department,
            'reporting_to' => $jobOpening->reporting_to,
            'posted_at' => $jobOpening->posted_at ? $jobOpening->posted_at->format('Y-m-d') : '',
            'status' => $jobOpening->status,
            'description' => $jobOpening->description,
            'active' => $jobOpening->active ? 1 : 0,
        ]);
    }

    // Update job via modal form
    public function updateViaForm(Request $request, Facility $facility, JobOpening $jobOpening)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'employment_type' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'reporting_to' => 'required|string|max:255',
            'posted_at' => 'required|date',
            'status' => 'required|string|in:open,closed,filled',
            'description' => 'required|string',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->input('active', 0) == '1';
        $jobOpening->update($data);
        return redirect()->back()->with('success', "Job '{$jobOpening->title}' updated successfully.");
    }
}
