<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Facility;
use App\Support\HipaaWebsiteChecklist;
use Illuminate\Support\Facades\Log;

class HipaaChecklistInteractive extends Component
{
    public Facility $facility;
    public array $flags = [];      // editable flags bound to toggles
    public array $rows  = [];      // computed rows (label/help/passed)
    public $showCompletedMessage = false; // Default value

    protected $rules = [
        'flags.*' => 'boolean',
    ];

    public function mount(Facility $facility)
    {
        $this->facility = $facility;
        $this->flags    = $facility->hipaa_flags ?? [];
        $this->showCompletedMessage = false;
        
        // Ensure rows are computed during mount
        $this->computeRows();

        // Debugging logs
        Log::debug('Mounting HipaaChecklistInteractive', [
            'facility_id' => $facility->id,
            'flags_count' => count($this->flags),
            'rows_count' => count($this->rows),
            'showCompletedMessage' => $this->showCompletedMessage,
        ]);
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
        Log::debug('toggleFlag called', ['key' => $key, 'current_value' => $this->flags[$key] ?? false]);
        
        $this->flags[$key] = !($this->flags[$key] ?? false);
        
        $this->validate();
        $this->facility->update(['hipaa_flags' => $this->flags]);
        $this->computeRows();
        
        // Show a brief success message
        $this->showCompletedMessage = true;
        $this->dispatch('notify', ['msg' => 'HIPAA checklist updated!']);
        
        Log::debug('toggleFlag completed', ['key' => $key, 'new_value' => $this->flags[$key]]);
    }

    public function computeRows(): void
    {
        $this->rows = HipaaWebsiteChecklist::forFacility($this->facility->toArray(), $this->flags);
    }

    public function getCompletedCountProperty()
    {
        return collect($this->rows)->where('passed', true)->count();
    }

    public function getTotalCountProperty()
    {
        return count($this->rows);
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
        // Ensure rows are computed before counting
        if (empty($this->rows)) {
            $this->computeRows();
        }
        
        $completedCount = collect($this->rows)->where('passed', true)->count();
        $totalCount = count($this->rows);
        
        Log::debug('Rendering HipaaChecklistInteractive', [
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
            'rows_count' => count($this->rows),
            'facility_id' => $this->facility->id ?? 'N/A',
        ]);

        return view('livewire.hipaa-checklist-interactive', [
            'completedCount' => $completedCount,
            'totalCount' => $totalCount,
        ]);
    }
}