<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\Auth;

class JobListingManager extends Component
{
    #[Locked]
    public $facilityId;

    public $title = '';
    public $description = '';
    public $reporting_to = '';
    public $department = '';
    public $employment_type = '';
    public $posted_at = '';
    public $status = 'open';
    public $active = true;

    public array $positions = [];
    public array $departments = [];
    public array $supervisors = [];

    // Template management
    public array $templates = [];
    public $selectedTemplateId = '';
    public $showSaveTemplateModal = false;
    public $templateName = '';
    public $saveAsNewTemplate = false;

    public $successMessage = '';
    public $errorMessage = '';

    public function mount($facility)
    {
        // Check for facility_id in GET request
        $requestFacilityId = request()->query('facility_id');
        if ($requestFacilityId) {
            $this->facilityId = $requestFacilityId;
            session(['facility_id' => $this->facilityId]);
        } elseif (!$facility) {
            $facilityId = session('facility_id');
            $this->facilityId = $facilityId;
        } else {
            $this->facilityId = is_object($facility) ? $facility->id : $facility;
            session(['facility_id' => $this->facilityId]);
        }
        $this->posted_at = date('Y-m-d');
        
        // Load positions
        $this->positions = \App\Models\Position::orderBy('title')->pluck('title', 'id')->toArray();
        
        // Load departments
        $this->departments = \App\Models\Department::where('type', 'facility')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
        
        // Load supervisors
        $this->supervisors = \App\Models\Position::where('supervisor_role', 1)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
        
        // Load templates
        $this->loadTemplates();
        
        
        // Populate success and error messages from session flash data
        if (session()->has('success')) {
            $this->successMessage = session('success');
        }
        
        if (session()->has('error')) {
            $this->errorMessage = session('error');
        }
    }

    private function loadTemplates()
    {
        $this->templates = \App\Models\JobDescriptionTemplate::select('id', 'name', 'contents', 'created_by')
            ->with('creator:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id, 
                'name' => $t->name, 
                'contents' => $t->contents,
                'created_by' => $t->created_by,
                'creator_name' => $t->creator?->name ?? 'Unknown'
            ])
            ->toArray();
    }

    public function addJob()
    {
        $this->errorMessage = 'DEBUG: addJob called';
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reporting_to' => 'required|string|max:255',
            'employment_type' => 'required|string|max:100',
            'posted_at' => 'required|date',
        ]);

        try {
            \App\Models\Facility::findOrFail($this->facilityId)->jobOpenings()->create([
                'title' => $this->title,
                'description' => $this->description,
                'reporting_to' => $this->reporting_to,
                'department' => $this->department ?: null,
                'employment_type' => $this->employment_type,
                'posted_at' => $this->posted_at,
                'status' => $this->status,
                'active' => $this->active ? 1 : 0,
                'created_by' => Auth::id(),
            ]);

            // Redirect to the correct facility job openings route to preserve context
            return redirect()->route('admin.facility.job_openings', ['facility' => $this->facilityId])
                ->with('success', 'Job listing created successfully!');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error creating job: ' . $e->getMessage();
        }
    }

    public function resetForm()
    {
        $this->reset(['title', 'description', 'reporting_to', 'department', 'employment_type']);
        $this->posted_at = date('Y-m-d');
        $this->status = 'open';
        $this->active = true;
    }

    public function loadTemplate($templateId)
    {
        $template = \App\Models\JobDescriptionTemplate::find($templateId);
        if ($template) {
            $this->description = $template->contents;
            $this->successMessage = 'Template loaded! You can now customize it.';
        }
    }

    public function openSaveTemplateModal()
    {
        if (!$this->description) {
            $this->errorMessage = 'Please write a description first';
            return;
        }
        $this->showSaveTemplateModal = true;
        $this->templateName = '';
        $this->saveAsNewTemplate = false;
    }

    public function saveTemplate()
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
        ]);

        try {
            \App\Models\JobDescriptionTemplate::create([
                'name' => $this->templateName,
                'contents' => $this->description,
                'position_id' => null,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.facility.job_openings', ['facility' => $this->facilityId])
                ->with('success', 'Template saved successfully!');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error saving template: ' . $e->getMessage();
        }
    }

    public function deleteTemplate($templateId)
    {
        try {
            \App\Models\JobDescriptionTemplate::find($templateId)?->delete();
            return redirect()->route('admin.facility.job_openings', ['facility' => $this->facilityId])
                ->with('success', 'Template deleted');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error deleting template: ' . $e->getMessage();
        }
    }

    public function closeSaveTemplateModal()
    {
        $this->showSaveTemplateModal = false;
    }

    public function toggleActive($jobId)
    {
        $job = \App\Models\JobOpening::find($jobId);
        if ($job) {
            $job->active = !$job->active;
            $job->save();
            $this->successMessage = $job->active ? 'Job activated' : 'Job inactivated';
        }
    }

    public function changeStatus($jobId, $newStatus)
    {
        $job = \App\Models\JobOpening::find($jobId);
        if ($job && in_array($newStatus, ['open', 'closed', 'filled'])) {
            $job->status = $newStatus;
            $job->save();
            $this->successMessage = 'Job status updated to ' . ucfirst($newStatus);
        }
    }

    public function deleteJob($jobId)
    {
        $job = \App\Models\JobOpening::find($jobId);
        if ($job) {
            $job->delete();
            return redirect()->route('admin.facility.job_openings', ['facility' => $this->facilityId])
                ->with('success', 'Job listing deleted');
        }
    }

    public function closeSucess()
    {
        $this->successMessage = '';
    }

    public function render()
    {
        $facility = \App\Models\Facility::find($this->facilityId);
        if (!$facility) {
            return view('livewire.job-listing-manager', [
                'facility' => null,
                'jobs' => collect(),
                'templates' => $this->templates,
                'errorMessage' => 'Facility not found. Please contact support.'
            ]);
        }
        $jobs = $facility->jobOpenings()->latest()->get();
        return view('livewire.job-listing-manager', [
            'facility' => $facility,
            'jobs' => $jobs,
            'templates' => $this->templates,
        ]);
    }
}
