<?php

namespace App\Http\Controllers;

use App\Models\EmployeeChecklist;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\Position;
use App\Models\PreEmploymentApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreEmploymentController extends Controller
{
    /**
     * Display the pre-employment registration page.
     * 
     * This page is accessed by applicants who have been invited by a hiring manager
     * to proceed with the pre-employment process. Applicants must register or login
     * to access their pre-employment forms.
     */
    public function show($code = null)
    {
        // If user is logged in and no code provided, check if they have a job application
        if (!$code && Auth::check()) {
            $jobApplication = JobApplication::where('user_id', Auth::id())->first();
            if ($jobApplication) {
                return redirect()->route('pre-employment.portal');
            }
        }

        $hasAccount = false;
        $applicantName = null;
        if ($code) {
            $jobApplication = JobApplication::where('applicant_code', $code)->first();
            if ($jobApplication && $jobApplication->email) {
                $hasAccount = User::where('email', $jobApplication->email)->exists();
                if ($hasAccount) {
                    $applicantName = trim($jobApplication->first_name.' '.$jobApplication->last_name);
                }
            }
        }

        return view('pre-employment.show', [
            'applicantCode' => $code,
            'hasAccount' => $hasAccount,
            'applicantName' => $applicantName,
        ]);
    }

    /**
     * Display the authenticated pre-employment portal.
     * 
     * This is the authenticated area where applicants complete their pre-employment forms.
     */
    public function portal()
    {
        $user = Auth::user();

        $checklistDefaults = [
            ['key' => 'application_form', 'label' => 'Application Form'],
            ['key' => 'reference_check', 'label' => 'Reference Check'],
            ['key' => 'medical_exam', 'label' => 'Medical Examination'],
            ['key' => 'references', 'label' => 'Submit References'],
            ['key' => 'compliance_forms', 'label' => 'Compliance Forms'],
        ];

        $checklistItems = collect($checklistDefaults)->map(function ($item) use ($user) {
            return EmployeeChecklist::firstOrCreate(
                ['user_id' => $user->id, 'item_key' => $item['key']],
                ['item_label' => $item['label'], 'status' => 'draft']
            );
        });

        $completedCount = $checklistItems->where('status', 'completed')->count();
        $inProgressCount = $checklistItems->where('status', 'submitted')->count();
        $pendingCount = $checklistItems->whereIn('status', ['draft', 'returned'])->count();

        // Get existing employee data if available with current address and phone
        $employee = $user->employee;
        if ($employee) {
            $employee->load('currentAddress', 'currentPhone');
        }

        // Get pre-employment application data if available (priority source)
        $preEmployment = PreEmploymentApplication::where('user_id', $user->id)->first();

        // Get job application data as fallback
        $jobApplication = JobApplication::where('user_id', $user->id)->first();

        // Get all positions for the dropdown
        $positions = Position::orderBy('title')->get();

        // Get the job opening position if applicant was invited via job opening
        $selectedPositionId = null;
        if ($jobApplication && $jobApplication->jobOpening && $jobApplication->jobOpening->template) {
            $selectedPositionId = $jobApplication->jobOpening->template->position_id;
        }

        return view('pre-employment.portal', [
            'user' => $user,
            'checklistItems' => $checklistItems,
            'completedCount' => $completedCount,
            'inProgressCount' => $inProgressCount,
            'pendingCount' => $pendingCount,
            'employee' => $employee,
            'preEmployment' => $preEmployment,
            'jobApplication' => $jobApplication,
            'positions' => $positions,
            'selectedPositionId' => $selectedPositionId,
        ]);
    }
}
