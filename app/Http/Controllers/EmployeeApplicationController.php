<?php

namespace App\Http\Controllers;

use App\Models\PreEmploymentApplication;
use App\Models\EmployeeChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
// use Illuminate\Support\Facades\Log;

class EmployeeApplicationController extends Controller
{
    /**
     * Show the pre-employment application form, prefilled from job_applications if possible
     */
    public function showPreEmploymentForm(Request $request)
    {
        $user = Auth::user();
        $preEmployment = \App\Models\PreEmploymentApplication::where('user_id', $user->id)->first();
        $jobApplication = \App\Models\JobApplication::where('user_id', $user->id)->where('status', 'pre-employment')->first();

        $prefill = [];
        if (!$preEmployment && $jobApplication) {
            $prefill = [
                'first_name' => $jobApplication->first_name,
                'middle_name' => $jobApplication->middle_name,
                'last_name' => $jobApplication->last_name,
                'email' => $jobApplication->email,
                'phone_number' => $jobApplication->phone_number,
                'current_address' => $jobApplication->current_address,
                'city' => $jobApplication->city,
                'state' => $jobApplication->state,
                'zip_code' => $jobApplication->zip_code,
                'county' => $jobApplication->county,
                'position_applied_for' => $jobApplication->position_id,
                // Add more fields as needed
            ];
        }

        // Build educationFields[$key][$entry] for easy Blade repopulation
        $educationLevels = [
            0 => 'High School (Last Attended)',
            1 => 'Colleges/Universities',
            2 => 'Graduate School',
            3 => 'Other (Business, Technical, Secretarial, etc.)',
        ];
        $educationFields = [];
        if ($preEmployment && $preEmployment->education) {
            $eduArr = is_array($preEmployment->education) ? $preEmployment->education : [];
            foreach ($educationLevels as $key => $label) {
                $normalizedLevel = $label === 'Other (Business, Technical, Secretarial, etc.)' ? 'Other' : $label;
                $entries = array_values(array_filter($eduArr, function($e) use ($normalizedLevel) {
                    return isset($e['level']) && $e['level'] === $normalizedLevel;
                }));
                for ($entry = 1; $entry <= 2; $entry++) {
                    $educationFields[$key][$entry] = isset($entries[$entry-1]) ? $entries[$entry-1] : [
                        'school' => '',
                        'date_from' => '',
                        'date_to' => '',
                        'graduated' => '',
                        'degree' => '',
                        'major' => '',
                    ];
                }
            }
        } else {
            foreach ($educationLevels as $key => $label) {
                for ($entry = 1; $entry <= 2; $entry++) {
                    $educationFields[$key][$entry] = [
                        'school' => '',
                        'date_from' => '',
                        'date_to' => '',
                        'graduated' => '',
                        'degree' => '',
                        'major' => '',
                    ];
                }
            }
        }

        return view('pre-employment.application_form', compact('preEmployment', 'prefill', 'educationFields'));
    }
    /**
     * Store or update pre-employment application data
     */
    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'current_address' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:2',
                'zip_code' => 'required|string|max:10',
                'county' => 'nullable|string|max:100',
                'position_applied_for' => 'required|exists:positions,id',
                'employment_type' => 'required|string|in:full_time,part_time,temporary,other',
                'employment_type_other' => 'nullable|string|max:100',
                'shift_preference' => 'nullable|string',
                'date_available' => 'nullable|date',
                'wage_salary_expected' => 'nullable|string|max:100',
                'worked_here_before' => 'boolean',
                'worked_here_when_where' => 'nullable|required_if:worked_here_before,1|string',
                'applied_here_before' => 'boolean',
                'applied_here_when_where' => 'nullable|required_if:applied_here_before,1|string',
                'relatives_work_here' => 'boolean',
                'relatives_details' => 'nullable|required_if:relatives_work_here,1|string',
                'has_drivers_license' => 'required|boolean',
                'drivers_license_number' => 'nullable|required_if:has_drivers_license,1|string|max:50',
                'drivers_license_state' => 'nullable|required_if:has_drivers_license,1|string|max:50',
                'drivers_license_expiration' => 'nullable|required_if:has_drivers_license,1|date',
                'how_heard_about_us' => 'nullable|string|max:255',
                'how_heard_other' => 'nullable|required_if:how_heard_about_us,other|string|max:255',
                'authorized_to_work_usa' => 'boolean',
                'contact_current_employer' => 'nullable|boolean',
                'work_history_description' => 'nullable|string',
                'additional_references' => 'nullable|string',
                'professional_affiliations' => 'nullable|string',
                'license_suspended' => 'nullable|boolean',
                'license_suspended_explanation' => 'nullable|string',
                'special_skills' => 'nullable|string',
                'action' => 'nullable|in:save,submit', // Track which button was clicked
            ], [
                'employment_type.required' => 'Please select an employment type.',
                'employment_type.in' => 'Invalid employment type selected.',
                'employment_type_other.max' => 'Other employment type must be less than 100 characters.',
                'worked_here_when_where.required_if' => 'Please provide when and where you worked here previously.',
                'relatives_details.required_if' => 'Please specify the name and relationship of your relatives who work for the company.',
                'drivers_license_number.required_if' => 'Driver\'s license number is required when you have a valid driver\'s license.',
                'drivers_license_state.required_if' => 'Driver\'s license state is required when you have a valid driver\'s license.',
                'drivers_license_expiration.required_if' => 'Driver\'s license expiration date is required when you have a valid driver\'s license.',
                'how_heard_other.required_if' => 'Please specify how you heard about us if you selected Other.',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation failed for employee application', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);
            throw $e; // Re-throw to show validation errors
        }

        DB::beginTransaction();
        try {
            // Get the position title for the position_id submitted
            $position = \App\Models\Position::find($validated['position_applied_for']);
            
            // Collect work experience entries
            $workExperience = [];
            for ($i = 1; $i <= 3; $i++) {
                $entry = [
                    'employer' => $request->input("work_exp_{$i}_employer"),
                    'start_position' => $request->input("work_exp_{$i}_start_position"),
                    'end_position' => $request->input("work_exp_{$i}_end_position"),
                    'dates' => $request->input("work_exp_{$i}_dates"),
                    'phone' => $request->input("work_exp_{$i}_phone"),
                    'supervisor' => $request->input("work_exp_{$i}_supervisor"),
                    'reason' => $request->input("work_exp_{$i}_reason"),
                    'rehire' => $request->input("work_exp_{$i}_rehire"),
                ];
                if ($entry['employer'] || $entry['dates']) {
                    $workExperience[] = $entry;
                }
            }

            // Collect previous addresses
            $previousAddresses = [];
            for ($i = 1; $i <= 7; $i++) {
                $entry = [
                    'address' => $request->input("prev_address_{$i}_address"),
                    'phone' => $request->input("prev_address_{$i}_phone"),
                    'city' => $request->input("prev_address_{$i}_city"),
                    'state' => $request->input("prev_address_{$i}_state"),
                    'zip' => $request->input("prev_address_{$i}_zip"),
                    'county' => $request->input("prev_address_{$i}_county"),
                ];
                if ($entry['address'] || $entry['city']) {
                    $previousAddresses[] = $entry;
                }
            }

            // Collect education entries
            $education = [];
            $educationLevels = [
                'High School (Last Attended)',
                'Colleges/Universities',
                'Graduate School',
                'Other (Business, Technical, Secretarial, etc.)'
            ];
            foreach ($educationLevels as $index => $level) {
                // Normalize 'Other (Business, Technical, Secretarial, etc.)' to 'Other' for saving
                $normalizedLevel = ($level === 'Other (Business, Technical, Secretarial, etc.)') ? 'Other' : $level;
                for ($entry = 1; $entry <= 2; $entry++) {
                    $eduEntry = [
                        'level' => $normalizedLevel,
                        'school' => $request->input("education_{$index}_{$entry}_school"),
                        'date_from' => $request->input("education_{$index}_{$entry}_from"),
                        'date_to' => $request->input("education_{$index}_{$entry}_to"),
                        'graduated' => $request->input("education_{$index}_{$entry}_graduated"),
                        'degree' => $request->input("education_{$index}_{$entry}_degree"),
                        'major' => $request->input("education_{$index}_{$entry}_major"),
                    ];
                    if ($eduEntry['school'] || $eduEntry['degree']) {
                        $education[] = $eduEntry;
                    }
                }
            }

            // Determine status based on action
            $action = $request->input('action', 'save');
            $status = $action === 'submit' ? 'submitted' : 'draft';

            if (!(bool) ($validated['has_drivers_license'] ?? false)) {
                $validated['drivers_license_number'] = null;
                $validated['drivers_license_state'] = null;
                $validated['drivers_license_expiration'] = null;
            }
            
            if (!(bool) ($validated['worked_here_before'] ?? false)) {
                $validated['worked_here_when_where'] = null;
            }
            
            if (!(bool) ($validated['relatives_work_here'] ?? false)) {
                $validated['relatives_details'] = null;
            }
            if (!(bool) ($validated['applied_here_before'] ?? false)) {
                $validated['applied_here_when_where'] = null;
            }
            
            // Prepare application data
            $applicationData = array_merge($validated, [
                'user_id' => Auth::id(),
                'position_id' => $validated['position_applied_for'],
                'position_applied_for' => $position ? $position->title : 'Unknown Position',
                'employment_type' => $validated['employment_type'],
                'employment_type_other' => $validated['employment_type_other'] ?? null,
                'work_experience' => !empty($workExperience) ? $workExperience : null,
                'previous_addresses' => !empty($previousAddresses) ? $previousAddresses : null,
                'education' => !empty($education) ? $education : null,
                'status' => $status,
            ]);

            // Update or create pre-employment application
            PreEmploymentApplication::updateOrCreate(
                ['user_id' => Auth::id()],
                $applicationData
            );

            // Update the EmployeeChecklist status if form is being submitted
            if ($action === 'submit') {
                EmployeeChecklist::where('user_id', Auth::id())
                    ->where('item_key', 'application_form')
                    ->update(['status' => 'submitted']);
            }

            DB::commit();
            
            // Provide different success messages based on action
            if ($action === 'submit') {
                return redirect()->back()->with('success', 'Application successfully submitted to hiring manager!');
            } else {
                return redirect()->back()->with('success', 'Application form saved successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pre-employment application error: ' . $e->getMessage(), [
                'exception' => $e->__toString(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while saving your application. Please try again.'])
                ->withInput();
        }
    }
}
