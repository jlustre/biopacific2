<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use App\Models\Facility;
use App\Models\JobOpening;
use App\Models\Position;
use App\Models\JobDescriptionTemplate;
use Illuminate\Support\Str;

class JobOpeningsForm extends Component
{
    #[Locked]
    public $facilityId;
    
    public $title = '';
    public $description = '';
    public $reporting_to = '';
    public $department = '';
    public $salary_range = '';
    public $salary_unit = '';
    public $employment_type = '';
    public $posted_at = '';
    public $expires_at = '';
    public $status = 'open';
    public $active = true;
    public $created_by = '';
    
    public array $positions = [];
    public array $departments = [];
    public array $supervisorPositions = [];
    public array $templates = [];
    public array $positionDepartmentMap = [];
    public array $positionIdMap = [];
    public array $expandedJobs = [];
    public $selected_template_id = '';
    public $successMessage = '';
    
    // Save Template properties
    public $showSaveTemplateModal = false;
    public $templateName = '';
    public $templatePositionId = '';
    public $editingTemplateId = null;
    public $editingTemplateCreatorId = null;
    public $canUpdateOriginal = false;
    
    // View Template properties
    public $showTemplateViewModal = false;
    public $viewingTemplateId = null;
    public $viewingTemplateName = '';
    public $viewingTemplateContents = '';
    public $viewingTemplateCreatorId = null;
    public $viewingTemplateCreatorName = '';

    #[Computed]
    public function facility()
    {
        return Facility::find($this->facilityId);
    }

    public function mount($facility)
    {
        $this->facilityId = is_object($facility) ? $facility->id : $facility;
        $this->posted_at = date('Y-m-d');
        $this->created_by = auth()->check() ? auth()->user()->name : 'Guest';
        
        // Load positions with their departments from database
        $positionData = Position::with('department')
            ->orderBy('title')
            ->get();
        
        $this->positions = $positionData->pluck('title')->toArray();
        if (!in_array('Other', $this->positions)) {
            $this->positions[] = 'Other';
        }
        
        // Create a mapping of position title to department name
        foreach ($positionData as $position) {
            if ($position->department) {
                $this->positionDepartmentMap[$position->title] = $position->department->name;
            }
            // Create a mapping of position title to position ID
            $this->positionIdMap[$position->title] = $position->id;
        }

        // Load supervisor positions for "Reporting To" select
        $this->supervisorPositions = Position::where('supervisor_role', 1)
            ->orderBy('title')
            ->pluck('title')
            ->toArray();

        // Load job description templates with their position_id for filtering
        $this->templates = \App\Models\JobDescriptionTemplate::select('id', 'name', 'position_id', 'contents')
            ->orderBy('name')
            ->get()
            ->toArray();

        // Load departments from database
        $this->departments = \App\Models\Department::where('type', 'facility')
            ->select('name')
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
        
        if (!in_array('Other', $this->departments)) {
            $this->departments[] = 'Other';
        }
    }

    public function addJobOpening()
    {
        // Validate all fields
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required_without:selected_template_id|string',
            'reporting_to' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'salary_unit' => 'required_with:salary_range|in:hourly,weekly,monthly,yearly',
            'employment_type' => 'required|string|max:100',
            'posted_at' => 'required|date',
            'expires_at' => 'nullable|date|after_or_equal:posted_at',
            'status' => 'required|in:open,closed',
            'active' => 'boolean',
            'selected_template_id' => 'nullable|exists:job_description_templates,id',
        ], [
            'title.required' => 'Job Title is required.',
            'description.required_without' => 'Please either select a template or provide a description.',
            'reporting_to.required' => 'Reporting To is required.',
            'employment_type.required' => 'Employment Type is required.',
            'posted_at.required' => 'Posted date is required.',
            'posted_at.date' => 'Posted date must be a valid date.',
            'expires_at.after_or_equal' => 'Expiration date must be after or equal to the posted date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either open or closed.',
            'salary_unit.required_with' => 'Salary Unit is required when Salary Range is provided.',
        ]);

        try {
            // Determine which description to use
            $descriptionToUse = $this->description;
            if ($this->selected_template_id) {
                $template = \App\Models\JobDescriptionTemplate::find($this->selected_template_id);
                if ($template) {
                    $descriptionToUse = $template->contents;
                }
            }

            Facility::findOrFail($this->facilityId)->jobOpenings()->create([
                'title' => $this->title,
                'description' => $descriptionToUse,
                'reporting_to' => $this->reporting_to,
                'department' => $this->department,
                'salary_range' => $this->salary_range,
                'salary_unit' => $this->salary_unit,
                'employment_type' => $this->employment_type,
                'posted_at' => $this->posted_at,
                'expires_at' => $this->expires_at ?: null,
                'status' => $this->status,
                'active' => $this->active ? 1 : 0,
                'created_by' => auth()->check() ? auth()->user()->id : null,
                'job_description_template_id' => $this->selected_template_id ?: null,
            ]);

            $this->successMessage = 'Job listing added successfully!';
            $this->reset(['title', 'description', 'reporting_to', 'department', 'salary_range', 'employment_type', 'expires_at', 'selected_template_id']);
            $this->salary_unit = '';
            $this->posted_at = date('Y-m-d');
            $this->status = 'open';
            $this->active = true;
            $this->created_by = auth()->check() ? auth()->user()->name : 'Guest';
            
            // Dispatch event to clear CKEditor
            $this->dispatch('description-updated', description: '');
            
            // Clear any previous error messages
            $this->resetErrorBag();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save job listing: ' . $e->getMessage());
        }
    }

    public function toggleExpand($jobId)
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
            $this->canUpdateOriginal = auth()->check() && auth()->user()->id == $template->created_by;
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
                    'created_by' => auth()->check() ? auth()->user()->id : null,
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
        return view('livewire.job-openings-form', [
            'jobs' => Facility::find($this->facilityId)?->jobOpenings()->get() ?? collect(),
            'positionDepartmentMap' => $this->positionDepartmentMap,
            'positionIdMap' => $this->positionIdMap,
        ]);
    }
}
