<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesMemberPortalContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmploymentController extends Controller
{
    use ProvidesMemberPortalContext;
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

        return view('employment.portal', array_merge($this->memberPortalContext($user), [
            'portalActive' => 'employment',
            'portalTitle' => 'Employment Portal | Bio Pacific HR Portal',
            'portalEyebrow' => 'Onboarding',
            'portalPageTitle' => 'Employment Portal',
            'showPortalSearch' => false,
            'showPortalNotifications' => true,
            'checklistDefaults' => $checklistDefaults,
        ]));
    }
}