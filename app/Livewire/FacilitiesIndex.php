<?php
// namespace App\Http\Livewire;
namespace App\Livewire;

use Livewire\Component;
use App\Models\Facility; // Make sure you have this model
use Illuminate\Support\Facades\Auth;

class FacilitiesIndex extends Component
{
    public function mount()
    {
        if (!Auth::user() || !Auth::user()->can('view facilities')) {
            abort(403, 'You do not have permission to view facilities.');
        }
    }

    public function render()
    {
        $facilities = Facility::all(); // Or whatever query you need

        return view('livewire.facilities-index', [
            'facilities' => $facilities
        ]);
    }
}
