<?php
// namespace App\Http\Livewire;
namespace App\Livewire;

use Livewire\Component;
use App\Models\Facility; // Make sure you have this model

class FacilitiesIndex extends Component
{
    public function render()
    {
        $facilities = Facility::all(); // Or whatever query you need

        return view('livewire.facilities-index', [
            'facilities' => $facilities
        ]);
    }
}
