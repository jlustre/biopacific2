<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Facility;
use Illuminate\Support\Facades\Session;

class FacilityEdit extends Component
{
    public $facility;
    public $facilities;
    public $activeTab = 'basic';

    protected $rules = [
        'facility.name' => 'required|string|max:255',
        'facility.colorScheme.primary_color' => 'nullable|string',
        'facility.colorScheme.secondary_color' => 'nullable|string',
        'facility.colorScheme.accent_color' => 'nullable|string',
    ];

    public function mount(Facility $facility)
    {
        $this->facility = $facility;
        $this->facilities = Facility::all();
    }

    public function save()
    {
        try {
            $this->validate();
            $this->facility->save();
            Session::flash('success', 'Facility updated successfully!');
        } catch (\Exception $e) {
            Session::flash('error', 'Error saving facility: ' . $e->getMessage());
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.facility-edit', [
            'facility' => $this->facility,
            'facilities' => $this->facilities,
            'activeTab' => $this->activeTab,
        ]);
    }
}
