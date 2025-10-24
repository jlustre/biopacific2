<?php

namespace App\Livewire;

use Livewire\Component;

class Alerts extends Component
{
    public $success;
    public $error;
    public $errors = [];

    public function mount($success = null, $error = null, $errors = [])
    {
        $this->success = $success;
        $this->error = $error;
        $this->errors = $errors;
    }

    public function render()
    {
        return view('livewire.alerts');
    }
}