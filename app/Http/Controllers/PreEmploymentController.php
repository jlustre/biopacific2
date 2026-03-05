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

    /**
     * Save or update a Confidential Reference Check for the authenticated user.
     */
    public function saveReferenceCheck(Request $request, $id)
    {
        $user = Auth::user();
        $referenceCheck = \App\Models\ConfidentialReferenceCheck::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'position_applied_for' => 'required|string|max:255',
            'employed_by' => 'nullable|string|max:255',
            'supervisor' => 'required|string|max:255',
            'reference_phone' => 'nullable|string|max:255',
            'reference_email' => 'nullable|email|max:255',
            'applicant_signature' => 'required|string|max:255',
            'date' => 'required|date',
            // Add other fields as needed
        ]);

        // Map validated fields to model
        $referenceCheck->applicant_name = $validated['applicant_name'];
        $referenceCheck->position_applied_for = $validated['position_applied_for'];
        $referenceCheck->employed_by = $request->input('employed_by');
        $referenceCheck->supervisor = $validated['supervisor'];
        $referenceCheck->reference_phone = $request->input('reference_phone');
        $referenceCheck->reference_email = $request->input('reference_email');
        $referenceCheck->applicant_signature = $validated['applicant_signature'];
        $referenceCheck->date = $validated['date'];
        // Add other fields as needed

        $referenceCheck->save();

        return redirect()->route('pre-employment.portal')->with('success', 'Reference check saved.');
    }
{
    /**
     * Add a new blank Confidential Reference Check for the authenticated user.
     */
    public function addReferenceCheck(Request $request)
    {
        $user = $request->user();
        // Optionally, you can limit the number of reference checks per user
        $max = 5;
        $count = \App\Models\ConfidentialReferenceCheck::where('user_id', $user->id)->count();
        if ($count >= $max) {
            return redirect()->route('pre-employment.portal')->with('error', 'Maximum number of reference checks reached.');
        }
        \App\Models\ConfidentialReferenceCheck::create([
            'user_id' => $user->id,
            'reference_name' => 'Reference Name',
            'relationship' => 'Relationship',
            'comments' => '',
        ]);
        return redirect()->route('pre-employment.portal');
    }
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

        // Fetch all confidential reference checks for this user
        $referenceChecks = \App\Models\ConfidentialReferenceCheck::where('user_id', $user->id)->get();

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
            'referenceChecks' => $referenceChecks,
        ]);
    }

        /**
     * Delete a Confidential Reference Check for the authenticated user.
     */
    public function deleteReferenceCheck($id)
    {
        $user = Auth::user();
        $referenceCheck = \App\Models\ConfidentialReferenceCheck::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $referenceCheck->delete();
        return redirect()->route('pre-employment.portal')->with('success', 'Reference check deleted.');
    }
}
