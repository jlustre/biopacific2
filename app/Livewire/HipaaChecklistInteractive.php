<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Facility;
use App\Support\HipaaWebsiteChecklist;

class HipaaChecklistInteractive extends Component
{
    public Facility $facility;
    public array $flags = [];      // editable flags bound to toggles
    public array $rows  = [];      // computed rows (label/help/passed)
    public $showCompletedMessage = false;

    protected $rules = [
        'flags.*' => 'boolean',
    ];

    public function mount(Facility $facility)
    {
        $this->facility = $facility;
        $this->flags    = $facility->hipaa_flags ?? [];
        $this->computeRows();
    }

    public function updatedFlags()
    {
        $this->validate();
        $this->facility->update(['hipaa_flags' => $this->flags]);
        $this->computeRows();
        
        // Show a brief success message
        $this->showCompletedMessage = true;
        $this->dispatch('notify', ['msg' => 'HIPAA checklist updated!']);
    }

    // Alternative method to toggle individual flags
    public function toggleFlag($key)
    {
        $this->flags[$key] = !($this->flags[$key] ?? false);
        
        $this->validate();
        $this->facility->update(['hipaa_flags' => $this->flags]);
        $this->computeRows();
        
        // Show a brief success message
        $this->showCompletedMessage = true;
        $this->dispatch('notify', ['msg' => 'HIPAA checklist updated!']);
    }

    public function computeRows(): void
    {
        $this->rows = HipaaWebsiteChecklist::forFacility($this->facility->toArray(), $this->flags);
    }

    public function saveNpp($nppUrl)
    {
        $this->facility->update(['npp_url' => $nppUrl]);
        $this->computeRows(); // Recalculate since NPP affects the checklist
        $this->dispatch('notify', ['msg' => 'NPP URL saved!']);
    }

    public function resetHipaaFlags()
    {
        $this->flags = [];
        $this->facility->update(['hipaa_flags' => []]);
        $this->computeRows();
        $this->dispatch('notify', ['msg' => 'HIPAA checklist reset!']);
    }

    public function render()
    {
        $completedCount = collect($this->rows)->where('passed', true)->count();
        $totalCount = count($this->rows);
        
        return view('livewire.hipaa-checklist-interactive', [
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
        ]);
    }
}