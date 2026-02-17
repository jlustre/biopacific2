<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\JobOpening;

class CareersController extends Controller
{
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

    public function index(Facility $facility)
    {
        $facilities = Facility::orderBy('name')->get();
        $facilityId = $facility->id;
        $jobOpenings = $facility->jobOpenings()->latest()->get();
        return view('admin.facilities.webcontents.careers', compact('facilities', 'facilityId', 'jobOpenings'));
    }

    public function store(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'title_other' => 'nullable|string|max:255',
            'reporting_to' => 'nullable|string|max:255',
            'description' => 'required|string',
            'job_description_template_id' => 'nullable|exists:job_description_templates,id',
            'department' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'salary_range' => 'nullable|string',
            'salary_unit' => 'nullable|in:annual,monthly,hourly',
            'active' => 'nullable|boolean',
            'status' => 'nullable|string',
        ]);
        if ($data['title'] === 'Other' && !empty($data['title_other'])) {
            $data['title'] = $data['title_other'];
        }
        unset($data['title_other']);
        $data['facility_id'] = $facility->id;
        $data['created_by'] = $request->user() ? $request->user()->id : null;
        $data['active'] = $data['active'] ?? true;
        $data['status'] = $data['status'] ?? 'active';
        JobOpening::create($data);
        return redirect()->back()->with('success', 'Job opening created.');
    }

    public function update(Request $request, JobOpening $jobOpening)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'reporting_to' => 'nullable|string|max:255',
            'description' => 'required|string',
            'job_description_template_id' => 'nullable|exists:job_description_templates,id',
            'department' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'posted_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'salary_range' => 'nullable|string',
            'salary_unit' => 'nullable|in:annual,monthly,hourly',
            'active' => 'nullable|boolean',
            'status' => 'nullable|string',
        ]);
        $jobOpening->update($data);
        return redirect()->back()->with('success', 'Job opening updated.');
    }

    public function destroy(JobOpening $jobOpening)
    {
        $jobOpening->delete();
        return redirect()->back()->with('success', 'Job opening deleted.');
    }

    public function edit(Facility $facility, JobOpening $jobOpening)
    {
        return view('admin.facilities.edit_job_opening', compact('facility', 'jobOpening'));
    }

    public function show(Facility $facility, JobOpening $jobOpening)
    {
        return view('admin.facilities.show_job_opening', compact('facility', 'jobOpening'));
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
}
