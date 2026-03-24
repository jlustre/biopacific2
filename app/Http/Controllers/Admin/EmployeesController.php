<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BPEmployee;
use App\Models\Facility;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EmployeesController extends Controller
{
    /**
     * Update only the user's email from the modal form.
     * Route: PUT admin/employees/{user}/update-email
     */
    public function updateEmail(Request $request, $userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        // Flash a warning that this will affect login credentials
        session()->flash('warning', 'Changing the email will affect the user\'s login credentials. The user will need to use the new email to log in.');

        $user->email = $validated['email'];
        $user->save();
        return redirect()->back()->with('success', 'Email updated successfully.');
    }
    public function index(Request $request)
    {
        $facilities = Facility::all();
        $departments = \App\Models\BPDepartment::all();
        $positions = \App\Models\BPPosition::all();
        $query = BPEmployee::query();

        // Filter by facility (current assignment only)
        if ($request->filled('facility')) {
            $query->whereHas('currentAssignment', function ($q) use ($request) {
                $q->where('facility_id', $request->facility);
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('dept_id', $request->department);
            });
        }

        // Filter by position
        if ($request->filled('position')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('job_code_id', $request->position);
            });
        }

        // Filter by union status
        if ($request->filled('union')) {
            if ($request->union === 'union') {
                $query->whereHas('assignments', function ($q) {
                    $q->whereNotNull('bargaining_unit_id');
                });
            } elseif ($request->union === 'non-union') {
                $query->whereHas('assignments', function ($q) {
                    $q->whereNull('bargaining_unit_id');
                });
            }
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        $perPage = $request->input('per_page', 10);
        $employees = $query->with([
            'currentAssignment',
            'currentAssignment.facility',
            'currentAssignment.department',
            'currentAssignment.position',
        ])->paginate($perPage)->appends($request->except('page'));

        // dd($employees->toArray());

        return view('admin.facilities.employees', compact('employees', 'facilities', 'departments', 'positions', 'perPage'));
    }
    /**
     * Display the specified employee details for modal.
     */
    public function show($emp_id)
    {
        $employee = \App\Models\BPEmployee::with([
            'currentAssignment',
            'currentAssignment.facility',
            'currentAssignment.department',
            'currentAssignment.position',
        ])->findOrFail($emp_id);
        return view('admin.facilities.employee-details', compact('employee'));
    }
    /**
     * Show the form for editing the specified employee.
     */
    public function edit($emp_id)
    {
        $employee = \App\Models\BPEmployee::with(['phones', 'addresses', 'user', 'currentAssignment'])->findOrFail($emp_id);
        $departments = \App\Models\BPDepartment::all();
        $positions = \App\Models\BPPosition::all();
        $facilities = \App\Models\Facility::all();
        $checklistItems = \App\Models\ChecklistItem::all();
        $empChecklists = \App\Models\BPEmpChecklist::where('emp_id', $emp_id)->get();
        $users = \App\Models\User::all();

        // PART F: Load all assessment periods for this employee
        $assessmentPeriods = \App\Models\EmployeeAssessmentPeriod::orderBy('date_from', 'desc')->get();
        $selectedAssessmentPeriodId = request('assessment_period_id') ?: ($assessmentPeriods->first()->id ?? null);

        // Load performance assessment items and review date for this employee and selected assessment period
        $empPerformanceChecklist = [];
        $reviewDate = '';
        $reviewerName = '';
        $reviewType = '';
        if ($selectedAssessmentPeriodId) {
            $assessment = \App\Models\EmployeePerformanceAssessment::where('emp_id', $emp_id)
                ->where('assessment_period_id', $selectedAssessmentPeriodId)
                ->first();
            $period = \App\Models\EmployeeAssessmentPeriod::find($selectedAssessmentPeriodId);
            if ($period) {
                $reviewType = $period->review_type === 'Q' ? 'Quarterly' : 'Annual';
            }
            if ($assessment) {
                if ($assessment->items) {
                    $empPerformanceChecklist = json_decode($assessment->items, true);
                }
                $reviewDate = $assessment->review_dt;
                // Lookup reviewer name if assessed_by is set
                if ($assessment->assessed_by) {
                    $reviewerUser = \App\Models\User::find($assessment->assessed_by);
                    $reviewerName = $reviewerUser ? $reviewerUser->name : '';
                }
            }
        }

        // PART F: Load section comments for this employee and assessment period
        $sectionComments = [];
        if ($selectedAssessmentPeriodId) {
            $comments = \App\Models\EmployeePerformanceSectionComment::where('emp_id', $emp_id)
                ->where('assessment_period_id', $selectedAssessmentPeriodId)
                ->get();
            foreach ($comments as $comment) {
                $sectionComments[$comment->doc_type_id] = $comment->comment;
            }
        }

        // Supervisor name logic: use Reports To if available, else logged-in user
        $supervisorName = '';
        $assignment = $employee->currentAssignment;
        if ($assignment && $assignment->reports_to_emp_id) {
            $supervisorEmp = \App\Models\BPEmployee::where('emp_id', $assignment->reports_to_emp_id)->first();
            if ($supervisorEmp && $supervisorEmp->user) {
                $supervisorName = $supervisorEmp->user->name;
            } elseif ($supervisorEmp) {
                $supervisorName = trim($supervisorEmp->last_name . ', ' . $supervisorEmp->first_name . ($supervisorEmp->middle_name ? ' ' . $supervisorEmp->middle_name : ''));
            }
        }
        if (!$supervisorName && auth()->check()) {
            $supervisorName = auth()->user()->name;
        }

        // Get doc_type_id for each section
        $areasDevDocType = \App\Models\DocType::where('name', 'Areas Requiring Further Development')->first();
        $devPlansDocType = \App\Models\DocType::where('name', 'Development Plans')->first();
        $empCommentsDocType = \App\Models\DocType::where('name', 'Employee Comments')->first();

        $areasForDevelopment = $areasDevDocType && isset($sectionComments[$areasDevDocType->id]) ? $sectionComments[$areasDevDocType->id] : '';
        $developmentPlans = $devPlansDocType && isset($sectionComments[$devPlansDocType->id]) ? $sectionComments[$devPlansDocType->id] : '';
        $employeeComments = $empCommentsDocType && isset($sectionComments[$empCommentsDocType->id]) ? $sectionComments[$empCommentsDocType->id] : '';

        $reviewDt = $reviewDate; // For PART F form field
        return view('admin.facilities.employee.edit_employee', compact(
            'employee',
            'departments',
            'positions',
            'facilities',
            'checklistItems',
            'empChecklists',
            'users',
            'empPerformanceChecklist',
            'assessmentPeriods',
            'selectedAssessmentPeriodId',
            'sectionComments',
            'supervisorName',
            'areasForDevelopment',
            'developmentPlans',
            'employeeComments',
            'reviewDate',
            'reviewerName',
            'reviewType',
            'reviewDt'
        ));
    }

    /**
     * Update the specified employee's personal info (tabbed form).
     */
    public function updatePersonal(Request $request, $emp_id)
    {
        $employee = \App\Models\BPEmployee::with('user')->findOrFail($emp_id);
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|string|max:255',
                'emp_id' => 'nullable|string|max:255',
                'ssn' => 'nullable|string|max:255',
                'original_hire_dt' => 'nullable|date',
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'dob' => 'nullable|date',
                'gender' => 'required|in:M,F,O,N',
                'email' => [
                    'nullable',
                    'string',
                    'email',
                    'max:255',
                    \Illuminate\Validation\Rule::unique('users', 'email')->ignore($employee->user_id),
                ],
            ]);
            $user = Auth::user();
            $isHrrd = $user && method_exists($user, 'hasRole') && $user->hasRole('hrrd');
            $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
            $isSelf = $user && ($user->id == $employee->user_id);
            // Only allow SSN update if the input is all digits (not masked)
            $ssnInput = $validated['ssn'] ?? null;
            $ssnIsAllDigits = $ssnInput && preg_match('/^\d+$/', $ssnInput);
            $canUpdateSsn = ($isHrrd || $isAdmin || $isSelf) && $ssnIsAllDigits;
            if (!$canUpdateSsn) {
                unset($validated['ssn']); // Prevent masked or unauthorized SSN update
            }
            $employee->fill($validated);
            $dirty = $employee->isDirty();
            $employee->save();

            // Update email in user table if provided
            if (isset($validated['email']) && $employee->user) {
                $employee->user->email = $validated['email'];
                $employee->user->save();
            }

            $msg = $dirty ? 'Personal information updated successfully.' : 'No changes were made, but your profile is up to date.';
            return redirect()->route('admin.employees.edit', $employee->emp_id)
                ->with('success', $msg);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.employees.edit', $employee->emp_id)
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('admin.employees.edit', $employee->emp_id)
                ->with('error', 'Failed to update personal information: ' . $e->getMessage())
                ->withInput();
        }
    }

        /**
         * Add a phone to an employee, ensuring only one primary phone.
         */
        public function addPhone(Request $request, $employee)
        {
            $validated = $request->validate([
                'phone_type' => 'required|string|max:50',
                'phone_number' => 'required|string|max:50',
                'is_primary' => 'nullable', // Remove boolean rule
            ]);

            // Checkbox submits as 'on' if checked, so use has()
            $isPrimary = $request->has('is_primary');

            // If this phone is set as primary, unset all other primary phones for this employee
            if ($isPrimary) {
                \App\Models\BPEmpPhone::where('emp_id', $employee)
                    ->where('is_primary', 1)
                    ->update(['is_primary' => 0]);
            }

            $phone = new \App\Models\BPEmpPhone();
            $phone->emp_id = $employee;
            $phone->phone_type = $validated['phone_type'];
            $phone->phone_number = $validated['phone_number'];
            $phone->is_primary = $isPrimary ? 1 : 0;
            $phone->save();

            return back()->with('success', 'Phone added successfully.');
        }

        /**
         * Update a phone for an employee.
         */
        public function updatePhone(Request $request, $employee, $phone)
        {
            $validated = $request->validate([
                'phone_type' => 'required|string|max:50',
                'phone_number' => 'required|string|max:50',
                'is_primary' => 'nullable',
            ]);

            $phoneModel = \App\Models\BPEmpPhone::where('emp_id', $employee)->where('phone_id', $phone)->firstOrFail();

            $isPrimary = $request->has('is_primary');
            if ($isPrimary) {
                \App\Models\BPEmpPhone::where('emp_id', $employee)
                    ->where('is_primary', 1)
                    ->update(['is_primary' => 0]);
            }

            $phoneModel->phone_type = $validated['phone_type'];
            $phoneModel->phone_number = $validated['phone_number'];
            $phoneModel->is_primary = $isPrimary ? 1 : 0;
            $phoneModel->save();

            return back()->with('success', 'Phone updated successfully.');
        }
    /**
     * Add or update an address for an employee (tabbed form).
     */
    public function updateAddress(Request $request, $employee)
    {
        $validated = $request->validate([
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_primary' => 'required|in:0,1',
            'address_type' => 'required|in:h,w,o',
            'effdt' => 'required|date',
            'effseq' => 'nullable|integer',
        ]);

        // If is_primary is set, unset all other primary addresses for this employee
        if ($validated['is_primary'] == '1') {
            \App\Models\BPEmpAddress::where('emp_id', $employee)
                ->where('is_primary', 1)
                ->update(['is_primary' => 0]);
        }

        // If effseq is present, update existing address; else, add new with correct effseq
        if (isset($validated['effseq']) && $validated['effseq'] !== '') {
            // Update existing address
            $address = \App\Models\BPEmpAddress::where([
                'emp_id' => $employee,
                'effdt' => $validated['effdt'],
                'effseq' => $validated['effseq'],
                'address_type' => $validated['address_type'],
            ])->first();
            if ($address) {
                $address->fill($validated);
                $address->save();
                $msg = 'Address updated successfully.';
            } else {
                // fallback: create new if not found
                $validated['emp_id'] = $employee;
                \App\Models\BPEmpAddress::create($validated);
                $msg = 'Address added successfully.';
            }
        } else {
            // Add new address, determine effseq
            $latest = \App\Models\BPEmpAddress::where('emp_id', $employee)
                ->where('address_type', $validated['address_type'])
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();
            $effseq = 0;
            if ($latest && $latest->effdt === $validated['effdt']) {
                $effseq = $latest->effseq + 1;
            }
            $validated['effseq'] = $effseq;
            $validated['emp_id'] = $employee;
            \App\Models\BPEmpAddress::create($validated);
            $msg = 'Address added successfully.';
        }

        return redirect()->route('admin.employees.edit', $employee)
            ->with('success', $msg);
    }
    /**
     * Add or update an assignment for an employee (tabbed form).
     */
    public function updateAssignment(Request $request, $employee)
    {
        $validated = $request->validate([
            'facility_id' => 'required|integer',
            'dept_id' => 'required|integer',
            'job_code_id' => 'required|integer',
            'reports_to_emp_id' => 'nullable|integer',
            'reg_temp' => 'required|in:r,t',
            'full_part_time' => 'required|in:ft,pt,pd',
            'bargaining_unit_id' => 'nullable|integer',
            'union_seniority_dt' => 'nullable|date',
            'effdt' => 'required|date',
            'effseq' => 'nullable|integer',
        ]);
        $userId = Auth::id();
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;

        // If effseq is present, update existing assignment; else, add new with correct effseq
        if (isset($validated['effseq']) && $validated['effseq'] !== '') {
            // Update existing assignment
            $assignment = \App\Models\BPEmpAssignment::where([
                'emp_id' => $employee,
                'effdt' => $validated['effdt'],
                'effseq' => $validated['effseq'],
            ])->first();
            if ($assignment) {
                $assignment->fill($validated);
                $assignment->save();
                $msg = 'Assignment updated successfully.';
            } else {
                // fallback: create new if not found
                $validated['emp_id'] = $employee;
                \App\Models\BPEmpAssignment::create($validated);
                $msg = 'Assignment added successfully.';
            }
        } else {
            // Add new assignment, determine effseq
            $latest = \App\Models\BPEmpAssignment::where('emp_id', $employee)
                ->where('facility_id', $validated['facility_id'])
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();
            $effseq = 0;
            if ($latest && $latest->effdt === $validated['effdt']) {
                $effseq = $latest->effseq + 1;
            }
            $validated['effseq'] = $effseq;
            $validated['emp_id'] = $employee;
            \App\Models\BPEmpAssignment::create($validated);
            $msg = 'Assignment added successfully.';
        }

        return redirect()->route('admin.employees.edit', $employee)
            ->with('success', $msg);
    }

    /**
     * Save checklist verification from modal (AJAX).
     */
    public function saveChecklistVerification(Request $request, $employee)
    {
        try {
            // Log::debug('Checklist verification request received', [
            //     'employee' => $employee,
            //     'request' => $request->all(),
            // ]);
            $validated = $request->validate([
                'doc_name' => 'required|string|max:255',
                'doc_type_id' => 'required|integer',
                'on_file' => 'required|boolean',
                'verified_dt' => 'nullable|date',
                'exp_dt' => 'nullable|date',
                'comments' => 'nullable|string|max:1000',
                'exp_dt_not_required' => 'nullable|boolean',
            ]);

            $userId = Auth::id();
            $checklist = \App\Models\BPEmpChecklist::firstOrNew(['emp_id' => $employee]);
            $items = $checklist->items ?? [];
            $docName = $validated['doc_name'];
            $items[$docName] = [
                'doc_type_id' => $validated['doc_type_id'],
                'on_file' => $validated['on_file'],
                'verified_dt' => $validated['verified_dt'] ?? now()->toDateString(),
                'exp_dt' => !empty($validated['exp_dt_not_required']) ? null : ($validated['exp_dt'] ?? null),
                'comments' => $validated['comments'] ?? null,
                'verified_by' => $userId,
                'exp_dt_not_required' => !empty($validated['exp_dt_not_required']) ? 1 : 0,
            ];
            $checklist->items = $items;
            $checklist->save();

            // Log::debug('Checklist verification saved successfully', [
            //     'emp_id' => $employee,
            //     'doc_name' => $docName,
            //     'item' => $items[$docName],
            // ]);

            // Lookup user name for verified_by
            $verifiedByName = null;
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $verifiedByName = $user->name;
                }
            }
            $itemWithName = $items[$docName];
            $itemWithName['verified_by_name'] = $verifiedByName;
            return response()->json([
                'success' => true,
                'message' => 'Checklist verification saved.',
                'data' => [
                    'doc_name' => $docName,
                    'item' => $itemWithName,
                ]
            ]);
        } catch (\Exception $e) {
            // Log::error('Checklist verification save error', [
            //     'error' => $e->getMessage(),
            //     'employee' => $employee,
            //     'request' => $request->all(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Revoke (delete) a checklist item for an employee (AJAX).
     */
    public function revokeChecklistItem(Request $request, $employee)
    {
        try {
            $validated = $request->validate([
                'doc_name' => 'required|string|max:255',
            ]);
            $docName = $validated['doc_name'];
            $checklist = \App\Models\BPEmpChecklist::where('emp_id', $employee)->first();
            $itemData = null;
            if ($checklist && is_array($checklist->items) && array_key_exists($docName, $checklist->items)) {
                $items = $checklist->items;
                unset($items[$docName]);
                $checklist->items = $items;
                // If no items left, delete the row, else save
                if (empty($items)) {
                    $checklist->delete();
                } else {
                    $checklist->save();
                }
            }
            // After revoke, return default/empty item for UI update
            $itemData = [
                'on_file' => false,
                'verified_dt' => null,
                'exp_dt' => null,
                'comments' => '',
                'verified_by' => null,
                'verified_by_name' => '',
                'exp_dt_not_required' => 0
            ];
            return response()->json(['success' => true, 'data' => ['item' => $itemData], 'message' => 'Checklist item revoked.']);
        } catch (\Exception $e) {
            // Log::error('Checklist revoke error', [
            //     'error' => $e->getMessage(),
            //     'employee' => $employee,
            //     'request' => $request->all(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Areas for Development (PART F) for an employee and assessment period.
     */
    public function saveAreasDevelopment(Request $request, $emp_id)
    {
        $rules = [
            'supervisor_name' => 'required|string|max:255',
            'employee_name' => 'required|string|max:255',
            'review_dt' => ($request->input('action') === 'submit' ? 'required' : 'nullable') . '|date',
            'employee_acknowledge_dt' => 'nullable|date',
            // Add more validation as needed for the actual fields
        ];
        $validated = $request->validate($rules);

        $assessmentPeriodId = $request->input('assessment_period_id');
        if (!$assessmentPeriodId) {
            return back()->with('error', 'Assessment period is required.');
        }

        // Save Areas Requiring Further Development
        $areasDevDocType = \App\Models\DocType::where('name', 'Areas Requiring Further Development')->first();
        if ($areasDevDocType) {
            \App\Models\EmployeePerformanceSectionComment::updateOrCreate(
                [
                    'emp_id' => $emp_id,
                    'assessment_period_id' => $assessmentPeriodId,
                    'doc_type_id' => $areasDevDocType->id,
                ],
                [
                    'comment' => $request->input('areas_for_development', ''),
                ]
            );
        }
        // Save Development Plans
        $devPlansDocType = \App\Models\DocType::where('name', 'Development Plans')->first();
        if ($devPlansDocType) {
            \App\Models\EmployeePerformanceSectionComment::updateOrCreate(
                [
                    'emp_id' => $emp_id,
                    'assessment_period_id' => $assessmentPeriodId,
                    'doc_type_id' => $devPlansDocType->id,
                ],
                [
                    'comment' => $request->input('development_plans', ''),
                ]
            );
        }
        // Save Employee Comments
        $empCommentsDocType = \App\Models\DocType::where('name', 'Employee Comments')->first();
        if ($empCommentsDocType) {
            \App\Models\EmployeePerformanceSectionComment::updateOrCreate(
                [
                    'emp_id' => $emp_id,
                    'assessment_period_id' => $assessmentPeriodId,
                    'doc_type_id' => $empCommentsDocType->id,
                ],
                [
                    'comment' => $request->input('employee_comments', ''),
                ]
            );
        }

        // Set review_dt and acknowledge_dt on EmployeePerformanceAssessment
        $assessment = \App\Models\EmployeePerformanceAssessment::where('emp_id', $emp_id)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->first();
        if ($assessment) {
            $assessment->review_dt = $request->input('review_dt');
            if ($request->filled('employee_acknowledge_dt')) {
                $assessment->acknowledge_dt = $request->input('employee_acknowledge_dt');
            }
            // If submitted, finalize and send email
            if ($request->input('action') === 'submit') {
                $assessment->finalized = 1;
                // TODO: Implement email notification to employee
                // \Mail::to($assessment->employee->user->email)->send(new AssessmentSubmittedMail($assessment));
            }
            $assessment->save();
        }

        if ($request->input('action') === 'submit') {
            return back()->with('success', 'Assessment submitted and employee notified.');
        }
        return back()->with('success', 'Areas for Development saved successfully.');
    }
}
