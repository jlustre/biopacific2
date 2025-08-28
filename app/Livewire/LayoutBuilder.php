<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\LayoutSection;
use App\Models\LayoutTemplate;

class LayoutBuilder extends Component
{
    public $sections = [];
    public $selectedSlugs = [];
    public $availableSections = [];
    public $templateId;
    public $facility;

    public function mount($templateId = null, $facilityId = null)
    {
        $this->templateId = $templateId;
        $this->availableSections = LayoutSection::all()->toArray();
        if ($templateId) {
            $template = LayoutTemplate::find($templateId);
            $this->sections = $template ? $template->sections : [];
        }
        if ($facilityId) {
            $this->facility = \App\Models\Facility::find($facilityId);
        } else {
            $this->facility = \App\Models\Facility::first(); // fallback
        }
    }

    public function addSection($sectionSlug)
    {
        $section = collect($this->availableSections)->firstWhere('slug', $sectionSlug);
        if ($section) {
            $this->sections[] = $section;
            $this->selectedSlugs[] = $sectionSlug;
            $this->emitUp('sectionsUpdated', $this->selectedSlugs);
        }
    }

    public function render()
    {
        return view('livewire.layout-builder', [
            'facility' => $this->facility
        ]);
    }
}
