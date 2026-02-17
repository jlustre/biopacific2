<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\JobDescription;
use App\Models\JobDescriptionTemplate;
use App\Models\Position;
use Livewire\Component;

class DescriptionTemplatesManager extends Component
{
    public array $positions = [];
    public array $departments = [];
    public array $jobDescriptions = [];
    public array $descriptionItems = [];
    public array $addedJobDescriptions = [];
    public ?int $selectedPositionId = null;
    public ?string $selectedPositionDescription = null;
    public ?int $loadedTemplateId = null;
    public string $newPositionTitle = '';
    public string $newPositionDescription = '';
    public ?int $selectedDepartmentId = null;
    public string $newDepartmentName = '';
    public string $templateName = '';
    public string $templateTitle = '';
    public string $templateContents = '';
    public string $finalDescription = '';
    public ?int $selectedJobDescriptionId = null;
    public string $selectedJobDescriptionDescription = '';
    public ?int $editingJobDescriptionId = null;
    public string $editJobDescriptionTitle = '';
    public string $editJobDescriptionDescription = '';
    public bool $showPositionBlock = false;
    public bool $showImportPanel = false;
    public bool $showFinalDescriptionEditor = false;
    public ?int $copyToPositionId = null;
    public string $copiedJobDescriptionTitle = '';
    public ?int $importFromPositionId = null;
    public ?int $importJobDescriptionId = null;
    public ?int $importTargetPositionId = null;
    public string $importedJobDescriptionTitle = '';
    public array $importAvailableDescriptions = [];

    protected $rules = [
        'newPositionTitle' => 'required|string|max:255',
        'newPositionDescription' => 'nullable|string',
        'selectedDepartmentId' => 'required|integer',
        'newDepartmentName' => 'required|string|max:255',
    ];

    public function mount(): void
    {
        $this->loadPositions();
        $this->loadDepartments();
    }

    public function openPositionBlock(): void
    {
        $this->showPositionBlock = true;
    }

    public function toggleImportPanel(): void
    {
        $this->showImportPanel = !$this->showImportPanel;
    }

    public function toggleFinalDescriptionEditor(): void
    {
        $this->showFinalDescriptionEditor = !$this->showFinalDescriptionEditor;
    }

    public function updatedSelectedPositionId($value): void
    {
        if (!$value) {
            $this->selectedPositionDescription = null;
            $this->importTargetPositionId = null;
            return;
        }

        $selected = $this->findPosition((int) $value);
        $this->selectedPositionDescription = $selected['description'] ?? null;
        $this->syncDepartmentForPosition($selected);
        $this->applySelectedPositionToTemplate($selected);
        $this->importTargetPositionId = (int) $value;
    }

    public function syncDepartmentForPosition($selected = null): void
    {
        if (!$selected && $this->selectedPositionId) {
            $selected = $this->findPosition((int) $this->selectedPositionId);
        }

        if ($selected && isset($selected['department_id']) && !empty($selected['department_id'])) {
            $this->selectedDepartmentId = $selected['department_id'];
        }

        // Load job descriptions for this position
        if ($this->selectedPositionId) {
            $this->loadJobDescriptions();
            $this->selectedJobDescriptionId = null;
            $this->selectedJobDescriptionDescription = '';
        }
    }

    public function updatedSelectedJobDescriptionId($value): void
    {
        if (!$value) {
            $this->selectedJobDescriptionDescription = '';
            $this->descriptionItems = [];
            $this->editingJobDescriptionId = null;
            $this->editJobDescriptionTitle = '';
            $this->editJobDescriptionDescription = '';
            return;
        }

        $jobDescription = JobDescription::find((int) $value);
        if ($jobDescription) {
            $this->selectedJobDescriptionDescription = $jobDescription->description ?? '';
            $this->parseDescriptionItems();
            $this->editingJobDescriptionId = null;
            $this->editJobDescriptionTitle = '';
            $this->editJobDescriptionDescription = '';
        }
    }

    public function startEditJobDescription(): void
    {
        if (!$this->selectedJobDescriptionId) {
            return;
        }

        $jobDescription = JobDescription::find((int) $this->selectedJobDescriptionId);
        if (!$jobDescription) {
            $this->addError('selectedJobDescriptionId', 'Job description not found.');
            return;
        }

        $this->editingJobDescriptionId = $jobDescription->id;
        $this->editJobDescriptionTitle = $jobDescription->title;
        $this->editJobDescriptionDescription = $jobDescription->description ?? '';
        $this->dispatch('jobDescriptionEditOpened');
    }

    public function cancelEditJobDescription(): void
    {
        $this->editingJobDescriptionId = null;
        $this->editJobDescriptionTitle = '';
        $this->editJobDescriptionDescription = '';
    }

    public function saveJobDescription(): void
    {
        if (!$this->editingJobDescriptionId) {
            return;
        }

        $this->validate([
            'editJobDescriptionTitle' => 'required|string|max:255',
            'editJobDescriptionDescription' => 'nullable|string',
        ]);

        $jobDescription = JobDescription::find((int) $this->editingJobDescriptionId);
        if (!$jobDescription) {
            $this->addError('selectedJobDescriptionId', 'Job description not found.');
            return;
        }

        $jobDescription->update([
            'title' => $this->editJobDescriptionTitle,
            'description' => $this->editJobDescriptionDescription,
        ]);

        $this->loadJobDescriptions();
        $this->selectedJobDescriptionId = $jobDescription->id;
        $this->selectedJobDescriptionDescription = $jobDescription->description ?? '';
        $this->parseDescriptionItems();
        $this->cancelEditJobDescription();
    }

    public function deleteJobDescription(): void
    {
        if (!$this->selectedJobDescriptionId) {
            return;
        }

        $jobDescription = JobDescription::find((int) $this->selectedJobDescriptionId);
        if (!$jobDescription) {
            $this->addError('selectedJobDescriptionId', 'Job description not found.');
            return;
        }

        $jobDescription->delete();

        $this->addedJobDescriptions = array_values(array_filter(
            $this->addedJobDescriptions,
            fn ($item) => (int) $item['id'] !== (int) $this->selectedJobDescriptionId
        ));
        $this->regenerateFinalDescription();

        $this->selectedJobDescriptionId = null;
        $this->selectedJobDescriptionDescription = '';
        $this->descriptionItems = [];
        $this->cancelEditJobDescription();
        $this->loadJobDescriptions();
    }

    public function updatedImportFromPositionId($value): void
    {
        if (!$value) {
            $this->importAvailableDescriptions = [];
            $this->importJobDescriptionId = null;
            $this->importedJobDescriptionTitle = '';
            return;
        }

        // Load job descriptions from the selected source position
        $this->importAvailableDescriptions = JobDescription::where('position_id', (int) $value)
            ->orderBy('title')
            ->get(['id', 'title', 'description'])
            ->toArray();
    }

    public function updatedImportJobDescriptionId($value): void
    {
        if (!$value) {
            $this->importedJobDescriptionTitle = '';
            return;
        }

        // Find the selected description and auto-populate the title
        $selectedDescription = JobDescription::find($value);
        if ($selectedDescription) {
            $this->importedJobDescriptionTitle = $selectedDescription->title;
        }
    }

    private function parseDescriptionItems(): void
    {
        if (empty($this->selectedJobDescriptionDescription)) {
            $this->descriptionItems = [];
            return;
        }

        // Split by "|" and trim whitespace from each item
        $items = array_map('trim', explode('|', $this->selectedJobDescriptionDescription));
        
        // Filter out empty items
        $this->descriptionItems = array_filter($items, fn($item) => !empty($item));
    }

    public function addJobDescriptionToFinal(): void
    {
        if (!$this->selectedJobDescriptionId) {
            return;
        }

        $jobDescription = JobDescription::find((int) $this->selectedJobDescriptionId);
        if (!$jobDescription) {
            return;
        }

        // Check if already added
        if (in_array($this->selectedJobDescriptionId, array_column($this->addedJobDescriptions, 'id'))) {
            return;
        }

        // Add to the list
        $this->addedJobDescriptions[] = [
            'id' => $jobDescription->id,
            'title' => $jobDescription->title,
            'description' => $jobDescription->description ?? '',
        ];

        // Regenerate the final description HTML
        $this->regenerateFinalDescription();
    }

    private function regenerateFinalDescription(): void
    {
        $html = '';

        foreach ($this->addedJobDescriptions as $item) {
            // Add title as h4 heading (bold)
            $html .= '<h4 style="font-weight: bold; margin-top: 1rem; margin-bottom: 0.5rem;">' . htmlspecialchars($item['title']) . '</h4>';
            
            // Add description as bullet list or paragraph based on content
            if (!empty($item['description'])) {
                $description = $item['description'];
                $hasHtml = $description !== strip_tags($description);
                $safeHtml = strip_tags($description, '<p><strong><em><u><ul><ol><li><br><a><h4><h5><h6><blockquote>');

                if ($hasHtml) {
                    $html .= '<div style="margin-bottom: 1rem; line-height: 1.6;">' . $safeHtml . '</div>';
                } elseif (strpos($description, '|') !== false) {
                    // Split into list items
                    $items = array_map('trim', explode('|', $description));
                    $items = array_filter($items, fn($i) => !empty($i));
                    
                    if (!empty($items)) {
                        $html .= '<ul style="margin-left: 1.5rem; margin-bottom: 1rem;">';
                        foreach ($items as $listItem) {
                            $html .= '<li>' . htmlspecialchars($listItem) . '</li>';
                        }
                        $html .= '</ul>';
                    }
                } else {
                    // Display as paragraph
                    $html .= '<p style="margin-bottom: 1rem; line-height: 1.6;">' . nl2br(htmlspecialchars($description)) . '</p>';
                }
            }
        }

        $this->finalDescription = $html;
    }

    public function removeFromFinal($index): void
    {
        unset($this->addedJobDescriptions[$index]);
        $this->addedJobDescriptions = array_values($this->addedJobDescriptions); // Re-index array
        $this->regenerateFinalDescription();
    }

    public function copyJobDescriptionToPosition(): void
    {
        // Validate required fields
        if (!$this->selectedJobDescriptionId) {
            $this->addError('selectedJobDescriptionId', 'Please select a job description to copy.');
            return;
        }

        if (!$this->copyToPositionId) {
            $this->addError('copyToPositionId', 'Please select a position to copy the description to.');
            return;
        }

        $this->validate([
            'copiedJobDescriptionTitle' => 'required|string|max:255',
        ]);

        // Get the job description to copy
        $sourceJobDescription = JobDescription::find($this->selectedJobDescriptionId);
        if (!$sourceJobDescription) {
            $this->addError('selectedJobDescriptionId', 'Job description not found.');
            return;
        }

        // Get the target position
        $targetPosition = Position::find($this->copyToPositionId);
        if (!$targetPosition) {
            $this->addError('copyToPositionId', 'Target position not found.');
            return;
        }

        // Check if description with same title already exists for this position
        $existingDescription = JobDescription::where('position_id', $this->copyToPositionId)
            ->where('title', $this->copiedJobDescriptionTitle)
            ->first();

        if ($existingDescription) {
            $this->addError('copiedJobDescriptionTitle', 'A description with this title already exists for the selected position.');
            return;
        }

        // Create the new job description
        JobDescription::create([
            'position_id' => $this->copyToPositionId,
            'title' => $this->copiedJobDescriptionTitle,
            'description' => $sourceJobDescription->description,
        ]);

        // Reset copy form
        $this->copyToPositionId = null;
        $this->copiedJobDescriptionTitle = '';

        // Show success message
        session()->flash('success', 'Job description copied successfully to ' . $targetPosition->title . '!');

        // Reload positions in case UI needs updating
        $this->loadPositions();
    }

    public function importJobDescription(): void
    {
        // Validate required fields
        if (!$this->importFromPositionId) {
            $this->addError('importFromPositionId', 'Please select a source position.');
            return;
        }

        if (!$this->importJobDescriptionId) {
            $this->addError('importJobDescriptionId', 'Please select a job description to import.');
            return;
        }

        $targetPositionId = $this->importTargetPositionId ?: $this->selectedPositionId;
        if (!$targetPositionId) {
            $this->addError('importTargetPositionId', 'Please select a target position.');
            return;
        }

        $this->validate([
            'importedJobDescriptionTitle' => 'required|string|max:255',
        ]);

        // Get the job description to import
        $sourceJobDescription = JobDescription::find($this->importJobDescriptionId);
        if (!$sourceJobDescription) {
            $this->addError('importJobDescriptionId', 'Job description not found.');
            return;
        }

        $targetPosition = Position::find($targetPositionId);
        if (!$targetPosition) {
            $this->addError('importTargetPositionId', 'Target position not found.');
            return;
        }

        // Check if description with same title already exists for target position
        $existingDescription = JobDescription::where('position_id', $targetPositionId)
            ->where('title', $this->importedJobDescriptionTitle)
            ->first();

        if ($existingDescription) {
            $this->addError('importedJobDescriptionTitle', 'A description with this title already exists for the selected position.');
            return;
        }

        // Create the new job description in the target position
        JobDescription::create([
            'position_id' => $targetPositionId,
            'title' => $this->importedJobDescriptionTitle,
            'description' => $sourceJobDescription->description,
        ]);

        // Reset import form
        $this->importFromPositionId = null;
        $this->importJobDescriptionId = null;
        $this->importedJobDescriptionTitle = '';
        $this->importAvailableDescriptions = [];

        // Reload job descriptions for current position when it is the target
        if ((int) $targetPositionId === (int) $this->selectedPositionId) {
            $this->loadJobDescriptions();
        }

        // Show success message
        session()->flash('success', 'Job description imported successfully!');
    }

    public function importAllJobDescriptions(): void
    {
        // Validate required fields
        if (!$this->importFromPositionId) {
            $this->addError('importFromPositionId', 'Please select a source position.');
            return;
        }

        $targetPositionId = $this->importTargetPositionId ?: $this->selectedPositionId;
        if (!$targetPositionId) {
            $this->addError('importTargetPositionId', 'Please select a target position.');
            return;
        }

        // Get all job descriptions from source position
        $sourceDescriptions = JobDescription::where('position_id', $this->importFromPositionId)
            ->orderBy('title')
            ->get();

        if ($sourceDescriptions->isEmpty()) {
            $this->addError('importFromPositionId', 'No job descriptions found in the selected source position.');
            return;
        }

        $targetPosition = Position::find($targetPositionId);
        if (!$targetPosition) {
            $this->addError('importTargetPositionId', 'Target position not found.');
            return;
        }

        $importedCount = 0;
        $skippedCount = 0;

        // Import all descriptions from source position
        foreach ($sourceDescriptions as $sourceDescription) {
            // Check if description with same title already exists for target position
            $existingDescription = JobDescription::where('position_id', $targetPositionId)
                ->where('title', $sourceDescription->title)
                ->first();

            if ($existingDescription) {
                $skippedCount++;
                continue;
            }

            // Create the new job description in the target position
            JobDescription::create([
                'position_id' => $targetPositionId,
                'title' => $sourceDescription->title,
                'description' => $sourceDescription->description,
                'version' => $sourceDescription->version,
            ]);

            $importedCount++;
        }

        // Reset import form
        $this->importFromPositionId = null;
        $this->importJobDescriptionId = null;
        $this->importedJobDescriptionTitle = '';
        $this->importAvailableDescriptions = [];
        $this->showImportPanel = false;

        // Reload job descriptions for current position when it is the target
        if ((int) $targetPositionId === (int) $this->selectedPositionId) {
            $this->loadJobDescriptions();
        }

        // Show success message
        $message = "Successfully imported $importedCount job descriptions";
        if ($skippedCount > 0) {
            $message .= " ($skippedCount skipped - already exist)";
        }
        $message .= "!";
        session()->flash('success', $message);
    }

    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'selectedPositionId' => 'required|integer',
            'finalDescription' => 'required|string',
        ]);

        $position = Position::find($this->selectedPositionId);
        if (!$position) {
            $this->addError('selectedPositionId', 'Invalid position selected.');
            return;
        }

        // If a template is loaded, update it; otherwise create a new one
        if ($this->loadedTemplateId) {
            $template = JobDescriptionTemplate::find($this->loadedTemplateId);
            if (!$template) {
                $this->addError('loadedTemplateId', 'Template not found.');
                return;
            }

            $template->update([
                'name' => $this->templateName,
                'position_id' => $position->id,
                'contents' => $this->finalDescription,
                'job_descriptions' => json_encode($this->addedJobDescriptions),
            ]);

            session()->flash('success', 'Template updated successfully!');
        } else {
            $template = JobDescriptionTemplate::create([
                'name' => $this->templateName,
                'position_id' => $position->id,
                'contents' => $this->finalDescription,
                'job_descriptions' => json_encode($this->addedJobDescriptions),
                'created_by' => auth()->id(),
            ]);

            session()->flash('success', 'Template saved successfully!');
        }

        if ($template) {
            // Emit event to refresh templates list
            $this->dispatch('templateSaved');
            
            // Reset form
            $this->resetTemplateForm();
        }
    }

    public function saveTemplateAs(): void
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'selectedPositionId' => 'required|integer',
            'finalDescription' => 'required|string',
        ]);

        $position = Position::find($this->selectedPositionId);
        if (!$position) {
            $this->addError('selectedPositionId', 'Invalid position selected.');
            return;
        }

        // Check if a template with the same name already exists
        $existingTemplate = JobDescriptionTemplate::where('name', $this->templateName)->first();
        if ($existingTemplate) {
            $this->addError('templateName', 'A template with this name already exists. Please choose a different name.');
            return;
        }

        // Always create a new template
        $template = JobDescriptionTemplate::create([
            'name' => $this->templateName,
            'position_id' => $position->id,
            'contents' => $this->finalDescription,
            'job_descriptions' => json_encode($this->addedJobDescriptions),
            'created_by' => auth()->id(),
        ]);

        if ($template) {
            // Update the loaded template ID to the new template
            $this->loadedTemplateId = $template->id;
            
            // Emit event to refresh templates list
            $this->dispatch('templateSaved');
            
            // Show success message
            session()->flash('success', 'Template saved as new copy successfully!');
        }
    }

    public function resetTemplateForm(): void
    {
        $this->templateName = '';
        $this->templateTitle = '';
        $this->templateContents = '';
        $this->finalDescription = '';
        $this->addedJobDescriptions = [];
        $this->selectedJobDescriptionId = null;
        $this->selectedJobDescriptionDescription = '';
        $this->selectedPositionId = null;
        $this->selectedDepartmentId = null;
        $this->showPositionBlock = false;
        $this->loadedTemplateId = null;
        $this->copyToPositionId = null;
        $this->copiedJobDescriptionTitle = '';
        $this->importFromPositionId = null;
        $this->importJobDescriptionId = null;
        $this->importTargetPositionId = null;
        $this->importedJobDescriptionTitle = '';
        $this->importAvailableDescriptions = [];
        $this->showImportPanel = false;
        
        // Dispatch event to refresh templates list
        $this->dispatch('templateSaved');
    }

    public function loadTemplate($templateId): void
    {
        $template = JobDescriptionTemplate::find($templateId);
        if (!$template) {
            $this->addError('templateId', 'Template not found.');
            return;
        }

        // Track the loaded template ID
        $this->loadedTemplateId = $template->id;

        // Populate form with template data
        $this->templateName = $template->name;
        $this->templateTitle = $template->position->title ?? '';
        $this->templateContents = $template->contents;
        $this->finalDescription = $template->contents;

        // Load job descriptions from JSON
        if ($template->job_descriptions) {
            $this->addedJobDescriptions = json_decode($template->job_descriptions, true) ?? [];
        }

        // Show all blocks
        $this->showPositionBlock = true;

        // Use position_id from template
        if ($template->position_id) {
            $this->selectedPositionId = $template->position_id;
            $this->syncDepartmentForPosition();
        }
    }

    public function createPosition(): void
    {
        $this->validate([
            'newPositionTitle' => 'required|string|max:255',
            'selectedDepartmentId' => 'required|integer',
            'newPositionDescription' => 'nullable|string',
        ]);

        $department = Department::find($this->selectedDepartmentId);
        if (!$department) {
            $this->addError('selectedDepartmentId', 'Please select a valid department.');
            return;
        }

        // Check if position already exists with same title and department
        $existingPosition = Position::where('title', $this->newPositionTitle)
            ->where('department_id', $this->selectedDepartmentId)
            ->first();

        if ($existingPosition) {
            $this->addError('newPositionTitle', "This position already exists in {$department->name} department.");
            return;
        }

        $position = Position::create([
            'title' => $this->newPositionTitle,
            'department_id' => $this->selectedDepartmentId,
            'description' => $this->newPositionDescription ?: null,
        ]);

        $this->newPositionTitle = '';
        $this->newPositionDescription = '';

        $this->loadPositions();
        $this->selectedPositionId = $position->id;
        $this->selectedPositionDescription = $position->description;
        $this->applySelectedPositionToTemplate([
            'id' => $position->id,
            'title' => $position->title,
            'description' => $position->description,
        ]);
    }

    public function createDepartment(): void
    {
        $this->validate([
            'newDepartmentName' => 'required|string|max:255',
        ]);

        // Check if department already exists
        $existingDepartment = Department::where('name', $this->newDepartmentName)->first();

        if ($existingDepartment) {
            $this->addError('newDepartmentName', 'This department already exists.');
            return;
        }

        $department = Department::create([
            'name' => $this->newDepartmentName,
        ]);

        $this->newDepartmentName = '';
        $this->loadDepartments();
        $this->selectedDepartmentId = $department->id;
    }

    private function applySelectedPositionToTemplate(?array $selected): void
    {
        if (!$selected) {
            return;
        }

        $this->templateTitle = $selected['title'] ?? '';
        if ($this->templateName === '') {
            $this->templateName = $selected['title'] ?? '';
        }
    }

    private function findPosition(int $id): ?array
    {
        foreach ($this->positions as $position) {
            if ((int) $position['id'] === $id) {
                return $position;
            }
        }

        return null;
    }

    private function loadPositions(): void
    {
        $positions = Position::with('department')
            ->orderBy('title')
            ->get(['id', 'title', 'description', 'department_id'])
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'department_id' => $position->department_id,
                    'department' => $position->department ? $position->department->name : null,
                ];
            })
            ->toArray();
        
        // Check for duplicate titles and add department suffix if needed
        $titleCounts = array_count_values(array_column($positions, 'title'));
        
        foreach ($positions as &$position) {
            if ($titleCounts[$position['title']] > 1) {
                $position['display_title'] = $position['title'] . ' - ' . $position['department'];
            } else {
                $position['display_title'] = $position['title'];
            }
        }
        
        $this->positions = $positions;
    }

    private function loadDepartments(): void
    {
        $this->departments = Department::orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    private function loadJobDescriptions(): void
    {
        if (!$this->selectedPositionId) {
            $this->jobDescriptions = [];
            return;
        }

        $this->jobDescriptions = JobDescription::where('position_id', $this->selectedPositionId)
            ->orderBy('title')
            ->get(['id', 'title', 'description'])
            ->toArray();
    }

    public function deleteTemplate($templateId): void
    {
        $template = JobDescriptionTemplate::find($templateId);
        if (!$template) {
            session()->flash('error', 'Template not found.');
            return;
        }

        // Check if template is being used in job_openings
        $usageCount = \App\Models\JobOpening::where('job_description_template_id', $templateId)->count();
        if ($usageCount > 0) {
            session()->flash('error', "Cannot delete this template. It is currently being used by {$usageCount} job opening(s).");
            return;
        }

        $template->delete();
        session()->flash('success', 'Template deleted successfully!');
        $this->dispatch('templateDeleted');
    }

    public function render()
    {
        return view('livewire.admin.description-templates-manager');
    }
}
