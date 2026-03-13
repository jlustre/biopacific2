<?php

namespace App\Livewire;

use Livewire\Component;

class TestValidation extends Component
{
    public $name = '';

    public function submit()
    {
        $this->validate([
            'name' => 'required|min:3',
        ]);
    }

    public function render()
    {
        return view('livewire.test-validation');
    }
}
