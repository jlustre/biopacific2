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
     * Save or update a Confidential Reference Check for the authenticated user.
     */
    public function saveReferenceCheck(Request $request, $id)
    {
        $user = Auth::user();

        // Prevent duplicate reference check for same user, reference_name, or company
        $referenceIndex = $request->input('reference_index') ?? 1;
        $referenceCheck = \App\Models\ConfidentialReferenceCheck::where('user_id', $user->id)
            ->where('reference_index', $referenceIndex)
            ->first();

        if ($referenceCheck) {
            $notification = 'Reference check updated.';
        } else {
            $referenceCheck = new \App\Models\ConfidentialReferenceCheck();
            $referenceCheck->user_id = $user->id;
            $referenceCheck->reference_index = $referenceIndex;
            $notification = 'Reference check created.';
        }

        $validated = $request->validate([
            'reference_index' => 'nullable|integer|min:1',
            'reference_name' => 'nullable|string|max:255',
            'reference_title' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'reference_phone' => 'nullable|string|max:255',
            'reference_email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'signed' => 'nullable|boolean',
            'signed_date' => 'nullable|date',
            'employment_from' => 'nullable|date',
            'employment_to' => 'nullable|date',
            'salary' => 'nullable|numeric',
            'salary_per' => 'nullable|string|max:255',
            'duties_description' => 'nullable|string',
            'performance_description' => 'nullable|string',
            'date_contacted' => 'nullable|date',
            'applicant_signature' => 'nullable|string|max:255',
            'signature_date' => 'nullable|date',
        ]);

        $referenceCheck->user_id = $user->id;
        $referenceCheck->reference_index = $validated['reference_index'] ?? $request->input('reference_index') ?? 1;
        $referenceCheck->reference_name = $validated['reference_name'] ?? $request->input('reference_name') ?? null;
        $referenceCheck->reference_title = $validated['reference_title'] ?? $request->input('reference_title') ?? null;
        $referenceCheck->company_address = $validated['company_address'] ?? $request->input('company_address') ?? null;
        $referenceCheck->reference_phone = $validated['reference_phone'] ?? null;
        $referenceCheck->reference_email = $validated['reference_email'] ?? null;
        $referenceCheck->company = $validated['company'] ?? null;
        // Set signed to 1 if both applicant_signature and signature_date are filled
        $applicantSignature = $validated['applicant_signature'] ?? $request->input('applicant_signature') ?? null;
        $signatureDate = $validated['signature_date'] ?? $request->input('date') ?? null;
        if (!empty($applicantSignature) && !empty($signatureDate)) {
            $referenceCheck->signed = 1;
        } else {
            $referenceCheck->signed = $validated['signed'] ?? false;
        }
        $referenceCheck->signed_date = $validated['signed_date'] ?? null;
        $referenceCheck->employment_from = $validated['employment_from'] ?? null;
        $referenceCheck->employment_to = $validated['employment_to'] ?? null;
        $referenceCheck->salary = $validated['salary'] ?? null;
        $referenceCheck->salary_per = $validated['salary_per'] ?? null;
        $referenceCheck->duties_description = $validated['duties_description'] ?? null;
        $referenceCheck->performance_description = $validated['performance_description'] ?? null;
        $referenceCheck->date_contacted = $validated['date_contacted'] ?? null;
        $referenceCheck->applicant_signature = $validated['applicant_signature'] ?? $request->input('applicant_signature') ?? null;
        $referenceCheck->signature_date = $validated['signature_date'] ?? $request->input('date') ?? null;

        $referenceCheck->save();

        return redirect()->route('pre-employment.portal')->with('success', $notification);
    }
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
        // Find the max reference_index for this user and increment
        $maxIndex = \App\Models\ConfidentialReferenceCheck::where('user_id', $user->id)->max('reference_index');
        $nextIndex = $maxIndex ? $maxIndex + 1 : 1;
        \App\Models\ConfidentialReferenceCheck::create([
            'user_id' => $user->id,
            'reference_index' => $nextIndex,
            'reference_name' => '',
            'relationship' => '',
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
