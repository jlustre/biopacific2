<?php

<<<<<<< HEAD
namespace App\Livewire;
=======
namespace App\Http\Livewire;
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

use Livewire\Component;
use App\Models\Facility;
use Illuminate\Support\Str;

class FacilityLandingPage extends Component
{
    public Facility $facility;

    public function mount(Facility $facility)
    {
        $this->facility = $facility->load('values','services','testimonials','galleryImages');
    }

    public function render()
    {
        $title = $this->facility->name.' | Compassionate Care in '.$this->facility->city.', '.$this->facility->state;
        $desc  = Str::limit(strip_tags($this->facility->about_text ?? ''), 155);
        $url   = route('facility.show', $this->facility); // uses slug binding
        $image = $this->facility->hero_image_url;

        return view('livewire.facility-landing-page', [
            'facility' => $this->facility,
            'metaTitle'       => $title,
            'metaDescription' => $desc ?: 'Compassionate senior care and rehabilitation services.',
            'metaKeywords'    => 'nursing home, skilled nursing, rehabilitation, memory care, hospice, '.$this->facility->city,
            'canonical'       => $url,
            'ogImage'         => $image,
            'robots'          => 'index,follow',
        ]);
    }
}


