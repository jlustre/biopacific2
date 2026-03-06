<?php

namespace App\Livewire;

use App\Models\Facility;
use App\Models\JobOpening;
use App\Models\JobDescriptionTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Locked;

class JobOpeningsManager extends Component
{
    #[Locked]
    public $facilityId;
    public $title = '';
    public $description = '';
    public $expandedJobs = [];
    public $templateName = '';
    public $templatePositionId = '';
    public $editingTemplateId = null;
    public $editingTemplateCreatorId = null;
    public $canUpdateOriginal = false;
    public $selected_template_id = '';
    public $showSaveTemplateModal = false;
    public $templates = [];
    public $positionDepartmentMap = [];
    public $positionIdMap = [];
    public $successMessage = '';
    public $created_by = '';
    public $created_by_name = '';
    public $showJobModal = false;
    public $selectedJob = [];
    public function showJobModal($jobId)
    {
        $job = JobOpening::find($jobId);
        if ($job) {
            $this->selectedJob = $job->toArray();
            $this->showJobModal = true;
        }
    }

    public function mount()
    {
        if (Auth::check()) {
            $this->created_by = Auth::user()->id;
            $this->created_by_name = Auth::user()->name;
        }
    }

    public function updatedFacilityId()
    {
        // Refill created_by and created_by_name if user changes facility (optional, for dynamic UX)
        if (Auth::check()) {
            $this->created_by = Auth::user()->id;
            $this->created_by_name = Auth::user()->name;
        }
    }


    public function toggleExpanded($jobId)
    {
        if (in_array($jobId, $this->expandedJobs)) {
            $this->expandedJobs = array_filter($this->expandedJobs, fn($id) => $id !== $jobId);
        } else {
            $this->expandedJobs[] = $jobId;
        }
    }

    public function toggleActive($jobId)
    {
        $job = JobOpening::find($jobId);
        if ($job) {
            $job->active = !$job->active;
            $job->save();
            session()->flash('success', 'Job listing ' . ($job->active ? 'activated' : 'inactivated') . ' successfully');
        }
    }

    public function changeStatus($jobId, $newStatus)
    {
        $job = JobOpening::find($jobId);
        if ($job && in_array($newStatus, ['open', 'closed'])) {
            $job->status = $newStatus;
            $job->save();
            session()->flash('success', 'Job status changed to ' . ucfirst($newStatus));
        }
    }

    public function deleteJob($jobId)
    {
        JobOpening::find($jobId)?->delete();
        session()->flash('success', 'Job listing deleted');
    }

    public function openSaveTemplateModal()
    {
        // Auto-populate templatePositionId from selected title
        if (!empty($this->title) && isset($this->positionIdMap[$this->title])) {
            $this->templatePositionId = $this->positionIdMap[$this->title];
        } else {
            $this->templatePositionId = '';
        }
        // Clear template name and editing state for new entry
        $this->templateName = '';
        $this->editingTemplateId = null;
        $this->editingTemplateCreatorId = null;
        $this->canUpdateOriginal = false;
        // Open the modal
        $this->showSaveTemplateModal = true;
    }

    public function updatedSelectedTemplateId($value)
    {
        if ($value) {
            $this->loadTemplateForEditing($value);
        } else {
            $this->clearTemplateEditing();
        }
    }

    public function viewAndLoadTemplate()
    {
        if (!$this->selected_template_id) {
            session()->flash('error', 'Please select a template first.');
            return;
        }
        $template = JobDescriptionTemplate::find($this->selected_template_id);
        if (!$template) {
            session()->flash('error', 'Template not found.');
            return;
        }
        // Load template contents into the description field
        $this->description = $template->contents;
        // Also set up the editing state
        $this->loadTemplateForEditing($this->selected_template_id);
        // Show success message
        session()->flash('success', 'Template loaded successfully. You can now edit the description or save your job listing.');
    }

    public function loadTemplateForEditing($templateId)
    {
        $template = JobDescriptionTemplate::find($templateId);
        if ($template) {
            $this->editingTemplateId = $template->id;
            $this->editingTemplateCreatorId = $template->created_by;
            $this->templateName = $template->name;
            $this->templatePositionId = $template->position_id;
            // Check if current user created this template
            $this->canUpdateOriginal = Auth::check() && Auth::user()->id == $template->created_by;
        }
    }

    public function clearTemplateEditing()
    {
        $this->templateName = '';
        $this->templatePositionId = '';
        $this->editingTemplateId = null;
        $this->editingTemplateCreatorId = null;
        $this->canUpdateOriginal = false;
        $this->selected_template_id = '';
        // Clear any flash messages
        session()->forget(['template_success', 'template_error']);
    }

    public function saveAsTemplate()
    {
        // Validate required fields
        $this->validate([
            'templateName' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'templatePositionId' => 'required|exists:positions,id',
        ], [
            'templateName.required' => 'Template name is required.',
            'templateName.max' => 'Template name cannot exceed 255 characters.',
            'description.required' => 'Description content is required to save as template.',
            'description.min' => 'Description must be at least 10 characters long.',
            'templatePositionId.required' => 'Please select a position to link this template to.',
            'templatePositionId.exists' => 'The selected position is invalid.',
        ]);
        try {
            // If editing an existing template and user is the creator
            if ($this->editingTemplateId && $this->canUpdateOriginal) {
                // Check for duplicate name only if name is being changed
                $currentTemplate = JobDescriptionTemplate::find($this->editingTemplateId);
                if ($currentTemplate && $currentTemplate->name !== $this->templateName) {
                    // Name is being changed, check for duplicates
                    $duplicateExists = JobDescriptionTemplate::where('name', $this->templateName)
                        ->where('id', '!=', $this->editingTemplateId)
                        ->exists();
                    if ($duplicateExists) {
                        $this->addError('templateName', 'A template with this name already exists. Please choose a different name.');
                        return;
                    }
                }
                // Update existing template
                if ($currentTemplate) {
                    $currentTemplate->update([
                        'name' => $this->templateName,
                        'position_id' => $this->templatePositionId ?: null,
                        'contents' => $this->description,
                    ]);
                    session()->flash('template_success', 'Template updated successfully!');
                }
            } else {
                // Creating new template (Save as Template scenario)
                // If currently editing a template, force user to use a different name
                if ($this->editingTemplateId) {
                    $currentTemplate = JobDescriptionTemplate::find($this->editingTemplateId);
                    if ($currentTemplate && $currentTemplate->name === $this->templateName) {
                        $this->addError('templateName', 'Please enter a different name to save as a new template.');
                        return;
                    }
                }
                // Check for duplicate name
                $duplicateExists = JobDescriptionTemplate::where('name', $this->templateName)->exists();
                if ($duplicateExists) {
                    $this->addError('templateName', 'A template with this name already exists. Please choose a unique name.');
                    return;
                }
                // Create new template
                JobDescriptionTemplate::create([
                    'name' => $this->templateName,
                    'position_id' => $this->templatePositionId ?: null,
                    'contents' => $this->description,
                    'created_by' => Auth::check() ? Auth::user()->id : null,
                ]);
                session()->flash('template_success', 'Template saved successfully!');
            }
            // Reset modal fields
            $this->templateName = '';
            $this->templatePositionId = '';
            $this->editingTemplateId = null;
            $this->editingTemplateCreatorId = null;
            $this->canUpdateOriginal = false;
            // Reload templates
            $this->loadTemplates();
        } catch (\Exception $e) {
            session()->flash('template_error', 'Failed to save template: ' . $e->getMessage());
        }
    }

    private function loadTemplates()
    {
        $this->templates = JobDescriptionTemplate::select('id', 'name', 'position_id', 'contents')
            ->orderBy('name')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'position_id' => $template->position_id,
                    'contents' => $template->contents,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        $positions = \App\Models\Position::pluck('title')->toArray();
        $departments = \App\Models\Department::pluck('name')->toArray();
        // Use only positions with supervisor_role = 1, matching: SELECT * FROM positions WHERE supervisor_role = 1
        $supervisorPositions = \App\Models\Position::where('supervisor_role', 1)->get();
        return view('livewire.job-openings-manager', [
            'jobs' => Facility::find($this->facilityId)?->jobOpenings()->get() ?? collect(),
            'positionDepartmentMap' => $this->positionDepartmentMap,
            'positionIdMap' => $this->positionIdMap,
            'positions' => $positions,
            'departments' => $departments,
            'supervisorPositions' => $supervisorPositions,
            'created_by_name' => $this->created_by_name,
        ]);
    }
}
