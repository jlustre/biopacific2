<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreEmployment2Controller extends Controller
{
    /**
     * Display the authenticated pre-employment2 portal.
     */
    public function portal()
    {
        $user = Auth::user();
        $checklistDefaults = [
            ['key' => 'application_form', 'label' => 'Application Form'],
            ['key' => 'reference_check', 'label' => 'Reference Check'],
            ['key' => 'medical_exam', 'label' => 'Medical Examination'],
        ];
        return view('pre-employment2.portal', [
            'user' => $user,
            'checklistDefaults' => $checklistDefaults,
        ]);
    }
}