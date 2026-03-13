<?php

namespace App\Livewire\Employment;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Portal extends Component
{
    public $user;
    public $checklistDefaults = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->checklistDefaults = [
            ['key' => 'onboarding_form', 'label' => 'Onboarding Form'],
            ['key' => 'policy_acknowledgement', 'label' => 'Policy Acknowledgement'],
            ['key' => 'benefits_enrollment', 'label' => 'Benefits Enrollment'],
        ];
    }

    public function render()
    {
        return view('employment.portal', [
            'user' => $this->user,
            'checklistDefaults' => $this->checklistDefaults,
        ]);
    }
}