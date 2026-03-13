<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploymentController extends Controller
{
    /**
     * Display the authenticated employment portal.
     *
     * This is the authenticated area where employees complete their onboarding forms.
     */
    public function portal()
    {
        $user = Auth::user();

        // You can customize the checklist for employment onboarding here
        $checklistDefaults = [
            ['key' => 'onboarding_form', 'label' => 'Onboarding Form'],
            ['key' => 'policy_acknowledgement', 'label' => 'Policy Acknowledgement'],
            ['key' => 'benefits_enrollment', 'label' => 'Benefits Enrollment'],
        ];

        return view('employment.portal', [
            'user' => $user,
            'checklistDefaults' => $checklistDefaults,
        ]);
    }
}