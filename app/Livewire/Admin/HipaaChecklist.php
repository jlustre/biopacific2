<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Facility;
use App\Support\HipaaWebsiteChecklist;

class HipaaChecklist extends Component
{
    public Facility $facility;
    public array $flags = [];      // editable flags bound to toggles
    public array $rows  = [];      // computed rows (label/help/passed)

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
        $this->dispatchBrowserEvent('notify', ['msg' => 'HIPAA checklist saved.']);
    }

    public function computeRows(): void
    {
        $this->rows = HipaaWebsiteChecklist::forFacility($this->facility->toArray(), $this->flags);
    }

     public function saveNpp($nppUrl)
    {
        $this->facility->npp_url = $nppUrl;
        $this->facility->save();
        // Optionally emit event or flash message
    }


    public function render()
    {
        return view('livewire.admin.hipaa-checklist');
    }
}
