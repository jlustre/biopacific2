<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\PhoneHelper;
use Illuminate\Http\Request;
use App\Models\BPEmpAddress;
use App\Models\BPEmpPhone;
use App\Models\BPEmpTaxData;
use App\Models\BPEmployee;
use App\Models\Upload;
use App\Models\Facility;
use App\Models\ImportMappingPreset;
use App\Models\State;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SelectOption;
use App\Models\EmployeePerformanceAssessment;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\Optionstype;
use App\Support\PartFPerformanceScoring;
use App\Support\AssessmentWorkflowStatus;
use App\Support\CompetencyAssessmentWorkflowReadiness;
use App\Services\EmployeeAssessmentPeriodService;
use App\Support\EmployeeAssessmentPeriodCalculator;
use App\Support\RegistrationCodeService;
use App\Mail\EmployeeRegistrationInviteMail;
use App\Mail\EmployeeDocumentSubmissionMail;
use App\Mail\FacilityUploadNotificationMail;
use App\Support\MemberPortalLayout;
use App\Support\ImportMappingPresetAccess;
use App\Support\SelectedFacility;
use App\Support\UploadNotificationContext;
use App\Support\UploadSubmissionReason;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Concerns\HandlesEmployeeEditRedirects;


class EmployeesController extends Controller
{
    use HandlesEmployeeEditRedirects;

    /**
     * @param  list<string>  $with
     */
    protected function employeeFromRouteKey(string|int $routeKey, array $with = []): BPEmployee
    {
        return BPEmployee::findForAdminRoute($routeKey, $with);
    }

    protected function loadEmployeeAssessmentPeriods(BPEmployee $employee)
    {
        return app(EmployeeAssessmentPeriodService::class)->periodsForEmployee($employee);
    }

    protected function resolveSelectedAssessmentPeriodIdForEmployee(BPEmployee $employee, $assessmentPeriods): ?int
    {
        $reviewDate = request('assessment_review_date') ?: request('review_date');
        $on = $reviewDate ? \Illuminate\Support\Carbon::parse($reviewDate) : null;
        $service = app(EmployeeAssessmentPeriodService::class);

        $requested = null;
        if (request()->has('assessment_period_id')) {
            $requested = filled(request('assessment_period_id'))
                ? (int) request('assessment_period_id')
                : null;
        }

        $viewingHistoricalPeriod = request()->boolean('view_period');

        $resolved = $service->resolveActivePeriodIdForReview(
            $employee,
            $assessmentPeriods,
            $requested,
            $on,
            $viewingHistoricalPeriod,
        );

        if ($requested && $resolved === null) {
            $period = $assessmentPeriods->firstWhere('id', $requested);
            if ($period && ! EmployeeAssessmentPeriodCalculator::isPeriodLoadable($period)) {
                session()->flash(
                    'assessment_period_error',
                    'That assessment period is outside the loadable year range and cannot be opened for assessment work.'
                );
            } elseif ($period && (string) $period->employee_num !== (string) $employee->employee_num) {
                session()->flash('assessment_period_error', 'That assessment period does not belong to this employee.');
            }

            return null;
        }

        if ($requested && $resolved && $requested !== $resolved) {
            session()->flash(
                'assessment_period_notice',
                'The prior competency review period is completed. The current annual review period has been loaded so you can begin the next cycle.'
            );
        }

        return $resolved;
    }

    public function assessmentPeriodModalData(Request $request, $employee)
    {
        $employee = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employee);

        return response()->json([
            'success' => true,
            'data' => app(EmployeeAssessmentPeriodService::class)->modalDataForEmployee(
                $employee,
                $request->query('review_date')
            ),
        ]);
    }
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if (! $user->hasRole(['facility-admin', 'facility-dsd', 'don'])) {
            return null;
        }

        if ($user->facility_id) {
            return (int) $user->facility_id;
        }

        $employee = $user->resolvedBpEmployee(['currentAssignment']);
        if ($employee?->currentAssignment?->facility_id) {
            return (int) $employee->currentAssignment->facility_id;
        }

        return null;
    }

    protected function scopedDepartmentId(Request $request): ?int
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('don')) {
            return null;
        }

        $employee = $user->resolvedBpEmployee(['currentAssignment']);

        return $employee?->currentAssignment?->dept_id
            ? (int) $employee->currentAssignment->dept_id
            : null;
    }

    protected function facilitiesForUser(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return Facility::where('id', $scopedFacilityId)->orderBy('name')->get();
        }

        return Facility::orderBy('name')->get();
    }

    protected function resolveFacilityFromRoute($facility): ?Facility
    {
        if ($facility instanceof Facility) {
            return $facility;
        }

        if ($facility === null || $facility === '') {
            return null;
        }

        return Facility::query()
            ->where('id', $facility)
            ->orWhere('slug', $facility)
            ->first();
    }

    protected function resolveFacilityFilterId(Request $request, $facility = null): ?int
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return $scopedFacilityId;
        }

        $routeFacility = $this->resolveFacilityFromRoute($facility);

        if ($routeFacility) {
            return (int) $routeFacility->id;
        }

        if ($request->filled('facility')) {
            return (int) $request->facility;
        }

        $sessionFacilityId = SelectedFacility::id($request);
        if ($sessionFacilityId) {
            return $sessionFacilityId;
        }

        return null;
    }

    protected function authorizeEmployeeFacilityAccess(Request $request, BPEmployee $employee): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $scopedDepartmentId = $this->scopedDepartmentId($request);

        if (! $scopedFacilityId && ! $scopedDepartmentId) {
            return;
        }

        $employeeFacilityId = $employee->currentAssignment?->facility_id;
        $employeeDepartmentId = $employee->currentAssignment?->dept_id;

        if ($scopedFacilityId && $employeeFacilityId && (int) $employeeFacilityId !== $scopedFacilityId) {
            abort(403, 'You do not have access to employees at this facility.');
        }

        if ($scopedDepartmentId && $employeeDepartmentId && (int) $employeeDepartmentId !== $scopedDepartmentId) {
            abort(403, 'You do not have access to employees in this department.');
        }

        if ($scopedDepartmentId && ! $employeeDepartmentId) {
            abort(403, 'You do not have access to employees without a department assignment.');
        }
    }

    /**
     * Gate employee edit (and similar deep-links) for staff, the employee themselves, or assigned reviewers.
     */
    protected function authorizeEmployeeRecordAccess(Request $request, BPEmployee $employee): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // Trainee opening their own record (approval emails / checklist deep-links).
        if ((int) ($employee->user_id ?? 0) === (int) $user->id) {
            return;
        }

        if ($user->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don', 'facility-editor'])) {
            $this->authorizeEmployeeFacilityAccess($request, $employee);

            return;
        }

        if (app(\App\Services\EmployeeTrainingWorkflowService::class)->actorCanReview($user, $employee)) {
            return;
        }

        if (app(\App\Services\EmployeeDocumentVerificationService::class)->actorCanReview($user, $employee)) {
            return;
        }

        if (app(\App\Services\EmployeeDocumentVerificationService::class)->actorCanViewEmployeeDocumentHistory($user, $employee)) {
            return;
        }

        abort(403, 'You do not have access to this employee record.');
    }

    protected function canEditCoreEmployeeTabs($user): bool
    {
        return (bool) ($user && method_exists($user, 'can')
            && $user->can(\App\Support\Rbac\Permissions::EDIT_EMPLOYEE_CORE_TABS));
    }

    protected function authorizeUploadModification(Upload $upload): void
    {
        $user = Auth::user();

        if (! $upload->isOwnedBy($user)) {
            abort(403, 'You can only modify documents you uploaded.');
        }

        $employee = $upload->relationLoaded('employee')
            ? $upload->employee
            : $upload->employee()->first();

        $isSelfService = $employee
            && $this->isEmployeeSelfServiceRequest(request(), $employee);

        if ($isSelfService && $upload->isApproved()) {
            abort(403, 'Approved documents are read-only. Upload a new version to renew or replace them.');
        }

        if ($isSelfService && ! $upload->isCurrent()) {
            abort(403, 'Historical document versions are read-only.');
        }
    }

    /**
     * Employee (or reviewer/admin within role scope) may view/download current and historical uploads.
     */
    protected function authorizeUploadRead(Request $request, BPEmployee $employee, Upload $upload): void
    {
        if ($upload->employee_num !== $employee->employee_num) {
            abort(403, 'This document does not belong to this employee.');
        }

        $this->authorizeEmployeeRecordAccess($request, $employee);
    }

    protected function authorizeEmployeeDocumentNotification(Request $request, BPEmployee $employee): void
    {
        $user = Auth::user();

        if ($user && (int) $employee->user_id === (int) $user->id) {
            return;
        }

        $this->authorizeEmployeeFacilityAccess($request, $employee);

        if (! $user || ! (
            MemberPortalLayout::userIsSystemAdmin($user)
            || $user->hasRole(['rdhr', 'facility-admin', 'facility-dsd', 'facility-editor'])
        )) {
            abort(403, 'You are not authorized to send document notifications for this employee.');
        }
    }

    /**
     * @return array{upload: Upload, facility: Facility, email: string, expiryTier: string}
     */
    protected function resolveEmployeeUploadNotificationContext(BPEmployee $employee, Upload $upload): array
    {
        if ($upload->employee_num !== $employee->employee_num) {
            abort(403, 'This document does not belong to this employee.');
        }

        return UploadNotificationContext::resolve($upload);
    }

    protected function isEmployeeSelfServiceRequest(Request $request, BPEmployee $employee): bool
    {
        if ($request->routeIs('employment.*')) {
            return true;
        }

        $user = Auth::user();

        return $user && (int) $employee->user_id === (int) $user->id;
    }

    protected function isEmployeeDocumentSubmissionRequest(Request $request, BPEmployee $employee): bool
    {
        return $this->isEmployeeSelfServiceRequest($request, $employee);
    }

    protected function authorizeDocumentVerification(Request $request, BPEmployee $employee, Upload $upload): void
    {
        if ($upload->employee_num !== $employee->employee_num) {
            abort(403, 'This document does not belong to this employee.');
        }

        $user = Auth::user();
        if (
            ! $user
            || ! app(\App\Services\EmployeeDocumentVerificationService::class)->actorCanReview($user, $employee)
        ) {
            abort(403, 'You are not authorized to verify employee documents.');
        }

        $this->authorizeEmployeeFacilityAccess($request, $employee);
    }

    protected function resolveChecklistItemForUpload(Upload $upload): ?\App\Models\ChecklistItem
    {
        if ($upload->checklist_item_id) {
            return \App\Models\ChecklistItem::query()->find($upload->checklist_item_id);
        }

        $upload->loadMissing('uploadType.checklistItem');

        return $upload->uploadType?->checklistItem;
    }

    /**
     * Resolve route {employee} by primary key or employee_num (e.g. EMP022).
     */
    protected function resolveEmployee($employee): BPEmployee
    {
        if ($employee instanceof BPEmployee) {
            return $employee->relationLoaded('currentAssignment')
                ? $employee
                : $employee->load('currentAssignment');
        }

        return BPEmployee::query()
            ->with('currentAssignment')
            ->whereKey($employee)
            ->orWhere('employee_num', $employee)
            ->firstOrFail();
    }

    /**
     * Handle employee document upload.
     */
    public function uploadDocument(Request $request, $employee_num)
    {
        $uploadTypeId = $request->input('upload_type_id');
        $uploadType = $uploadTypeId ? \App\Models\UploadType::with('checklistItem')->find($uploadTypeId) : null;
        $checklistItem = $uploadType?->checklistItem;
        $requiresExpiry = (bool) ($uploadType?->requires_expiry);

        $rules = [
            'upload_type_id' => 'required|exists:upload_types,id',
            'document' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:255',
            'comments' => 'nullable|string|max:255',
        ];
        if ($requiresExpiry) {
            $rules['expires_at'] = 'required|date|after_or_equal:effective_start_date';
        } else {
            $rules['expires_at'] = 'nullable|date|after_or_equal:effective_start_date';
        }
        $rules['effective_start_date'] = 'nullable|date';

        $employee = $this->employeeFromRouteKey($employee_num, ['currentAssignment']);
        $isSelfService = $this->isEmployeeSelfServiceRequest($request, $employee);

        if ($isSelfService) {
            $rules['submission_reason'] = 'required|string|in:' . implode(',', UploadSubmissionReason::keys());
        }

        $validated = $request->validate($rules, [
            'expires_at.after_or_equal' => 'The expiration date must be on or after the Effective Start Date.',
            'submission_reason.required' => 'Please select why you are uploading this document.',
        ]);

        $this->authorizeEmployeeFacilityAccess($request, $employee);
        $facilityId = $employee->currentAssignment?->facility_id;
        if (!$facilityId) {
            return $this->redirectToEmployeeEdit($employee->id, 'documents', [
                'error' => 'The employee must have a current assignment with a facility before documents can be uploaded.',
            ])->withInput();
        }

        if ($checklistItem) {
            $positionId = $employee->currentAssignment?->position_id
                ?? $employee->currentAssignment?->position?->id;

            $isApplicable = \App\Models\ChecklistItem::query()
                ->whereKey($checklistItem->id)
                ->applicableToPosition($positionId)
                ->whereIn('section', \App\Services\ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)
                ->exists();

            if (!$isApplicable) {
                return $this->redirectToEmployeeEdit($employee->id, 'documents', [
                    'error' => 'The selected document type does not apply to this employee\'s position.',
                ])->withInput();
            }
        } elseif ($uploadType && !app(\App\Services\EmployeeDocumentRequirementsService::class)->canUploadTypeForEmployee(Auth::user(), $employee, $uploadType)) {
            return $this->redirectToEmployeeEdit($employee->id, 'documents', [
                'error' => 'The selected document type is not required for this employee\'s position.',
            ])->withInput();
        }

        $file = $request->file('document');
        $path = Upload::storeEmployeeFile($file, $employee->employee_num);

        $uploadAttributes = [
            'facility_id' => $facilityId,
            'employee_num' => $employee->employee_num,
            'user_id' => Auth::id(),
            'upload_type_id' => $uploadTypeId,
            'checklist_item_id' => $checklistItem?->id,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'uploaded_at' => now(),
            'comments' => $validated['comments'] ?? ($validated['description'] ?? null),
            'effective_start_date' => $validated['effective_start_date'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ];

        $reviewer = Auth::user();
        if ($isSelfService) {
            $uploadAttributes['submission_reason'] = $validated['submission_reason'];
            $uploadAttributes['verification_status'] = Upload::VERIFICATION_PENDING;
            $uploadAttributes['submitted_for_review_at'] = now();
        } elseif ($reviewer && $reviewer->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            $uploadAttributes['verification_status'] = Upload::VERIFICATION_APPROVED;
            $uploadAttributes['verified_by_user_id'] = $reviewer->id;
            $uploadAttributes['verified_at'] = now();
        }

        $upload = Upload::create($uploadAttributes);
        app(\App\Services\DocumentUploadLifecycleService::class)->supersedePriorCurrents($upload);

        if ($isSelfService) {
            try {
                app(\App\Services\EmployeeDocumentVerificationService::class)->handlePendingSubmission(
                    $upload,
                    $employee,
                    Auth::user(),
                    $validated['submission_reason'] ?? null,
                );
            } catch (\Throwable $e) {
                Log::error('Employee document submission notification failed', [
                    'upload_id' => $upload->id,
                    'employee_id' => $employee->id,
                    'exception' => $e,
                ]);
            }
        }

        if ($checklistItem) {
            \App\Support\EmployeeChecklistDocuments::markOnFile(
                $employee,
                $checklistItem,
                isset($validated['expires_at']) ? (string) $validated['expires_at'] : null
            );

            if ($upload->verification_status === Upload::VERIFICATION_APPROVED) {
                \App\Support\EmployeeChecklistDocuments::markVerified(
                    $employee,
                    $checklistItem,
                    now()->toDateString(),
                    isset($validated['expires_at']) ? (string) $validated['expires_at'] : null,
                    Auth::id()
                );
            }
        }

        return $this->redirectToEmployeeEdit($employee->id, 'documents', [
            'success' => $isSelfService
                ? 'Document uploaded and submitted for approval. Your DSD or supervisor will review it.'
                : config('documents.messages.created'),
        ]);
    }

        /**
     * Show the employee profile page (tabbed view).
     */
    public function showProfile(Request $request, $employee_num)
    {
        $employee = $this->employeeFromRouteKey($employee_num, ['currentAssignment']);
        $this->authorizeEmployeeFacilityAccess($request, $employee);
        $isAddMode = false;
        // Get the Optionstype id for 'marital status'
        $maritalType = Optionstype::where('name', 'marital status')->first();
        $maritalOptions = $maritalType
            ? SelectOption::where('type_id', $maritalType->id)->where('isActive', 1)->orderBy('sort_order')->get()
            : collect();
        // Get the Optionstype id for 'ethnic group'
        $ethnicType = Optionstype::where('name', 'ethnic group')->first();
        $ethnicOptions = $ethnicType
            ? SelectOption::where('type_id', $ethnicType->id)->where('isActive', 1)->orderBy('sort_order')->get()
            : collect();
        // Get the Optionstype id for 'military status'
        $militaryType = Optionstype::where('name', 'military status')->first();
        $militaryOptions = $militaryType
            ? SelectOption::where('type_id', $militaryType->id)->where('isActive', 1)->orderBy('sort_order')->get()
            : collect();
        // Get the Optionstype id for 'citizenship status'
        $citizenType = Optionstype::where('name', 'citizenship status')->first();
        $citizenOptions = $citizenType
            ? SelectOption::where('type_id', $citizenType->id)->where('isActive', 1)->orderBy('sort_order')->get()
            : collect();
        // You may want to load additional data as needed for the profile view
        return view('admin.facilities.employee.employee-profile', compact('employee', 'isAddMode', 'maritalOptions', 'ethnicOptions', 'militaryOptions', 'citizenOptions'));
    }

    /**
     * Delete an employee document.
     */
    public function deleteDocument($employee_num, $upload_id)
    {
        $employee = $this->employeeFromRouteKey($employee_num);
        $upload = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($upload_id)
            ->firstOrFail();
        $this->authorizeUploadModification($upload);

        // Soft-archive so prior versions (different expiration dates) remain in history.
        app(\App\Services\DocumentUploadLifecycleService::class)->archiveCurrent($upload);

        return $this->redirectToEmployeeEdit($employee->id, 'documents', [
            'success' => 'Document removed from the active list. Previous versions are preserved.',
        ]);
    }

     /**
     * Update an employee document (upload).
     */
    public function updateDocument(Request $request, $employee_num, $upload_id)
    {
        $employee = $this->employeeFromRouteKey($employee_num);
        $upload = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($upload_id)
            ->firstOrFail();
        $this->authorizeUploadModification($upload);

        $uploadTypeId = $request->input('upload_type_id');
        $uploadType = \App\Models\UploadType::find($uploadTypeId);
        $requiresExpiry = $uploadType && $uploadType->requires_expiry;

        $rules = [
            'upload_type_id' => 'required|exists:upload_types,id',
            'document' => 'nullable|file|max:10240', // 10MB max
            'comments' => 'nullable|string|max:255',
        ];
        if ($requiresExpiry) {
            $rules['expires_at'] = 'required|date|after_or_equal:effective_start_date';
        } else {
            $rules['expires_at'] = 'nullable|date|after_or_equal:effective_start_date';
        }
        $rules['effective_start_date'] = 'nullable|date';

        $validated = $request->validate($rules, [
            'expires_at.after_or_equal' => 'The expiration date must be on or after the Effective Start Date.',
        ]);

        $lifecycle = app(\App\Services\DocumentUploadLifecycleService::class);
        $previousAttributes = $upload->only([
            'expires_at',
            'effective_start_date',
            'file_path',
            'original_filename',
            'file_size',
            'comments',
            'uploaded_at',
            'submission_reason',
            'verification_status',
            'submitted_for_review_at',
            'verified_by_user_id',
            'verified_at',
            'verification_notes',
            'user_id',
        ]);

        $upload->upload_type_id = $uploadTypeId;
        $upload->expires_at = $validated['expires_at'] ?? null;
        $upload->effective_start_date = $validated['effective_start_date'] ?? null;
        $upload->comments = $validated['comments'] ?? null;

        if ($request->hasFile('document')) {
            // Keep the previous file for the preserved history snapshot.
            $file = $request->file('document');
            $path = Upload::storeEmployeeFile($file, $employee->employee_num);
            $upload->file_path = $path;
            $upload->original_filename = $file->getClientOriginalName();
            $upload->file_size = $file->getSize();
            $upload->user_id = Auth::id();
            $upload->uploaded_at = now();
            $upload->submission_reason = \App\Support\UploadSubmissionReason::CORRECTION;
            $upload->verification_status = Upload::VERIFICATION_PENDING;
            $upload->submitted_for_review_at = now();
            $upload->verified_by_user_id = null;
            $upload->verified_at = null;
            $upload->verification_notes = null;
        }

        $upload->save();
        $lifecycle->preservePreviousVersionBeforeUpdate($upload, $previousAttributes);
        $lifecycle->supersedePriorCurrents($upload);

        if ($request->hasFile('document')) {
            if ($upload->checklist_item_id) {
                $checklistItem = \App\Models\ChecklistItem::query()->find($upload->checklist_item_id);
                if ($checklistItem) {
                    \App\Support\EmployeeChecklistDocuments::markOnFile(
                        $employee,
                        $checklistItem,
                        optional($upload->expires_at)->toDateString(),
                    );
                }
            }

            try {
                app(\App\Services\EmployeeDocumentVerificationService::class)->handlePendingSubmission(
                    $upload->fresh(),
                    $employee,
                    Auth::user(),
                    \App\Support\UploadSubmissionReason::CORRECTION,
                );
            } catch (\Throwable $e) {
                Log::error('Corrected employee document notification failed', [
                    'upload_id' => $upload->id,
                    'employee_id' => $employee->id,
                    'exception' => $e,
                ]);
            }
        }

        return $this->redirectToEmployeeEdit($employee->id, 'documents', [
            'success' => 'Document updated successfully.',
        ]);
    }

       /**
     * Show the form for editing an employee document.
     */
    public function editDocument($employee_num, $upload_id)
    {
        $employee = $this->employeeFromRouteKey($employee_num);
        $upload = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($upload_id)
            ->firstOrFail();
        $this->authorizeUploadModification($upload);
        // You may want to pass upload types or other data as needed
        $uploadTypes = \App\Models\UploadType::catalogForEmployee($employee);
        return view('admin.facilities.employee.edit_document', compact('employee', 'upload', 'uploadTypes'));
    }

    public function previewDocumentNotification(Request $request, $employee_num, $upload_id)
    {
        try {
            $employee = $this->resolveEmployee($employee_num);
            $this->authorizeEmployeeDocumentNotification($request, $employee);
            $upload = Upload::query()
                ->where('employee_num', $employee->employee_num)
                ->whereKey($upload_id)
                ->firstOrFail();

            if ($this->isEmployeeDocumentSubmissionRequest($request, $employee)) {
                if (! $upload->isOwnedBy(Auth::user())) {
                    abort(403, 'You can only submit documents you uploaded.');
                }

                $context = UploadNotificationContext::resolveEmployeeSubmission($upload, $employee);

                return response()->json(
                    EmployeeDocumentSubmissionMail::previewPayload(
                        $context['upload'],
                        $employee,
                        $context['facility'],
                        Auth::user(),
                        implode(', ', $context['emails']),
                    )
                );
            }

            $context = $this->resolveEmployeeUploadNotificationContext($employee, $upload);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json(array_merge(
            FacilityUploadNotificationMail::previewPayload(
                $context['upload'],
                $context['facility'],
                Auth::user(),
                $context['expiryTier'],
                $context['email'],
            ),
            ['mode' => 'expiry'],
        ));
    }

    public function sendDocumentNotification(Request $request, $employee_num, $upload_id)
    {
        $employee = null;

        try {
            $employee = $this->resolveEmployee($employee_num);
            $this->authorizeEmployeeDocumentNotification($request, $employee);
            $upload = Upload::query()
                ->where('employee_num', $employee->employee_num)
                ->whereKey($upload_id)
                ->firstOrFail();

            if ($this->isEmployeeDocumentSubmissionRequest($request, $employee)) {
                return $this->sendEmployeeDocumentSubmission($request, $employee, $upload);
            }

            $context = $this->resolveEmployeeUploadNotificationContext($employee, $upload);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $this->redirectToEmployeeEdit($employee ?? $employee_num, 'documents', [
                'error' => $e->getMessage(),
            ]);
        }

        $upload = $context['upload'];
        $facility = $context['facility'];
        $email = $context['email'];
        $expiryTier = $context['expiryTier'];

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:10000',
        ]);

        $customSubject = trim((string) ($validated['subject'] ?? ''));
        $customMessage = trim((string) ($validated['message'] ?? ''));

        try {
            Mail::to($email)->send(new FacilityUploadNotificationMail(
                $upload,
                $facility,
                Auth::user(),
                $expiryTier,
                $customSubject !== '' ? $customSubject : null,
                $customMessage !== '' ? $customMessage : null,
            ));

            return $this->redirectToEmployeeEdit($employee, 'documents', [
                'success' => 'Notification sent to ' . $upload->employee->last_name . ', ' . $upload->employee->first_name . ' (' . $email . ').',
            ]);
        } catch (\Throwable $e) {
            Log::error('Employee document notification failed', [
                'upload_id' => $upload->id,
                'employee_id' => $employee->id,
                'email' => $email,
                'exception' => $e,
            ]);

            $message = config('app.debug')
                ? 'Failed to send notification: ' . $e->getMessage()
                : 'Failed to send notification. Please try again.';

            return $this->redirectToEmployeeEdit($employee, 'documents', [
                'error' => $message,
            ]);
        }
    }

    protected function sendEmployeeDocumentSubmission(Request $request, BPEmployee $employee, Upload $upload)
    {
        if (! $upload->isOwnedBy(Auth::user())) {
            abort(403, 'You can only submit documents you uploaded.');
        }

        $context = UploadNotificationContext::resolveEmployeeSubmission($upload, $employee);

        $validated = $request->validate([
            'submission_reason' => 'required|string|in:' . implode(',', UploadSubmissionReason::keys()),
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:10000',
        ]);

        $submissionReason = $validated['submission_reason'];
        $customSubject = trim((string) ($validated['subject'] ?? ''));
        $customMessage = trim((string) ($validated['message'] ?? ''));

        $upload->update([
            'submission_reason' => $submissionReason,
            'verification_status' => Upload::VERIFICATION_PENDING,
            'submitted_for_review_at' => now(),
            'verified_by_user_id' => null,
            'verified_at' => null,
            'verification_notes' => null,
        ]);

        $upload = $context['upload']->fresh(['uploadType', 'employee']);
        $submittedBy = Auth::user();

        try {
            app(\App\Services\EmployeeDocumentVerificationService::class)->handlePendingSubmission(
                $upload,
                $employee,
                $submittedBy,
                $submissionReason,
                $customSubject !== '' ? $customSubject : null,
                $customMessage !== '' ? $customMessage : null,
            );

            return $this->redirectToEmployeeEdit($employee, 'documents', [
                'success' => 'Document submitted for approval. Reason: ' . UploadSubmissionReason::label($submissionReason) . '.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Employee document submission notification failed', [
                'upload_id' => $upload->id,
                'employee_id' => $employee->id,
                'exception' => $e,
            ]);

            $message = config('app.debug')
                ? 'Failed to send submission notification: ' . $e->getMessage()
                : 'Failed to send submission notification. Please try again.';

            return $this->redirectToEmployeeEdit($employee, 'documents', [
                'error' => $message,
            ]);
        }
    }

    protected function notifyLeadershipOfDocumentSubmission(
        Upload $upload,
        BPEmployee $employee,
        ?string $submissionReason = null,
        ?\App\Models\User $submittedBy = null,
        ?string $customSubject = null,
        ?string $customMessage = null,
    ): void {
        $submittedBy ??= Auth::user();
        if (! $submittedBy) {
            throw new \RuntimeException('No submitting user is available for this notification.');
        }

        app(\App\Services\EmployeeDocumentVerificationService::class)->handlePendingSubmission(
            $upload,
            $employee,
            $submittedBy,
            $submissionReason,
            $customSubject,
            $customMessage,
        );
    }

    public function approveDocument(Request $request, $employee_num, $upload_id)
    {
        $employee = $this->resolveEmployee($employee_num);
        $upload = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($upload_id)
            ->firstOrFail();

        $this->authorizeDocumentVerification($request, $employee, $upload);

        if (! $upload->isPendingVerification()) {
            return $this->redirectToEmployeeEdit($employee, 'documents', [
                'error' => 'Only documents pending for approval can be approved.',
            ]);
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $checklistItem = $this->resolveChecklistItemForUpload($upload);
        $upload->update([
            'verification_status' => Upload::VERIFICATION_APPROVED,
            'verified_by_user_id' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'] ?? null,
            'checklist_item_id' => $checklistItem?->id ?? $upload->checklist_item_id,
        ]);

        $verification = app(\App\Services\EmployeeDocumentVerificationService::class);
        $verification->completeOpenReviewTasks($upload->fresh(), Auth::user());

        if ($checklistItem) {
            \App\Support\EmployeeChecklistDocuments::markVerified(
                $employee,
                $checklistItem,
                now()->toDateString(),
                optional($upload->expires_at)->toDateString(),
                Auth::id()
            );
        }

        $mailSent = $verification->notifyEmployeeApproved($upload->fresh(['uploadType', 'checklistItem', 'verifiedBy']), $employee);

        return $this->redirectToEmployeeEdit($employee, 'documents', [
            'success' => $mailSent
                ? 'Document approved. The employee has been notified.'
                : 'Document approved. The employee can also see this confirmation in Messages (no email on file or mail failed).',
        ]);
    }

    public function rejectDocument(Request $request, $employee_num, $upload_id)
    {
        $employee = $this->resolveEmployee($employee_num);
        $upload = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($upload_id)
            ->firstOrFail();

        $this->authorizeDocumentVerification($request, $employee, $upload);

        if (! $upload->isPendingVerification()) {
            return $this->redirectToEmployeeEdit($employee, 'documents', [
                'error' => 'Only documents pending for approval can be rejected.',
            ]);
        }

        $validated = $request->validate([
            'verification_notes' => 'required|string|max:1000',
        ], [
            'verification_notes.required' => 'Please provide a reason for rejection.',
        ]);

        $checklistItem = $this->resolveChecklistItemForUpload($upload);
        $upload->update([
            'verification_status' => Upload::VERIFICATION_REJECTED,
            'verified_by_user_id' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'],
            'checklist_item_id' => $checklistItem?->id ?? $upload->checklist_item_id,
        ]);

        if ($checklistItem) {
            \App\Support\EmployeeChecklistDocuments::markOnFile(
                $employee,
                $checklistItem,
                optional($upload->expires_at)->toDateString(),
            );
        }

        $verification = app(\App\Services\EmployeeDocumentVerificationService::class);
        $task = $verification->handleRejection(
            $upload->fresh(['uploadType', 'checklistItem', 'verifiedBy', 'user']),
            $employee,
            Auth::user(),
            $validated['verification_notes'],
        );

        return $this->redirectToEmployeeEdit($employee, 'documents', [
            'success' => $task
                ? 'Document rejected with notes. The uploader received a correction task and notification.'
                : 'Document rejected with notes. The uploader was notified (no portal user was available for a task).',
        ]);
    }

    /**
     * View an employee document (shows file inline if possible, otherwise downloads).
     * Includes approved and historical versions on the employee’s file (read-only).
     */
    public function viewDocument(Request $request, $employee_num, $upload_id)
    {
        $employee = $this->employeeFromRouteKey($employee_num);
        $upload = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($upload_id)
            ->firstOrFail();
        $this->authorizeUploadRead($request, $employee, $upload);
        $filePath = storage_path('app/public/' . $upload->file_path);
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        $mimeType = mime_content_type($filePath);
        $disposition = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif']) ? 'inline' : 'attachment';
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $upload->original_filename . '"',
        ]);
    }

    /**
     * Download an employee document (current or historical version).
     */
    public function downloadDocument(Request $request, $employee_num, $document_id)
    {
        $employee = $this->employeeFromRouteKey($employee_num);
        $document = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->whereKey($document_id)
            ->firstOrFail();
        $this->authorizeUploadRead($request, $employee, $document);
        $filePath = storage_path('app/public/' . $document->file_path);
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        return response()->download($filePath, $document->original_filename);
    }

    
    /**
     * Update only the user's email from the modal form.
     * Route: PUT admin/employees/{user}/update-email
     */
    public function updateEmail(Request $request, $userId)
    {
        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return redirect()->back()->with('error', 'You have read-only access to this employee profile.');
        }

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
    
    public function index(Request $request, $facility = null)
    {
        $user = $request->user();
        $scopedFacilityId = $this->scopedFacilityId($request);
        $scopedDepartmentId = $this->scopedDepartmentId($request);
        $isDonDepartmentScoped = (bool) ($user && $user->hasRole('don') && $scopedDepartmentId);
        $facilityFilterId = $this->resolveFacilityFilterId($request, $facility);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;
        $globalImportFacilityId = (int) config('import-mapping.global_facility_id', 99);
        $canImportEmployees = ImportMappingPresetAccess::canUse($user);
        $employeeImportPresets = collect();

        if ($canImportEmployees) {
            $presetQuery = ImportMappingPreset::query()
                ->whereNotNull('mappings');

            if ($facilityFilterId) {
                $presetQuery->where(function ($query) use ($facilityFilterId, $globalImportFacilityId) {
                    $query->where('facility_id', $globalImportFacilityId)
                        ->orWhere('facility_id', $facilityFilterId);
                });
            }

            if (! $user?->hasRole(['admin', 'super-admin', 'rdhr'])) {
                $presetQuery->where(function ($query) use ($globalImportFacilityId, $user) {
                    $query->where('facility_id', $globalImportFacilityId)
                        ->orWhere('user_id', $user->id);
                });
            }

            $employeeImportPresets = $presetQuery
                ->orderBy('name')
                ->get()
                ->filter(fn (ImportMappingPreset $preset) => $preset->mappingsCount() > 0)
                ->values();
        }

        $importFacilities = $facilities
            ->where('id', '!=', $globalImportFacilityId)
            ->values();
        $importTargetFacilityId = $facilityFilterId;
        $globalId = $globalImportFacilityId;
        $parseWorkbookUrl = route('admin.facility.mapping-presets.parse-workbook');

        if ($isDonDepartmentScoped && ! $request->filled('department')) {
            $request->merge(['department' => $scopedDepartmentId]);
        }

        $selectedDepartmentId = $isDonDepartmentScoped
            ? $scopedDepartmentId
            : ($request->filled('department') ? (int) $request->department : null);

        $departments = $scopedDepartmentId
            ? \App\Models\Department::where('id', $scopedDepartmentId)->get()
            : \App\Models\Department::all();

        $positionsQuery = \App\Models\Position::query();
        if ($scopedDepartmentId) {
            $positionsQuery->where('department_id', $scopedDepartmentId);
        }
        $positions = $positionsQuery->orderBy('title')->get();

        $supervisorPositionsQuery = \App\Models\Position::query()->supervisorRoles();
        if ($scopedDepartmentId) {
            $supervisorPositionsQuery->where('department_id', $scopedDepartmentId);
        }
        $supervisorPositions = $supervisorPositionsQuery->orderBy('title')->get();

        $query = BPEmployee::query();
        // Filter by Reports To (supervisor position)
        if ($request->filled('reports_to')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('reports_to', $request->reports_to);
            });
        }

        // Filter by facility (current assignment only)
        if ($facilityFilterId) {
            $query->whereHas('currentAssignment', function ($q) use ($facilityFilterId) {
                $q->where('facility_id', $facilityFilterId);
            });
        }

        if ($scopedDepartmentId) {
            $query->whereHas('currentAssignment', function ($q) use ($scopedDepartmentId) {
                $q->where('dept_id', $scopedDepartmentId);
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
                $q->where('position_id', $request->position);
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
                                    ->orWhere('middle_name', 'like', "%$search%")
                                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                                    ->orWhere('employee_num', 'like', "%$search%");
                        });
        }

        $perPage = $request->input('per_page', 10);
        $employees = $query->orderedByName()->with([
            'user',
            'currentAssignment',
            'currentAssignment.facility',
            'currentAssignment.department',
            'currentAssignment.position',
        ])->paginate($perPage)->appends($request->except('page'));

        $registrationCodeService = app(RegistrationCodeService::class);
        $canGenerateRegistrationCodes = $registrationCodeService->canGenerateCodes($request->user());

        $activeRegistrationCodes = collect();
        if ($canGenerateRegistrationCodes && $employees->isNotEmpty()) {
            $activeRegistrationCodes = \App\Models\RegistrationCode::query()
                ->whereIn('employee_num', $employees->pluck('employee_num')->filter()->values())
                ->whereNull('used_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderByDesc('created_at')
                ->get()
                ->unique('employee_num')
                ->keyBy('employee_num');
        }

        // dd($employees->toArray());

        return view('admin.facilities.employees', compact(
            'employees',
            'facilities',
            'departments',
            'positions',
            'supervisorPositions',
            'perPage',
            'facilityFilterId',
            'scopedFacility',
            'scopedFacilityId',
            'isDonDepartmentScoped',
            'selectedDepartmentId',
            'canGenerateRegistrationCodes',
            'activeRegistrationCodes',
            'registrationCodeService',
            'canImportEmployees',
            'employeeImportPresets',
            'importFacilities',
            'importTargetFacilityId',
            'globalId',
            'parseWorkbookUrl',
        ));
    }
    /**
     * Display the specified employee details for modal.
     */
    public function show(Request $request, $employee_num)
    {
        $employee = $this->employeeFromRouteKey($employee_num, [
            'currentAssignment',
            'currentAssignment.facility',
            'currentAssignment.department',
            'currentAssignment.position',
        ]);
        $this->authorizeEmployeeFacilityAccess($request, $employee);
        return view('admin.facilities.employee-details', compact('employee'));
    }

    /**
     * Update the specified employee's personal info (tabbed form).
     */
    public function updatePersonal(Request $request, $employee_num)
    {
        $employee = $this->employeeFromRouteKey($employee_num, ['user', 'currentAssignment']);
        $this->authorizeEmployeeFacilityAccess($request, $employee);

        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return $this->redirectToEmployeeEdit($employee->id, 'personal', [
                'error' => 'You have read-only access to Personal information.',
            ]);
        }

        try {
            $validated = $request->validate([
                'user_id' => 'nullable|string|max:255',
                // 'employee_num' => 'nullable|string|max:255', // removed, not a column in bp_employees
                'ssn' => 'nullable|string|max:255',
                'original_hire_dt' => 'nullable|date',
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'dob' => 'nullable|date',
                'badge_num' => 'nullable|string|max:50',
                'badge_eff_dt' => 'nullable|date',
                'union_code' => 'nullable|string|max:50',
                'effdt_of_membership' => 'nullable|date',
                'action_id' => 'nullable|integer|exists:selectoptions,id',
                'marital_status_id' => 'nullable|integer|exists:selectoptions,id',
                'ethnic_group_id' => 'nullable|integer|exists:selectoptions,id',
                'military_status_id' => 'nullable|integer|exists:selectoptions,id',
                'citizenship_status_id' => 'nullable|integer|exists:selectoptions,id',
                'gender' => 'required|in:M,F,O,N',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    \Illuminate\Validation\Rule::unique('users', 'email')->ignore($employee->user_id),
                    \Illuminate\Validation\Rule::unique('bp_employees', 'email')->ignore($employee->id),
                ],
                'phone_number' => 'nullable|string|max:50',
                'phone_id' => [
                    'nullable',
                    'integer',
                    \Illuminate\Validation\Rule::exists('bp_emp_phones', 'phone_id')
                        ->where('employee_num', $employee->employee_num),
                ],
            ]);
            $phoneNumber = $validated['phone_number'] ?? null;
            $phoneId = $validated['phone_id'] ?? null;
            unset($validated['phone_number'], $validated['phone_id']);
            $user = Auth::user();
            $isRdhr = $user && method_exists($user, 'hasRole') && $user->hasRole('rdhr');
            $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
            $isSelf = $user && ($user->id == $employee->user_id);
            // Only allow SSN update if the input is all digits (not masked)
            $ssnInput = $validated['ssn'] ?? null;
            $ssnIsAllDigits = $ssnInput && preg_match('/^\d+$/', $ssnInput);
            $canUpdateSsn = ($isRdhr || $isAdmin || $isSelf) && $ssnIsAllDigits;
            if (!$canUpdateSsn) {
                unset($validated['ssn']); // Prevent masked or unauthorized SSN update
            }
            if (array_key_exists('dob', $validated)) {
                $employee->dob = filled($validated['dob'])
                    ? \Illuminate\Support\Carbon::parse($validated['dob'])->toDateString()
                    : null;
                unset($validated['dob']);
            }
            $employee->fill($validated);
            $employee->marital_status_id = $validated['marital_status_id'] ?? null;
            $employee->ethnic_group_id = $validated['ethnic_group_id'] ?? null;
            $employee->military_status_id = $validated['military_status_id'] ?? null;
            $employee->citizenship_status_id = $validated['citizenship_status_id'] ?? null;
            $email = $validated['email'];
            $dirty = $employee->isDirty();
            $employee->save();

            if ($employee->user && $employee->user->email !== $email) {
                $employee->user->email = $email;
                $employee->user->save();
                $dirty = true;
            }

            if ($this->syncPrimaryPhoneFromPersonalForm($employee, $phoneNumber, $phoneId)) {
                $dirty = true;
            }

            $msg = $dirty ? 'Personal information updated successfully.' : 'No changes were made, but your profile is up to date.';
            return $this->redirectToEmployeeEdit($employee->id, 'personal', ['success' => $msg]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->redirectToEmployeeEdit($employee->id, 'personal')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return $this->redirectToEmployeeEdit($employee->id, 'personal', [
                'error' => 'Failed to update personal information: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Add a phone to an employee, ensuring only one primary phone.
     */
    public function addPhone(Request $request, $employee)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employeeModel);

        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return $this->redirectToEmployeeEdit($employeeModel->id, 'personal', [
                'error' => 'You have read-only access to Personal information.',
            ]);
        }

        $validated = $request->validate([
            'phone_type' => 'required|string|max:50',
            'phone_number' => 'required|string|max:50',
            'is_primary' => 'nullable|in:Y,N,y,n,1,0,on',
            'effdt' => ['required', 'date', 'after_or_equal:' . now()->toDateString()],
            'effseq' => 'nullable|integer',
        ]);

        $isPrimary = $this->normalizeYnPrimary($request->input('is_primary'));
        if (! $this->employeeHasPrimaryPhone($employeeModel->employee_num) && $isPrimary === BPEmpPhone::PRIMARY_NO) {
            $isPrimary = BPEmpPhone::PRIMARY_YES;
        }
        if ($isPrimary === BPEmpPhone::PRIMARY_YES) {
            BPEmpPhone::where('employee_num', $employeeModel->employee_num)
                ->where('is_primary', BPEmpPhone::PRIMARY_YES)
                ->update(['is_primary' => BPEmpPhone::PRIMARY_NO]);
        }

        $effseq = 0;
        if ($request->filled('effseq')) {
            $effseq = (int) $validated['effseq'];
        } else {
            $latest = BPEmpPhone::where('employee_num', $employeeModel->employee_num)
                ->where('phone_type', $validated['phone_type'])
                ->where('effdt', $validated['effdt'])
                ->orderByDesc('effseq')
                ->first();
            if ($latest) {
                $effseq = $latest->effseq + 1;
            }
        }

        $phone = new BPEmpPhone();
        $phone->employee_num = $employeeModel->employee_num;
        $phone->phone_type = $validated['phone_type'];
        $phone->effdt = $validated['effdt'];
        $phone->effseq = $effseq;
        $phone->phone_number = $validated['phone_number'];
        $phone->is_primary = $isPrimary;
        $phone->save();

        return back()->with('success', 'Phone added successfully.');
    }

    /**
     * Update a phone for an employee.
     */
    public function updatePhone(Request $request, $employee, $phone)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employeeModel);

        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return $this->redirectToEmployeeEdit($employeeModel->id, 'personal', [
                'error' => 'You have read-only access to Personal information.',
            ]);
        }

        $validated = $request->validate([
            'phone_type' => 'required|string|max:50',
            'phone_number' => 'required|string|max:50',
            'is_primary' => 'nullable|in:Y,N,y,n,1,0,on',
            'effdt' => 'required|date',
            'effseq' => 'required|integer',
        ]);
        $phoneModel = BPEmpPhone::where('employee_num', $employeeModel->employee_num)->where('phone_id', $phone)->firstOrFail();

        $isPrimary = $this->normalizeYnPrimary($request->input('is_primary'));
        if (! $this->employeeHasPrimaryPhone($employeeModel->employee_num) && $isPrimary === BPEmpPhone::PRIMARY_NO) {
            $isPrimary = BPEmpPhone::PRIMARY_YES;
        }
        if ($isPrimary === BPEmpPhone::PRIMARY_YES) {
            BPEmpPhone::where('employee_num', $employeeModel->employee_num)
                ->where('is_primary', BPEmpPhone::PRIMARY_YES)
                ->update(['is_primary' => BPEmpPhone::PRIMARY_NO]);
        }

        $phoneModel->phone_type = $validated['phone_type'];
        $phoneModel->effdt = $validated['effdt'];
        $phoneModel->effseq = (int) $validated['effseq'];
        $phoneModel->phone_number = $validated['phone_number'];
        $phoneModel->is_primary = $isPrimary;
        $phoneModel->save();

        return back()->with('success', 'Phone updated successfully.');
    }
    /**
     * Add or update an address for an employee (tabbed form).
     */
    public function updateAddress(Request $request, $employee)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employeeModel);

        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return $this->redirectToEmployeeEdit($employeeModel->id, 'address', [
                'error' => 'You have read-only access to Address information.',
            ]);
        }

        // If effseq is present, it's an update; otherwise, it's an add
        $isUpdate = $request->filled('effseq') && $request->input('effseq') !== '';
        $rules = [
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_primary' => 'required|in:Y,N,y,n,0,1',
            'address_type' => 'required|in:H,W,O,M,h,w,o,m',
            'effdt' => ['required', 'date'],
            'effseq' => 'nullable|integer',
        ];
        if (!$isUpdate) {
            // Only require effdt to be today or later when adding
            $rules['effdt'][] = 'after_or_equal:' . now()->toDateString();
        }
        $validated = $request->validate($rules);
        $validated['is_primary'] = $this->normalizeYnPrimary($validated['is_primary']);
        $validated['address_type'] = strtoupper($validated['address_type']);

        if ($this->isEmployeeSelfServiceRequest($request, $employeeModel) && $isUpdate) {
            $latestAddress = BPEmpAddress::query()
                ->where('employee_num', $employeeModel->employee_num)
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();

            if (! $latestAddress
                || (string) $latestAddress->effdt !== (string) $validated['effdt']
                || (int) $latestAddress->effseq !== (int) $validated['effseq']) {
                return $this->redirectToEmployeeEdit($employee, 'address', [
                    'error' => 'Historical address records cannot be edited. Update your current address above, use Add New Address if you moved, or contact your DSD, facility administrator, or RDHR.',
                ]);
            }
        }

        // Enforce only one default address per employee
        if ($validated['is_primary'] === BPEmpAddress::PRIMARY_YES) {
            // If updating, allow this address to remain primary, but unset all others
            $query = BPEmpAddress::where('employee_num', $employeeModel->employee_num)
                ->where('is_primary', BPEmpAddress::PRIMARY_YES);
            if (isset($validated['effseq']) && $validated['effseq'] !== '') {
                $query->where(function($q) use ($validated) {
                    $q->where('effdt', '!=', $validated['effdt'])
                      ->orWhere('effseq', '!=', $validated['effseq'])
                      ->orWhere('address_type', '!=', $validated['address_type']);
                });
            }
            $query->update(['is_primary' => BPEmpAddress::PRIMARY_NO]);
        } else {
            // If trying to set is_primary=N, but there is no other primary, prevent unsetting the last default
            $otherPrimary = BPEmpAddress::where('employee_num', $employeeModel->employee_num)
                ->where('is_primary', BPEmpAddress::PRIMARY_YES);
            if (isset($validated['effseq']) && $validated['effseq'] !== '') {
                $otherPrimary->where(function($q) use ($validated) {
                    $q->where('effdt', '!=', $validated['effdt'])
                      ->orWhere('effseq', '!=', $validated['effseq'])
                      ->orWhere('address_type', '!=', $validated['address_type']);
                });
            }
            if ($otherPrimary->count() === 0) {
                return $this->redirectToEmployeeEdit($employee, 'address', [
                    'error' => 'At least one address must be set as default.',
                ]);
            }
        }

        // If effseq is present, update existing address; else, add new with correct effseq
        if (isset($validated['effseq']) && $validated['effseq'] !== '') {
            // Update existing address
            $address = BPEmpAddress::where('employee_num', $employeeModel->employee_num)
                ->where('effdt', $validated['effdt'])
                ->where('effseq', $validated['effseq'])
                ->first();
            if ($address) {
                $address->fill($validated);
                $address->save();
                $msg = 'Address updated successfully.';
            } else {
                // fallback: create new if not found
                $validated['employee_num'] = $employeeModel->employee_num;
                BPEmpAddress::create($validated);
                $msg = 'Address added successfully.';
            }
        } else {
            // Add new address, determine effseq
            $latest = BPEmpAddress::where('employee_num', $employeeModel->employee_num)
                ->where('address_type', $validated['address_type'])
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();
            $effseq = 0;
            if ($latest && $latest->effdt === $validated['effdt']) {
                $effseq = $latest->effseq + 1;
            }
            $validated['effseq'] = $effseq;
            $validated['employee_num'] = $employeeModel->employee_num;
            BPEmpAddress::create($validated);
            $msg = 'Address added successfully.';
        }

        return $this->redirectToEmployeeEdit($employee, 'address', ['success' => $msg]);
    }

    protected function normalizeYnPrimary(mixed $value): string
    {
        if (in_array($value, [BPEmpPhone::PRIMARY_YES, 'y', '1', 1, true, 'yes', 'on'], true)) {
            return BPEmpPhone::PRIMARY_YES;
        }

        return BPEmpPhone::PRIMARY_NO;
    }

    protected function employeeHasPrimaryPhone(string $employeeNum): bool
    {
        return BPEmpPhone::query()
            ->where('employee_num', $employeeNum)
            ->where('is_primary', BPEmpPhone::PRIMARY_YES)
            ->exists();
    }

    protected function syncPrimaryPhoneFromPersonalForm(BPEmployee $employee, ?string $phoneNumber, mixed $phoneId): bool
    {
        $employeeNum = $employee->employee_num;
        $dirty = false;

        if (filled($phoneId)) {
            $selected = BPEmpPhone::query()
                ->where('employee_num', $employeeNum)
                ->where('phone_id', (int) $phoneId)
                ->first();

            if ($selected) {
                if ($selected->is_primary !== BPEmpPhone::PRIMARY_YES) {
                    BPEmpPhone::query()
                        ->where('employee_num', $employeeNum)
                        ->where('is_primary', BPEmpPhone::PRIMARY_YES)
                        ->update(['is_primary' => BPEmpPhone::PRIMARY_NO]);
                    $selected->is_primary = BPEmpPhone::PRIMARY_YES;
                    $dirty = true;
                }

                if (filled($phoneNumber)) {
                    $normalizedPhone = PhoneHelper::normalizeForStorage($phoneNumber);
                    if ($selected->phone_number !== $normalizedPhone) {
                        $selected->phone_number = $phoneNumber;
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $selected->save();
                }

                return $dirty;
            }
        }

        if (! filled($phoneNumber)) {
            return false;
        }

        $primary = BPEmpPhone::query()
            ->where('employee_num', $employeeNum)
            ->where('is_primary', BPEmpPhone::PRIMARY_YES)
            ->first();

        if (! $primary) {
            $primary = BPEmpPhone::query()
                ->where('employee_num', $employeeNum)
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();
        }

        if ($primary) {
            if ($primary->is_primary !== BPEmpPhone::PRIMARY_YES) {
                BPEmpPhone::query()
                    ->where('employee_num', $employeeNum)
                    ->where('is_primary', BPEmpPhone::PRIMARY_YES)
                    ->update(['is_primary' => BPEmpPhone::PRIMARY_NO]);
                $primary->is_primary = BPEmpPhone::PRIMARY_YES;
                $dirty = true;
            }

            $normalizedPhone = PhoneHelper::normalizeForStorage($phoneNumber);
            if ($primary->phone_number !== $normalizedPhone) {
                $primary->phone_number = $phoneNumber;
                $dirty = true;
            }

            if ($dirty) {
                $primary->save();
            }

            return $dirty;
        }

        $phone = new BPEmpPhone();
        $phone->employee_num = $employeeNum;
        $phone->phone_type = 'M';
        $phone->effdt = now()->toDateString();
        $phone->effseq = 0;
        $phone->phone_number = $phoneNumber;
        $phone->is_primary = BPEmpPhone::PRIMARY_YES;
        $phone->save();

        return true;
    }

    /**
     * Add or update job data for an employee (tabbed form).
     */
    public function updateAssignment(Request $request, $employee)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employeeModel);

        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return $this->redirectToEmployeeEdit($employeeModel->id, 'job-data', [
                'error' => 'You have read-only access to Job Data.',
            ]);
        }

        if (! \App\Support\EmployeeJobDataAuthorization::canManageJobData($request->user(), $employeeModel)) {
            return $this->redirectToEmployeeEdit($employeeModel, 'job-data', [
                'error' => \App\Support\EmployeeJobDataAuthorization::SELF_EDIT_DENIED_MESSAGE,
            ]);
        }

        $employeeNum = $employeeModel->employee_num;

        $request->merge([
            'hourly_status_id' => $request->input('hourly_status_id') ?: null,
            'compensation_rate_id' => $request->input('compensation_rate_id') ?: null,
            'union_code' => $request->input('union_code') ?: null,
            'effdt_of_membership' => $request->input('effdt_of_membership') ?: null,
            'std_hrs_week' => $request->input('std_hrs_week') !== '' && $request->input('std_hrs_week') !== null
                ? $request->input('std_hrs_week')
                : null,
            'amount' => $request->input('amount') !== '' && $request->input('amount') !== null
                ? $request->input('amount')
                : null,
        ]);

        // If effseq is present, it's an update; otherwise, it's an add
        $isUpdate = $request->filled('effseq') && $request->input('effseq') !== '';
        $rules = [
            'facility_id' => 'required|integer',
            'position_id' => 'required|integer|exists:positions,id',
            'reports_to' => 'nullable|integer',
            'reg_temp' => 'required|in:r,t',
            'full_part_time' => 'required|in:ft,pt,pd',
            'hourly_status_id' => 'nullable|integer|exists:selectoptions,id',
            'std_hrs_week' => 'nullable|integer|min:0|max:168',
            'compensation_rate_id' => 'nullable|integer|exists:selectoptions,id',
            'amount' => 'nullable|numeric|min:0',
            'union_code' => 'nullable|string|max:50',
            'effdt_of_membership' => 'nullable|date',
            'effdt' => ['required', 'date'],
            'effseq' => 'nullable|integer',
        ];
        if (!$isUpdate) {
            // Only require effdt to be today or later when adding
            $rules['effdt'][] = 'after_or_equal:' . now()->toDateString();
        }
        $validated = $request->validate($rules);
        $position = \App\Models\Position::query()->find($validated['position_id']);
        if (!$position || !$position->department_id) {
            return redirect()->back()
                ->withErrors(['position_id' => 'The selected position does not have an assigned department.'])
                ->withInput();
        }

        $validated['dept_id'] = $position->department_id;
        $validated['reports_to'] = $position->reports_to_position_id ?: null;
        $validated['hourly_status_id'] = $validated['hourly_status_id'] ?? null;
        $validated['std_hrs_week'] = isset($validated['std_hrs_week']) && $validated['std_hrs_week'] !== ''
            ? (int) $validated['std_hrs_week']
            : null;
        $validated['compensation_rate_id'] = $validated['compensation_rate_id'] ?? null;
        $validated['amount'] = isset($validated['amount']) && $validated['amount'] !== ''
            ? round((float) $validated['amount'], 2)
            : null;

        $employeeModel->union_code = $validated['union_code'] ?? null;
        $employeeModel->effdt_of_membership = $validated['effdt_of_membership'] ?? null;
        $employeeModel->save();

        unset($validated['union_code'], $validated['effdt_of_membership']);

        $userId = Auth::id();
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;

        // Always update the latest assignment unless effdt is changed
        $latest = \App\Models\BPEmpJobData::where('employee_num', $employeeNum)
            ->orderByDesc('effdt')
            ->orderByDesc('effseq')
            ->first();

        $latestEffdt = $latest?->effdt?->format('Y-m-d');

        // If effdt is changed or no assignment exists, create new
        if (!$latest || $validated['effdt'] !== $latestEffdt) {
            $effseq = 0;
            if ($latest && $latestEffdt === $validated['effdt']) {
                $effseq = $latest->effseq + 1;
            }
            $validated['effseq'] = $effseq;
            $validated['employee_num'] = $employeeNum;
            \App\Models\BPEmpJobData::create($validated);
            $msg = 'Job data added successfully.';
        } else {
            // Update the latest job data record
            $latest->fill($validated);
            $latest->save();
            $msg = 'Job data updated successfully.';
        }

        return $this->redirectToEmployeeEdit($employee, 'job-data', ['success' => $msg]);
    }

    /**
     * Generate and email a portal registration code for an employee without a linked user.
     */
    public function generateRegistrationCode(Request $request, $employee)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employeeModel);

        $registrationCodeService = app(RegistrationCodeService::class);

        if (! $registrationCodeService->canGenerateCodes($request->user())) {
            abort(403, 'You are not authorized to generate registration codes.');
        }

        try {
            $registrationCode = $registrationCodeService->generateForEmployee(
                $employeeModel,
                $request->user()
            );
            Mail::to($registrationCode->email)->send(new EmployeeRegistrationInviteMail($registrationCode));
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            Log::error('Failed to generate employee registration code', [
                'employee_num' => $employeeModel->employee_num,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to generate the registration code. Please try again.');
        }

        return redirect()->back()->with(
            'success',
            'Registration code ' . $registrationCode->code . ' was emailed to ' . $registrationCode->email . '.'
        );
    }

    /**
     * Add or update tax data for an employee (tabbed form).
     */
    public function updateTaxData(Request $request, $employee)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $this->authorizeEmployeeFacilityAccess($request, $employeeModel);

        if (! $this->canEditCoreEmployeeTabs($request->user())) {
            return $this->redirectToEmployeeEdit($employeeModel->id, 'tax-data', [
                'error' => 'You have read-only access to Tax Data.',
            ]);
        }

        $employeeNum = $employeeModel->employee_num;

        $request->merge([
            'fed_tax_data' => $request->input('fed_tax_data') ?: null,
            'state_tax_data' => $request->input('state_tax_data') ?: null,
            'resident' => $request->input('resident') ?: null,
            'fed_withholding_allowance' => $request->input('fed_withholding_allowance') !== '' && $request->input('fed_withholding_allowance') !== null
                ? $request->input('fed_withholding_allowance') : null,
            'state_withholding_allowance1' => $request->input('state_withholding_allowance1') !== '' && $request->input('state_withholding_allowance1') !== null
                ? $request->input('state_withholding_allowance1') : null,
            'local_withholding_allowance' => $request->input('local_withholding_allowance') !== '' && $request->input('local_withholding_allowance') !== null
                ? $request->input('local_withholding_allowance') : null,
            'addl_withholding_percentage1' => $request->input('addl_withholding_percentage1') !== '' && $request->input('addl_withholding_percentage1') !== null
                ? $request->input('addl_withholding_percentage1') : null,
            'addl_withholding_amount1' => $request->input('addl_withholding_amount1') !== '' && $request->input('addl_withholding_amount1') !== null
                ? $request->input('addl_withholding_amount1') : null,
            'addl_withholding_percentage2' => $request->input('addl_withholding_percentage2') !== '' && $request->input('addl_withholding_percentage2') !== null
                ? $request->input('addl_withholding_percentage2') : null,
            'addl_withholding_amount2' => $request->input('addl_withholding_amount2') !== '' && $request->input('addl_withholding_amount2') !== null
                ? $request->input('addl_withholding_amount2') : null,
        ]);

        $isUpdate = $request->filled('effseq') && $request->input('effseq') !== '';
        $rules = [
            'effdt' => ['required', 'date'],
            'effseq' => 'nullable|integer',
            'fed_tax_data' => 'nullable|in:1,2',
            'fed_withholding_allowance' => 'nullable|numeric|min:0',
            'state_tax_data' => 'nullable|in:1,2',
            'state_withholding_allowance1' => 'nullable|numeric|min:0',
            'resident' => 'nullable|in:Y,N',
            'local_withholding_allowance' => 'nullable|numeric|min:0',
            'locality' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'addl_withholding_percentage1' => 'nullable|numeric|min:0|max:100',
            'addl_withholding_amount1' => 'nullable|numeric|min:0',
            'addl_withholding_percentage2' => 'nullable|numeric|min:0|max:100',
            'addl_withholding_amount2' => 'nullable|numeric|min:0',
            'resident_state' => 'nullable|string|size:2',
        ];
        if (!$isUpdate) {
            $rules['effdt'][] = 'after_or_equal:' . now()->toDateString();
        }

        $validated = $request->validate($rules);

        foreach ([
            'fed_withholding_allowance',
            'state_withholding_allowance1',
            'local_withholding_allowance',
            'addl_withholding_percentage1',
            'addl_withholding_amount1',
            'addl_withholding_percentage2',
            'addl_withholding_amount2',
        ] as $decimalField) {
            if (isset($validated[$decimalField]) && $validated[$decimalField] !== '') {
                $validated[$decimalField] = round((float) $validated[$decimalField], 2);
            } else {
                $validated[$decimalField] = null;
            }
        }

        $validated['resident_state'] = strtoupper($validated['resident_state'] ?? 'CA') ?: 'CA';
        $validated['locality'] = $validated['locality'] ?? null;
        $validated['county'] = $validated['county'] ?? null;

        if ($this->isEmployeeSelfServiceRequest($request, $employeeModel) && $isUpdate) {
            $latestTax = BPEmpTaxData::query()
                ->where('employee_num', $employeeNum)
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();

            $latestEffdt = $latestTax?->effdt?->format('Y-m-d') ?? ($latestTax->effdt ?? null);

            if (! $latestTax
                || (string) $latestEffdt !== (string) $validated['effdt']
                || (int) $latestTax->effseq !== (int) $validated['effseq']) {
                return $this->redirectToEmployeeEdit($employee, 'tax-data', [
                    'error' => 'Historical tax data records cannot be edited. Update your current tax data above, use Add New Tax Data if you have a new effective record, or contact your DSD, facility administrator, or RDHR.',
                ]);
            }
        }

        if ($isUpdate) {
            $tax = BPEmpTaxData::query()
                ->where('employee_num', $employeeNum)
                ->where('effdt', $validated['effdt'])
                ->where('effseq', $validated['effseq'])
                ->first();

            if ($tax) {
                $tax->fill($validated);
                $tax->save();
                $msg = 'Tax data updated successfully.';
            } else {
                $validated['employee_num'] = $employeeNum;
                $validated['effseq'] = (int) ($validated['effseq'] ?? 0);
                BPEmpTaxData::create($validated);
                $msg = 'Tax data added successfully.';
            }
        } else {
            $latest = BPEmpTaxData::query()
                ->where('employee_num', $employeeNum)
                ->orderByDesc('effdt')
                ->orderByDesc('effseq')
                ->first();

            $latestEffdt = $latest?->effdt?->format('Y-m-d');

            if (!$latest || $validated['effdt'] !== $latestEffdt) {
                $effseq = 0;
                if ($latest && $latestEffdt === $validated['effdt']) {
                    $effseq = $latest->effseq + 1;
                }
                $validated['effseq'] = $effseq;
                $validated['employee_num'] = $employeeNum;
                BPEmpTaxData::create($validated);
                $msg = 'Tax data added successfully.';
            } else {
                $latest->fill($validated);
                $latest->save();
                $msg = 'Tax data updated successfully.';
            }
        }

        return $this->redirectToEmployeeEdit($employee, 'tax-data', ['success' => $msg]);
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
            $checklistItem = null;
            if ($request->filled('checklist_item_id')) {
                $checklistItem = \App\Models\ChecklistItem::find($request->input('checklist_item_id'));
            }
            if (!$checklistItem && $request->filled('doc_name')) {
                $checklistItem = \App\Models\ChecklistItem::where('name', $request->input('doc_name'))->first();
            }

            $validated = $request->validate([
                'checklist_item_id' => 'nullable|integer|exists:checklist_items,id',
                'doc_name' => 'required|string|max:255',
                'doc_type_id' => 'required|integer',
                'on_file' => 'required|boolean',
                'verified_dt' => 'nullable|date',
                'exp_dt' => [
                    $checklistItem && $checklistItem->isExpiring ? 'required' : 'nullable',
                    'date',
                ],
                'comments' => 'nullable|string|max:1000',
                'exp_dt_not_required' => 'nullable|boolean',
            ]);

            $userId = Auth::id();
            $employeeModel = \App\Models\BPEmployee::where('employee_num', $employee)->firstOrFail();

            if ($response = \App\Support\PreventsSelfAssessment::jsonDenyIfSelf(Auth::user(), $employeeModel)) {
                return $response;
            }

            $checklist = \App\Models\BPEmpChecklist::firstOrNew(['employee_num' => $employeeModel->employee_num]);
            $items = $checklist->items ?? [];
            $docName = $validated['doc_name'];
            $checklistKey = !empty($validated['checklist_item_id']) ? 'item_' . $validated['checklist_item_id'] : $docName;
            $items[$checklistKey] = [
                'checklist_item_id' => $validated['checklist_item_id'] ?? null,
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
            //     'employee_num' => $employee,
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
            $itemWithName = $items[$checklistKey];
            $itemWithName['verified_by_name'] = $verifiedByName;
            return response()->json([
                'success' => true,
                'message' => 'Checklist verification saved.',
                'data' => [
                    'checklist_key' => $checklistKey,
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
                'checklist_item_id' => 'nullable|integer|exists:checklist_items,id',
                'doc_name' => 'required|string|max:255',
            ]);
            $docName = $validated['doc_name'];
            $checklistKey = !empty($validated['checklist_item_id']) ? 'item_' . $validated['checklist_item_id'] : $docName;

            if ($response = \App\Support\PreventsSelfAssessment::jsonDenyIfSelf(Auth::user(), (string) $employee)) {
                return $response;
            }

            $checklist = \App\Models\BPEmpChecklist::where('employee_num', $employee)->first();
            $itemData = null;
            if ($checklist && is_array($checklist->items) && array_key_exists($checklistKey, $checklist->items)) {
                $items = $checklist->items;
                unset($items[$checklistKey]);
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
    public function saveAreasDevelopment(Request $request, $employee_num)
    {
        $action = $this->resolvePerformanceWorkflowAction($request);
        $employeeActions = ['acknowledge', 'send_back'];
        $isEmployeeAction = in_array($action, $employeeActions, true);

        $rules = [
            'employee_name' => 'required|string|max:255',
            'employee_acknowledge_dt' => 'nullable|date',
            'overall_rating' => 'nullable|string|in:Exceeds Expectations,Meets Expectations,Below Expectations,Excellent,Satisfactory,Unsatisfactory,Not Rated',
            'overall_unsatisfactory_reason' => 'nullable|string',
        ];

        if (! $isEmployeeAction) {
            $rules['supervisor_name'] = 'required|string|max:255';
            $rules['review_dt'] = ($action === 'submit' ? 'required' : 'nullable').'|date';
        }

        if ($action === 'submit') {
            $rules['areas_for_development'] = 'required|string|min:2';
        }

        $validated = $request->validate($rules);

        if (PartFPerformanceScoring::isBelowExpectationsRating($validated['overall_rating'] ?? null)
            && blank($validated['overall_unsatisfactory_reason'] ?? null)
            && in_array($action, ['save', 'submit'], true)) {
            return back()
                ->withErrors(['overall_unsatisfactory_reason' => 'Explain why the overall performance rating is below expectations.'])
                ->withInput();
        }

        $assessmentPeriodId = $request->input('assessment_period_id');
        if (! $assessmentPeriodId) {
            return back()->with('error', 'Assessment period is required.');
        }

        $employee = $this->employeeFromRouteKey($employee_num);
        $firstPerformanceDueDate = EmployeeAssessmentPeriodCalculator::firstAssessmentDueDate($employee);
        if ($firstPerformanceDueDate && ! EmployeeAssessmentPeriodCalculator::isAssessmentDue($employee)) {
            return back()->with(
                'error',
                'Performance appraisal is not due until '.$firstPerformanceDueDate->format('F j, Y').'. Competency evaluation may be completed during the first year.'
            );
        }
        $isSelfAssessment = \App\Support\PreventsSelfAssessment::isSelfAssessment($request->user(), $employee);

        $assessment = EmployeePerformanceAssessment::firstOrCreate(
            [
                'employee_num' => $employee->employee_num,
                'assessment_period_id' => $assessmentPeriodId,
            ],
            [
                'items' => [],
                'assessed_by' => auth()->id(),
                'status' => AssessmentWorkflowStatus::DRAFT,
            ]
        );

        $currentStatus = $assessment->workflowStatus();

        if ($isSelfAssessment) {
            if ($action === 'send_back') {
                if (! AssessmentWorkflowStatus::employeeCanSendBack($currentStatus)) {
                    return back()->with('error', 'This assessment cannot be sent back to the reviewer at its current status.');
                }

                $assessment->status = AssessmentWorkflowStatus::DRAFT;
                $assessment->syncFinalizedFromStatus();
                $assessment->save();

                $emailSent = app(\App\Services\AssessmentConfirmationNotificationService::class)
                    ->notifyPerformanceAssessmentReturnedToReviewer($assessment, $employee);

                $message = $emailSent
                    ? 'Assessment sent back to the reviewer for updates. The reviewer has been notified by email and will see a task on their dashboard.'
                    : 'Assessment sent back to the reviewer for updates. No reviewer email is on file, so no notification was sent.';

                return back()->with('success', $message);
            }

            if ($action !== 'acknowledge') {
                return back()->with('error', 'Only employee acknowledgement actions are available on your own record.');
            }

            if (! AssessmentWorkflowStatus::employeeCanConfirm($currentStatus)) {
                return back()->with('error', 'This performance assessment is not waiting for employee confirmation.');
            }

            $request->validate([
                'employee_signature_data' => 'nullable|string',
                'employee_signature_upload' => 'nullable|image|max:4096',
            ]);

            if (! $request->filled('employee_signature_data') && ! $request->hasFile('employee_signature_upload')) {
                return back()
                    ->withErrors(['employee_signature' => 'Draw or upload your signature before saving acknowledgement.'])
                    ->withInput();
            }

            $confirmationService = app(\App\Services\PerformanceAssessmentConfirmationService::class);

            $empCommentsDocType = \App\Models\DocType::where('name', 'Employee Comments')->first();
            if ($empCommentsDocType) {
                \App\Models\EmployeePerformanceSectionComment::syncForSection(
                    $employee->employee_num,
                    (int) $assessmentPeriodId,
                    (int) $empCommentsDocType->id,
                    $request->input('employee_comments'),
                );
            }

            try {
                $assessment->employee_signature_path = $confirmationService->storeEmployeeSignature(
                    $assessment,
                    $request->input('employee_signature_data'),
                    $request->file('employee_signature_upload'),
                );
            } catch (\InvalidArgumentException $exception) {
                return back()
                    ->withErrors(['employee_signature' => $exception->getMessage()])
                    ->withInput();
            }

            $assessment->acknowledge_dt = filled($validated['employee_acknowledge_dt'] ?? null)
                ? $validated['employee_acknowledge_dt']
                : now()->toDateString();
            $assessment->status = AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL;
            $assessment->syncFinalizedFromStatus();
            $assessment->save();

            $assessment->refresh();
            $confirmationService->storeEmployeeConfirmationSnapshot($assessment);
            $assessment->save();

            $notificationService = app(\App\Services\AssessmentConfirmationNotificationService::class);
            $emailSent = $notificationService->notifyPerformanceAssessmentReadyForReviewerApproval($assessment, $employee);

            $message = $emailSent
                ? 'Employee acknowledgement and signature saved. The assessment is now waiting for reviewer approval. The reviewer has been notified by email and will see a task on their dashboard.'
                : 'Employee acknowledgement and signature saved. The assessment is now waiting for reviewer approval.';

            return back()->with('success', $message);
        }

        if (in_array($action, ['submit', 'save', 'approve'], true)) {
            \App\Support\AssessmentEvaluatorAuthorization::assertCanEvaluateReviewer($request->user(), $employee);
        }

        if (! $isSelfAssessment && in_array($action, ['submit', 'save'], true) && $currentStatus === AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION) {
            return back()->with('error', 'This assessment has already been submitted for employee confirmation. It can be resubmitted only after the employee sends it back for corrections.');
        }

        if ($action === 'submit' && ! in_array($currentStatus, [AssessmentWorkflowStatus::DRAFT, AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL], true)) {
            return back()->with('error', 'Only in-progress assessments or post-employee-confirmation revisions can be submitted for employee confirmation.');
        }

        if ($action === 'reopen') {
            \App\Support\AssessmentEvaluatorAuthorization::assertCanEvaluateReviewer($request->user(), $employee);

            if (! AssessmentWorkflowStatus::reviewerCanReopen($currentStatus)) {
                return back()->with('error', 'Only completed assessments can be reopened for editing.');
            }

            $assessment->status = AssessmentWorkflowStatus::DRAFT;
            $assessment->syncFinalizedFromStatus();
            $assessment->save();

            return back()->with('success', 'Assessment reopened for editing.');
        }

        if (AssessmentWorkflowStatus::isLocked($currentStatus) && ! in_array($action, ['reopen'], true)) {
            return back()->with('error', 'This performance assessment is completed for the selected period. Reopen it to make changes.');
        }

        if ($action === 'approve') {
            if (! AssessmentWorkflowStatus::reviewerCanApprove($currentStatus)) {
                return back()->with('error', 'This assessment is not waiting for reviewer approval.');
            }

            $confirmationService = app(\App\Services\PerformanceAssessmentConfirmationService::class);
            if ($confirmationService->hasChangedSinceEmployeeConfirmation($assessment)) {
                return back()->with('error', 'This assessment was changed after the employee confirmed it. Save your changes to send it back to the employee for confirmation before approving.');
            }

            if (blank($assessment->employee_signature_path)) {
                return back()->with('error', 'Employee signature is required before this assessment can be approved.');
            }

            $assessment->status = AssessmentWorkflowStatus::COMPLETED;
            $assessment->syncFinalizedFromStatus();
            $assessment->save();

            app(\App\Http\Controllers\EmployeePerformanceAssessmentController::class)
                ->persistPerformanceAssessmentPdf($assessment->fresh());

            return back()->with('success', 'Performance assessment approved and marked as completed. The final PDF now includes the employee signature and comments.');
        }

        $areasDevDocType = \App\Models\DocType::where('name', 'Areas Requiring Further Development')->first();
        if ($areasDevDocType) {
            \App\Models\EmployeePerformanceSectionComment::syncForSection(
                $employee->employee_num,
                (int) $assessmentPeriodId,
                (int) $areasDevDocType->id,
                $request->input('areas_for_development'),
            );
        }

        $devPlansDocType = \App\Models\DocType::where('name', 'Development Plans')->first();
        if ($devPlansDocType) {
            \App\Models\EmployeePerformanceSectionComment::syncForSection(
                $employee->employee_num,
                (int) $assessmentPeriodId,
                (int) $devPlansDocType->id,
                $request->input('development_plans'),
            );
        }

        $empCommentsDocType = \App\Models\DocType::where('name', 'Employee Comments')->first();
        if ($empCommentsDocType) {
            \App\Models\EmployeePerformanceSectionComment::syncForSection(
                $employee->employee_num,
                (int) $assessmentPeriodId,
                (int) $empCommentsDocType->id,
                $request->input('employee_comments'),
            );
        }

        if ($action === 'submit') {
            $positionTitle = $employee->loadMissing('currentAssignment.position')->currentAssignment?->position?->title;
            $scorableItemIds = PartFPerformanceScoring::scorableItemIds(null, $positionTitle);

            if ($scorableItemIds === []) {
                return back()
                    ->with('error', 'No performance appraisal items are configured for this employee\'s position.')
                    ->withInput();
            }

            $missingItemIds = PartFPerformanceScoring::missingScorableItemIds(
                (string) $employee->employee_num,
                (int) $assessmentPeriodId,
                $scorableItemIds
            );

            if ($missingItemIds !== []) {
                return back()
                    ->with('error', 'All performance appraisal items must be rated before submitting. '.count($missingItemIds).' item(s) still need a rating.')
                    ->withInput();
            }
        }

        if (! $isSelfAssessment) {
            if ($request->filled('review_dt')) {
                $assessment->review_dt = $request->input('review_dt');
            } elseif ($action === 'submit' && array_key_exists('review_dt', $validated)) {
                $assessment->review_dt = $validated['review_dt'];
            }
        }

        $this->syncPerformanceAssessmentSummary($assessment);

        if (! $isSelfAssessment) {
            $reviewerName = trim((string) ($validated['supervisor_name'] ?? ''));
            if ($reviewerName !== '') {
                $assessment->reviewer_name = $reviewerName;
            } elseif (auth()->check()) {
                $assessment->reviewer_name = auth()->user()->name;
            }

            $assessment->assessed_by = auth()->id();

            $reviewerEmployee = auth()->user()?->resolvedBpEmployee(['currentAssignment.position']);
            $reviewerTitle = trim((string) (
                $reviewerEmployee?->currentAssignment?->position?->title
                ?? $reviewerEmployee?->position
                ?? ''
            ));
            if ($reviewerTitle !== '') {
                $assessment->reviewer_title = $reviewerTitle;
            }
        }

        if (filled($validated['overall_rating'] ?? null)) {
            $assessment->overall_rating = $validated['overall_rating'];
        }

        $assessment->comments = PartFPerformanceScoring::isBelowExpectationsRating($validated['overall_rating'] ?? null)
            ? ($validated['overall_unsatisfactory_reason'] ?? null)
            : null;

        $wasPerformanceResubmit = false;

        if ($action === 'submit') {
            $performanceConfirmationService = app(\App\Services\PerformanceAssessmentConfirmationService::class);
            $wasPerformanceResubmit = $performanceConfirmationService->prepareForEmployeeConfirmation($assessment);
        } elseif ($assessment->workflowStatus() === AssessmentWorkflowStatus::DRAFT) {
            $assessment->status = AssessmentWorkflowStatus::DRAFT;
        }

        $assessment->syncFinalizedFromStatus();
        $assessment->save();

        if ($action === 'save' && $currentStatus === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL) {
            $confirmationService = app(\App\Services\PerformanceAssessmentConfirmationService::class);
            $assessment->refresh();

            if ($confirmationService->hasChangedSinceEmployeeConfirmation($assessment)) {
                $confirmationService->resetForEmployeeReconfirmation($assessment);
                $assessment->save();

                $emailSent = app(\App\Services\AssessmentConfirmationNotificationService::class)
                    ->notifyPerformanceAssessmentResubmittedToEmployee($assessment, $employee);

                $message = $emailSent
                    ? 'Changes were saved and the assessment was sent back to the employee for confirmation. The employee has been notified by email and will see a task at the top of their dashboard.'
                    : 'Changes were saved and the assessment was sent back to the employee for confirmation. No employee email is on file, so no notification was sent.';

                return back()->with('success', $message);
            }

            return back()->with('success', 'Performance assessment changes saved successfully.');
        }

        if ($action === 'submit') {
            $notificationService = app(\App\Services\AssessmentConfirmationNotificationService::class);
            $emailSent = $wasPerformanceResubmit
                ? $notificationService->notifyPerformanceAssessmentResubmittedToEmployee($assessment, $employee)
                : $notificationService->notifyPerformanceAssessmentSubmitted($assessment, $employee);

            $message = $emailSent
                ? ($wasPerformanceResubmit
                    ? 'Employee assessment sent back to the employee for confirmation. The employee has been notified by email and will see a task at the top of their dashboard.'
                    : 'Employee assessment submitted successfully. The employee has been notified by email and will see a task on their dashboard.')
                : 'Employee assessment submitted successfully. No employee email is on file, so no notification was sent.';

            return back()->with('success', $message);
        }

        return back()->with('success', 'Performance assessment draft saved successfully.');
    }

    protected function resolvePerformanceWorkflowAction(Request $request): string
    {
        $action = trim((string) $request->input('workflow_action', ''));

        if ($action === '') {
            $action = trim((string) $request->input('action', 'save'));
        }

        return $action !== '' ? $action : 'save';
    }

    protected function formatAssessmentDateForInput(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        try {
            return \Illuminate\Support\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    protected function resolvePerformanceReviewerDisplayName(
        BPEmployee $employee,
        ?EmployeePerformanceAssessment $assessment,
        $users,
        string $resolvedReviewerName = ''
    ): string {
        $storedName = trim((string) ($assessment?->reviewer_name ?? ''));
        if ($storedName !== '') {
            return $storedName;
        }

        $resolvedReviewerName = trim($resolvedReviewerName);
        if ($resolvedReviewerName !== '') {
            return $resolvedReviewerName;
        }

        if ($assessment?->assessed_by) {
            $assessedByName = trim((string) (optional($users->firstWhere('id', $assessment->assessed_by))->name ?? ''));
            if ($assessedByName !== '') {
                return $assessedByName;
            }
        }

        $user = auth()->user();
        if ($user && ! \App\Support\PreventsSelfAssessment::isSelfAssessment($user, $employee)) {
            return trim((string) $user->name);
        }

        return '';
    }

    /**
     * Handle competency assessment workflow actions (acknowledge, approve, send back, reopen).
     */
    public function saveCompetencyWorkflow(Request $request, $employee_num)
    {
        $action = trim((string) $request->input('action', ''));
        $assessmentPeriodId = $request->input('assessment_period_id');
        $sectionLabel = trim((string) $request->input('section_label', ''));

        if (! $assessmentPeriodId) {
            return back()->with('error', 'Assessment period is required.');
        }

        if ($sectionLabel === '' && in_array($action, ['submit', 'acknowledge', 'send_back', 'approve'], true)) {
            return back()->with('error', 'Competency section is required for this action.');
        }

        $employee = $this->employeeFromRouteKey($employee_num);
        $isSelfAssessment = \App\Support\PreventsSelfAssessment::isSelfAssessment($request->user(), $employee);
        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);
        $confirmationService = app(\App\Services\CompetencyAssessmentConfirmationService::class);
        $notificationService = app(\App\Services\AssessmentConfirmationNotificationService::class);

        $assessment = \App\Models\EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employee->employee_num)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->first();

        if ($action === 'submit') {
            \App\Support\AssessmentEvaluatorAuthorization::assertCanEvaluateReviewer($request->user(), $employee);

            $assessment = \App\Models\EmployeeCompetencyAssessment::query()->firstOrCreate(
                [
                    'employee_num' => $employee->employee_num,
                    'assessment_period_id' => $assessmentPeriodId,
                ],
                [
                    'status' => AssessmentWorkflowStatus::DRAFT,
                    'submitted_by' => auth()->id(),
                ]
            );

            if (! $sectionWorkflow->sectionIsSubmitted($assessment, $sectionLabel)) {
                return back()->with('error', 'Submit the competency section ratings before sending it to the employee.');
            }

            if (
                $sectionWorkflow->sectionWasReturnedToReviewer($assessment, $sectionLabel)
                && ! $sectionWorkflow->reviewerCanResubmitReturnedSection($assessment, $sectionLabel)
            ) {
                return back()->with('error', 'Save your changes with Update before resubmitting this section to the employee.');
            }

            $wasResubmit = $sectionWorkflow->submitSectionForEmployeeConfirmation(
                $assessment,
                $sectionLabel,
                auth()->id(),
            );

            $reviewerEmployee = auth()->user()?->resolvedBpEmployee(['currentAssignment.position']);
            $reviewerTitle = trim((string) (
                $reviewerEmployee?->currentAssignment?->position?->title
                ?? $reviewerEmployee?->position
                ?? ''
            ));
            if ($reviewerTitle !== '') {
                $assessment->reviewer_title = $reviewerTitle;
            }
            if (auth()->check()) {
                $assessment->reviewer_name = trim((string) ($assessment->reviewer_name ?? '')) !== ''
                    ? $assessment->reviewer_name
                    : auth()->user()->name;
            }
            $assessment->save();

            $this->regenerateCompetencySectionPdf($assessment, $sectionLabel);

            $emailSent = $wasResubmit
                ? $notificationService->notifyCompetencySectionResubmittedToEmployee($assessment->fresh(), $employee, $sectionLabel)
                : $notificationService->notifyCompetencySectionSubmittedToEmployee($assessment->fresh(), $employee, $sectionLabel);

            $message = $emailSent
                ? ($wasResubmit
                    ? "{$sectionLabel} sent back to the employee for confirmation. The employee has been notified by email and will see a task at the top of their dashboard."
                    : "{$sectionLabel} submitted for employee confirmation. The employee has been notified by email and will see a task on their dashboard.")
                : "{$sectionLabel} submitted for employee confirmation. No employee email is on file, so no notification was sent.";

            return back()->with('success', $message);
        }

        if (! $assessment) {
            return back()->with('error', 'No competency assessment found for the selected period.');
        }

        $sectionWorkflow->syncSubmittedSectionsWithoutWorkflow($assessment);
        $assessment->refresh();

        $currentSectionStatus = $sectionWorkflow->sectionStatus($assessment, $sectionLabel);

        if ($isSelfAssessment) {
            if ($action === 'send_back') {
                if (! AssessmentWorkflowStatus::employeeCanSendBack($currentSectionStatus)) {
                    return back()->with('error', 'This competency section cannot be sent back to the reviewer at its current status.');
                }

                $sectionWorkflow->employeeSendBackSection(
                    $assessment,
                    $sectionLabel,
                    $request->input('employee_comments'),
                );

                $emailSent = $notificationService->notifyCompetencySectionReturnedToReviewer(
                    $assessment->fresh(),
                    $employee,
                    $sectionLabel,
                );

                $message = $emailSent
                    ? "{$sectionLabel} sent back to the reviewer for updates. The reviewer has been notified by email and will see a task on their dashboard."
                    : "{$sectionLabel} sent back to the reviewer for updates. No reviewer email is on file, so no notification was sent.";

                return back()->with('success', $message);
            }

            if ($action !== 'acknowledge') {
                return back()->with('error', 'Only employee acknowledgement actions are available on your own record.');
            }

            if (! AssessmentWorkflowStatus::employeeCanConfirm($currentSectionStatus)) {
                return back()->with('error', 'This competency section is not waiting for employee confirmation.');
            }

            $request->validate([
                'employee_signature_data' => 'nullable|string',
                'employee_signature_upload' => 'nullable|image|max:4096',
            ]);

            if (! $request->filled('employee_signature_data') && ! $request->hasFile('employee_signature_upload')) {
                return back()
                    ->withErrors(['employee_signature' => 'Draw or upload your signature before saving acknowledgement.'])
                    ->withInput();
            }

            $existingPath = $sectionWorkflow->sectionEmployeeSignaturePath($assessment, $sectionLabel);

            try {
                $signaturePath = $confirmationService->storeSectionEmployeeSignature(
                    $assessment,
                    $sectionLabel,
                    $request->input('employee_signature_data'),
                    $request->file('employee_signature_upload'),
                    $existingPath,
                );
            } catch (\InvalidArgumentException $exception) {
                return back()
                    ->withErrors(['employee_signature' => $exception->getMessage()])
                    ->withInput();
            }

            $sectionWorkflow->employeeAcknowledgeSection(
                $assessment,
                $sectionLabel,
                $signaturePath,
                $request->input('employee_comments'),
            );

            $employeeRecord = $employee->loadMissing('currentAssignment.position');
            $assessment->employee_name = $employeeRecord->formattedFullName();
            $assessment->employee_title = trim((string) (
                $employeeRecord->currentAssignment?->position?->title
                ?? $employeeRecord->position
                ?? ''
            ));
            $assessment->save();

            $this->regenerateCompetencySectionPdf($assessment->fresh(), $sectionLabel);

            $emailSent = $notificationService->notifyCompetencySectionReadyForReviewerApproval(
                $assessment->fresh(),
                $employee,
                $sectionLabel,
            );

            $message = $emailSent
                ? "Employee acknowledgement and signature saved for {$sectionLabel}. The reviewer has been notified by email and will see a task on their dashboard."
                : "Employee acknowledgement and signature saved for {$sectionLabel}. The section is now waiting for reviewer approval.";

            return back()->with('success', $message);
        }

        if ($action === 'reopen') {
            \App\Support\AssessmentEvaluatorAuthorization::assertCanEvaluateReviewer($request->user(), $employee);

            $currentStatus = $assessment->workflowStatus();

            if (! AssessmentWorkflowStatus::reviewerCanReopen($currentStatus)) {
                return back()->with('error', 'Only completed competency assessments can be reopened for editing.');
            }

            if (filled($assessment->reviewer_signature_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($assessment->reviewer_signature_path);
            }

            app(\App\Services\CompetencyAssessmentPdfStorage::class)->deleteAllPdfs($assessment);

            $assessment->status = AssessmentWorkflowStatus::DRAFT;
            $assessment->completed_at = null;
            $assessment->reviewer_signed_at = null;
            $assessment->reviewer_signature_path = null;
            $assessment->review_date = null;
            $assessment->pdf_path = null;
            $assessment->pdf_generated_at = null;
            $assessment->save();

            return back()->with('success', 'Competency assessment reopened for editing.');
        }

        if ($action === 'approve') {
            \App\Support\AssessmentEvaluatorAuthorization::assertCanEvaluateReviewer($request->user(), $employee);

            if (! AssessmentWorkflowStatus::reviewerCanApprove($currentSectionStatus)) {
                return back()->with('error', 'This competency section is not waiting for reviewer approval.');
            }

            if ($sectionWorkflow->sectionHasChangedSinceEmployeeConfirmation($assessment, $sectionLabel)) {
                return back()->with('error', 'This competency section was changed after the employee confirmed it. Resubmit it to the employee for confirmation before approving.');
            }

            if (blank($sectionWorkflow->sectionEmployeeSignaturePath($assessment, $sectionLabel))) {
                return back()->with('error', 'Employee signature is required before this competency section can be approved.');
            }

            $request->validate([
                'reviewer_signature_data' => 'nullable|string',
                'reviewer_signature_upload' => 'nullable|image|max:4096',
            ]);

            if (! $request->filled('reviewer_signature_data') && ! $request->hasFile('reviewer_signature_upload')) {
                return back()
                    ->withErrors(['reviewer_signature' => 'Draw or upload your signature before approving this section.'])
                    ->withInput();
            }

            $existingPath = $sectionWorkflow->sectionReviewerSignaturePath($assessment, $sectionLabel);

            try {
                $signaturePath = $confirmationService->storeSectionReviewerSignature(
                    $assessment,
                    $sectionLabel,
                    $request->input('reviewer_signature_data'),
                    $request->file('reviewer_signature_upload'),
                    $existingPath,
                );
            } catch (\InvalidArgumentException $exception) {
                return back()
                    ->withErrors(['reviewer_signature' => $exception->getMessage()])
                    ->withInput();
            }

            $sectionWorkflow->reviewerApproveSection($assessment, $sectionLabel, $signaturePath);

            $reviewerEmployee = auth()->user()?->resolvedBpEmployee(['currentAssignment.position']);
            $reviewerTitle = trim((string) (
                $reviewerEmployee?->currentAssignment?->position?->title
                ?? $reviewerEmployee?->position
                ?? ''
            ));
            if ($reviewerTitle !== '') {
                $assessment->reviewer_title = $reviewerTitle;
            }
            if (auth()->check()) {
                $assessment->reviewer_name = trim((string) auth()->user()->name);
            }
            $assessment->save();

            $this->regenerateCompetencySectionPdf($assessment->fresh(), $sectionLabel);

            return back()->with('success', "{$sectionLabel} signed, approved, and marked as completed.");
        }

        return back()->with('error', 'Unsupported competency workflow action.');
    }

    protected function regenerateCompetencySectionPdf(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): void {
        try {
            app(\App\Http\Controllers\EmployeePerformanceAssessmentController::class)
                ->persistCompetencySectionPdf($assessment->fresh(), $sectionLabel);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $employee = new \App\Models\BPEmployee();
        $departments = \App\Models\Department::all();
        $positions = \App\Models\Position::all();
        $facilities = \App\Models\Facility::all();
        $checklistItems = \App\Models\ChecklistItem::all();
        $employeeCompetencyItems = collect();
        $employeeTrainingItems = collect();
        $employeeTrainingHireCompletions = collect();
        $employeeTrainingPeriodCompletions = collect();
        $employeeTrainingLatestCompletedAt = collect();
        $empChecklists = collect();
        $users = \App\Models\User::all();
        $states = \App\Models\State::orderBy('name')->get();
        $assessmentPeriods = \App\Models\EmployeeAssessmentPeriod::orderBy('date_from', 'desc')->get();
        $selectedAssessmentPeriodId = null;
        $empPerformanceChecklist = [];
        $empCompetencyAssessments = [];
        $competencyAssessmentHistory = [];
        $assessmentItemStates = [];
        $assessmentItemHistories = [];
        $reviewDate = '';
        $reviewerName = '';
        $reviewType = '';
        $reviewDt = '';
        $sectionComments = [];
        $supervisorName = '';
        $areasForDevelopment = '';
        $developmentPlans = '';
        $employeeComments = '';
        $isAddMode = true;
        $maritalOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Marital Status')->value('id'))
            ->orderBy('sort_order')->get();
        $ethnicOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Ethnic Group')->value('id'))
            ->orderBy('sort_order')->get();
        $militaryOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Military Status')->value('id'))
            ->orderBy('sort_order')->get();
        $citizenOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Citizenship Status')->value('id'))
            ->orderBy('sort_order')->get();
        $actionOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Action')->value('id'))
            ->orderBy('sort_order')->get();
        $unionCodeOptions = \App\Models\BPBargainingUnit::query()
            ->whereNotNull('union_code')
            ->orderBy('union_code')
            ->pluck('union_code')
            ->unique()
            ->values();

        $uploadTypes = \App\Models\UploadType::query()->orderedForDisplay()->get();
        $requiredDocumentChecklist = [
            'position_id' => null,
            'position_title' => null,
            'department_id' => null,
            'items' => collect(),
            'summary' => [
                'total' => 0,
                'complete' => 0,
                'expired' => 0,
                'missing' => 0,
            ],
        ];
        return view('admin.facilities.employee.edit_employee', compact(
            'employee',
            'departments',
            'positions',
            'facilities',
            'checklistItems',
            'employeeCompetencyItems',
            'employeeTrainingItems',
            'employeeTrainingHireCompletions',
            'employeeTrainingPeriodCompletions',
            'employeeTrainingLatestCompletedAt',
            'empChecklists',
            'users',
            'empPerformanceChecklist',
            'empCompetencyAssessments',
            'competencyAssessmentHistory',
            'assessmentItemStates',
            'assessmentItemHistories',
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
            'reviewDt',
            'states',
            'isAddMode',
            'maritalOptions',
            'ethnicOptions',
            'militaryOptions',
            'citizenOptions',
            'actionOptions',
            'unionCodeOptions',
            'uploadTypes',
            'requiredDocumentChecklist'
        ));
    }

        /**
     * Store a newly created employee and user (with email) in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|string|max:255',
            'employee_num' => 'nullable|string|max:255',
            'ssn' => 'nullable|string|max:255',
            'original_hire_dt' => 'nullable|date',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'badge_num' => 'nullable|string|max:50',
            'badge_eff_dt' => 'nullable|date',
            'union_code' => 'nullable|string|max:50',
            'effdt_of_membership' => 'nullable|date',
            'action_id' => 'nullable|integer|exists:selectoptions,id',
            'marital_status_id' => 'nullable|integer|exists:selectoptions,id',
            'ethnic_group_id' => 'nullable|integer|exists:selectoptions,id',
            'military_status_id' => 'nullable|integer|exists:selectoptions,id',
            'citizenship_status_id' => 'nullable|integer|exists:selectoptions,id',
            'gender' => 'required|in:M,F,O,N',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email'),
            ],
        ]);

        DB::beginTransaction();
        try {
            $user = null;
            if (!empty($validated['email'])) {
                // Create a new user if email is provided
                $user = new \App\Models\User();
                $user->name = $validated['first_name'] . ' ' . $validated['last_name'];
                $user->email = $validated['email'];
                $user->password = bcrypt(str_random(12)); // Set a random password, force reset later
                $user->save();
            }

            $employee = new \App\Models\BPEmployee();
            $employee->user_id = $user ? $user->id : null;
            $employee->employee_num = $validated['employee_num'] ?? null;
            $employee->ssn = $validated['ssn'] ?? null;
            $employee->original_hire_dt = $validated['original_hire_dt'] ?? null;
            $employee->first_name = $validated['first_name'];
            $employee->middle_name = $validated['middle_name'] ?? null;
            $employee->last_name = $validated['last_name'];
            $employee->dob = isset($validated['dob']) && $validated['dob'] ? date('Y-m-d', strtotime($validated['dob'])) : null;
            $employee->badge_num = $validated['badge_num'] ?? null;
            $employee->badge_eff_dt = $validated['badge_eff_dt'] ?? null;
            $employee->union_code = $validated['union_code'] ?? null;
            $employee->effdt_of_membership = $validated['effdt_of_membership'] ?? null;
            $employee->action_id = $validated['action_id'] ?? null;
            $employee->gender = $validated['gender'];
            $employee->marital_status_id = $validated['marital_status_id'] ?? null;
            $employee->ethnic_group_id = $validated['ethnic_group_id'] ?? null;
            $employee->military_status_id = $validated['military_status_id'] ?? null;
            $employee->citizenship_status_id = $validated['citizenship_status_id'] ?? null;
            $employee->save();

            DB::commit();
            return redirect()->route('admin.employees.edit', $employee->id)
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    private function syncPerformanceAssessmentSummary(EmployeePerformanceAssessment $assessment): void
    {
        $ratings = [];
        foreach ($assessment->itemsArray() as $itemKey => $itemData) {
            if (! preg_match('/^F_(\d+)$/', (string) $itemKey, $matches)) {
                continue;
            }

            $rating = EmployeePerformanceAssessment::itemRating($itemData);
            if ($rating !== null) {
                $ratings[(int) $matches[1]] = $rating;
            }
        }

        $employeeRecord = BPEmployee::query()
            ->with('currentAssignment.position')
            ->where('employee_num', $assessment->employee_num)
            ->first();
        $positionId = $employeeRecord?->currentAssignment?->position_id;
        $positionTitle = $employeeRecord?->currentAssignment?->position?->title;

        $summary = PartFPerformanceScoring::summarize(
            $ratings,
            PartFPerformanceScoring::scorableItemIds(
                $positionId ? (int) $positionId : null,
                $positionTitle
            )
        );
        $assessment->total_score = $summary['total_score'];
        $assessment->average_score = $summary['average_score'];
        $assessment->overall_rating = $summary['overall_rating'];
    }

    /**
     * Resolve the active assessment period from the request.
     * Defaults to none so Part F/G start on "Select/Create Assessment Period".
     */
    private function resolveSelectedAssessmentPeriodId($assessmentPeriods): ?int
    {
        if (request()->has('assessment_period_id')) {
            return filled(request('assessment_period_id'))
                ? (int) request('assessment_period_id')
                : null;
        }

        return null;
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Request $request, $employee_num) {
        // Always define draft responses and raw draft row for Part G
        $draftResponses = [];
        $rawDraftRow = null;
        $employee = $this->employeeFromRouteKey($employee_num, [
            'phones',
            'addresses',
            'user',
            'taxData',
            'assignments.hourlyStatus',
            'assignments.compensationRate',
            'assignments.facility',
            'assignments.department',
            'assignments.position',
            'currentAssignment',
            'currentAssignment.facility',
            'currentAssignment.position',
            'currentAssignment.position.reportsToPosition',
            'currentAssignment.hourlyStatus',
            'currentAssignment.compensationRate',
        ]);
        $this->authorizeEmployeeRecordAccess($request, $employee);
        $employeesListFacilityId = $this->resolveFacilityFilterId($request);
        $employeesListFacility = $employeesListFacilityId
            ? Facility::find($employeesListFacilityId)
            : $employee->currentAssignment?->facility;

        $departments = \App\Models\Department::all();
        $positions = \App\Models\Position::all();
        $facilities = \App\Models\Facility::all();
        $positionIdForChecklist = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;
        $checklistItems = \App\Models\ChecklistItem::applicableToPosition($positionIdForChecklist)->get();
        $employeeCompetencyItems = \App\Models\EmployeeCompetencyItem::query()
            ->applicableToPosition($positionIdForChecklist)
            ->orderBy('order')
            ->get();
        $empChecklists = \App\Models\BPEmpChecklist::where('employee_num', $employee->employee_num)->get(); // employee_num is FK
        $users = \App\Models\User::all();
        $uploadTypes = \App\Models\UploadType::catalogForEmployee($employee);
        $requiredDocumentChecklist = app(\App\Services\DocumentComplianceService::class)->forEmployee($employee);
        $assessmentPeriods = $this->loadEmployeeAssessmentPeriods($employee);
        $requestedAssessmentPeriodId = filled(request('assessment_period_id'))
            ? (int) request('assessment_period_id')
            : null;
        $selectedAssessmentPeriodId = $this->resolveSelectedAssessmentPeriodIdForEmployee($employee, $assessmentPeriods);

        if ($requestedAssessmentPeriodId
            && $selectedAssessmentPeriodId
            && $requestedAssessmentPeriodId !== $selectedAssessmentPeriodId
            && ! request()->boolean('view_period')) {
            $resolvedPeriod = $assessmentPeriods->firstWhere('id', $selectedAssessmentPeriodId);

            return redirect()->to(request()->fullUrlWithQuery([
                'assessment_period_id' => $selectedAssessmentPeriodId,
                'assessment_year' => $resolvedPeriod?->period_year,
            ]));
        }

        $suggestedAssessmentPeriod = app(EmployeeAssessmentPeriodService::class)->suggestedPeriodForEmployee(
            $employee,
            request('assessment_review_date') ? \Illuminate\Support\Carbon::parse(request('assessment_review_date')) : null
        );
        $jsonRow = \App\Models\EmployeeCompetencyAssessment::where('employee_num', $employee->employee_num)
            ->where('assessment_period_id', $selectedAssessmentPeriodId)
            ->first();
        $rawDraftRow = $jsonRow;
       
        if ($jsonRow && $jsonRow->responses) {
            $decoded = is_array($jsonRow->responses)
                ? $jsonRow->responses
                : json_decode($jsonRow->responses, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            if (is_array($decoded)) {
                foreach ($decoded as $itemId => $data) {
                    $draftResponses[$itemId] = is_array($data)
                        ? ($data['response'] ?? null)
                        : $data;
                }
            }
        }
        $positionIdForTrainings = $employee->currentAssignment?->position_id ?? $employee->currentAssignment?->position?->id;
        $employeeTrainingItems = \App\Models\EmployeeTrainingItem::query()
            ->active()
            ->applicableToPosition($positionIdForTrainings)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // --- END: Load draft competency responses for Part G ---

        // Load all states for address select dropdown
        $states = State::orderBy('name')->get();

        // Load assessment item states and history for this employee and selected assessment period.
        $assessmentItemStates = [];
        $assessmentItemHistories = [];
        $empPerformanceChecklist = [];
        $empCompetencyAssessments = [];
        $performanceAssessmentHistory = [];
        $competencyAssessmentHistory = [];
        $performanceAssessmentSubmissions = \App\Models\EmployeePerformanceAssessment::query()
            ->where('employee_num', $employee->employee_num)
            ->get()
            ->keyBy('assessment_period_id');
        $performanceAssessmentStatuses = $performanceAssessmentSubmissions
            ->mapWithKeys(function ($submission, $assessmentPeriodId) {
                $meta = \App\Support\AssessmentWorkflowStatus::meta($submission->workflowStatus());

                return [
                    (int) $assessmentPeriodId => array_merge($meta, [
                        'id' => $submission->id,
                    ]),
                ];
            })
            ->all();
        $competencyAssessmentSubmissions = \App\Models\EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employee->employee_num)
            ->get()
            ->keyBy('assessment_period_id');
        $competencyAssessmentStatuses = $competencyAssessmentSubmissions
            ->mapWithKeys(function ($submission, $assessmentPeriodId) {
                $meta = \App\Support\AssessmentWorkflowStatus::meta($submission->workflowStatus());

                return [
                    (int) $assessmentPeriodId => array_merge($meta, [
                        'id' => $submission->id,
                    ]),
                ];
            })
            ->all();
        $selectedCompetencyAssessment = $selectedAssessmentPeriodId
            ? $competencyAssessmentSubmissions->get((int) $selectedAssessmentPeriodId)
            : null;
        $selectedPerformanceAssessment = $selectedAssessmentPeriodId
            ? $performanceAssessmentSubmissions->get((int) $selectedAssessmentPeriodId)
            : null;
        $trainingCompletionRelations = ['completedByUser', 'reviewedByUser', 'startedByUser', 'submittedByUser'];
        $employeeTrainingHireCompletions = \App\Models\EmployeeTrainingCompletion::query()
            ->with($trainingCompletionRelations)
            ->where('employee_num', $employee->employee_num)
            ->where('period_key', \App\Models\EmployeeTrainingCompletion::PERIOD_KEY_HIRE)
            ->get()
            ->keyBy('employee_training_item_id');
        $employeeTrainingPeriodCompletions = $selectedAssessmentPeriodId
            ? \App\Models\EmployeeTrainingCompletion::query()
                ->with($trainingCompletionRelations)
                ->where('employee_num', $employee->employee_num)
                ->where('period_key', (string) (int) $selectedAssessmentPeriodId)
                ->get()
                ->keyBy('employee_training_item_id')
            : collect();
        $employeeTrainingLatestCompletedAt = \App\Models\EmployeeTrainingCompletion::query()
            ->where('employee_num', $employee->employee_num)
            ->where('status', \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED)
            ->whereIn('employee_training_item_id', $employeeTrainingItems->pluck('id'))
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get(['employee_training_item_id', 'completed_at'])
            ->unique('employee_training_item_id')
            ->mapWithKeys(fn ($row) => [(int) $row->employee_training_item_id => $row->completed_at]);
        $reviewDate = '';
        $employeeAcknowledgeDt = '';
        $reviewerName = '';
        $reviewType = '';
        $assessmentPeriodLabels = $assessmentPeriods->keyBy('id');
        $allAssessmentEntries = \App\Models\EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $employee->employee_num)
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get();

        $assessmentItemHistories = $allAssessmentEntries
            ->groupBy('item_key')
            ->map(function ($entries) use ($users, $assessmentPeriodLabels, $selectedAssessmentPeriodId) {
                return $entries->map(function ($entry) use ($users, $assessmentPeriodLabels, $selectedAssessmentPeriodId) {
                    $period = $assessmentPeriodLabels->get($entry->assessment_period_id);

                    return [
                        'id' => $entry->id,
                        'rating' => $entry->rating,
                        'verified_dt' => optional($entry->assessment_date)->toDateString(),
                        'verified_by' => $entry->assessed_by,
                        'verified_by_name' => optional($users->firstWhere('id', $entry->assessed_by))->name ?? $entry->assessed_by,
                        'comments' => $entry->comments,
                        'assessment_period_id' => $entry->assessment_period_id,
                        'period_label' => $period ? ($period->date_from . ' to ' . $period->date_to) : ('Period #' . $entry->assessment_period_id),
                        'is_selected_period' => (int) $entry->assessment_period_id === (int) $selectedAssessmentPeriodId,
                        'revoked_at' => optional($entry->revoked_at)->format('Y-m-d H:i:s'),
                        'revoked_by' => $entry->revoked_by,
                        'revoked_by_name' => optional($users->firstWhere('id', $entry->revoked_by))->name ?? $entry->revoked_by,
                    ];
                })->values()->all();
            })
            ->all();

        $competencyEntriesByPeriod = $allAssessmentEntries
            ->filter(fn ($entry) => $entry->assessment_type === 'competency')
            ->groupBy('assessment_period_id');

        $competencyAssessmentHistory = \App\Support\CompetencyAssessmentHistoryBuilder::build(
            $employeeCompetencyItems,
            $competencyAssessmentSubmissions,
            $competencyEntriesByPeriod,
            $assessmentPeriodLabels,
            $selectedAssessmentPeriodId,
            $draftResponses ?? [],
            $users,
        );

        $performanceEntriesByPeriod = $allAssessmentEntries
            ->filter(fn ($entry) => $entry->assessment_type === 'performance')
            ->groupBy('assessment_period_id');

        $performanceAssessmentHistory = $performanceEntriesByPeriod
            ->keys()
            ->merge($performanceAssessmentSubmissions->keys())
            ->unique()
            ->map(function ($assessmentPeriodId) use ($employee, $performanceEntriesByPeriod, $assessmentPeriodLabels, $performanceAssessmentSubmissions, $selectedAssessmentPeriodId) {
                $entries = $performanceEntriesByPeriod->get($assessmentPeriodId, collect());
                $latestStates = $entries
                    ->filter(fn ($entry) => $entry->revoked_at === null)
                    ->groupBy('item_key')
                    ->map(function ($groupedEntries) {
                        return $groupedEntries
                            ->sortByDesc(fn ($entry) => sprintf('%s-%010d', optional($entry->assessment_date)->toDateString() ?? '', $entry->id))
                            ->first();
                    })
                    ->filter();

                $total = 0;
                $count = 0;
                foreach ($latestStates as $state) {
                    $score = PartFPerformanceScoring::numericScore((string) $state->rating);

                    if ($score === null) {
                        continue;
                    }

                    $total += $score;
                    $count++;
                }

                $average = $count > 0 ? round($total / $count, 2) : 0;
                $overall = $count === 0
                    ? 'N/A'
                    : PartFPerformanceScoring::overallLabel($average, $count);
                $period = $assessmentPeriodLabels->get($assessmentPeriodId);
                $latestAssessment = $entries
                    ->sortByDesc(fn ($entry) => sprintf('%s-%010d', optional($entry->assessment_date)->toDateString() ?? '', $entry->id))
                    ->first();
                $submission = $performanceAssessmentSubmissions->get((int) $assessmentPeriodId);

                $isFinalized = ! empty($submission?->finalized);
                $workflowStatus = $submission?->workflowStatus() ?? AssessmentWorkflowStatus::DRAFT;
                $totalRateableItems = count(PartFPerformanceScoring::scorableItemIds(
                    $employee->currentAssignment?->position_id ? (int) $employee->currentAssignment->position_id : null,
                    $employee->currentAssignment?->position?->title
                ));
                $assessmentDateRaw = optional($submission?->review_dt)->toDateString()
                    ?? optional($submission?->updated_at)->toDateString()
                    ?? optional(optional($latestAssessment)->assessment_date)->toDateString();

                return [
                    'assessment_period_id' => (int) $assessmentPeriodId,
                    'period_label' => $period ? $period->displayDateRange() : ('Period #' . $assessmentPeriodId),
                    'period_year' => $period?->period_year,
                    'assessment_date' => $assessmentDateRaw,
                    'items_count' => $count,
                    'total_items' => $totalRateableItems,
                    'total_score' => $submission?->total_score ?? $total,
                    'average_score' => $submission
                        ? number_format((float) $submission->average_score, 2)
                        : number_format($average, 2),
                    'overall_rating' => $submission?->overall_rating ?? $overall,
                    'status' => $submission
                        ? AssessmentWorkflowStatus::label($workflowStatus)
                        : 'Draft',
                    'status_key' => $workflowStatus,
                    'is_finalized' => $isFinalized,
                    'is_current' => (int) $assessmentPeriodId === (int) $selectedAssessmentPeriodId,
                    'performance_assessment_id' => $submission?->id,
                    'can_view_pdf' => ! empty($submission?->id) && $count > 0,
                ];
            })
            ->sortByDesc('assessment_date')
            ->values()
            ->all();

        if ($selectedAssessmentPeriodId) {
            $assessment = $selectedPerformanceAssessment;
            $period = \App\Models\EmployeeAssessmentPeriod::find($selectedAssessmentPeriodId);
            if ($period) {
                $reviewType = $period->review_type === 'Q' ? 'Quarterly' : 'Annual';
            }

            $assessmentEntries = $allAssessmentEntries
                ->where('assessment_period_id', (int) $selectedAssessmentPeriodId)
                ->values();

            $assessmentItemStates = $assessmentEntries
                ->filter(fn ($entry) => $entry->revoked_at === null)
                ->groupBy(function ($entry) {
                    if ($entry->assessment_type === 'performance' && !empty($entry->source_item_id)) {
                        return 'F_' . $entry->source_item_id;
                    }

                    return $entry->item_key;
                })
                ->map(function ($entries) use ($users) {
                    $latest = $entries->sortByDesc(fn ($entry) => sprintf('%s-%010d', optional($entry->assessment_date)->toDateString() ?? '', $entry->id))->first();

                    return [
                        'rating' => $latest->rating,
                        'verified_dt' => optional($latest->assessment_date)->toDateString(),
                        'verified_by' => $latest->assessed_by,
                        'verified_by_name' => optional($users->firstWhere('id', $latest->assessed_by))->name ?? $latest->assessed_by,
                        'comments' => $latest->comments,
                    ];
                })
                ->all();

            $legacyPerformanceEntries = $assessmentEntries
                ->filter(fn ($entry) => $entry->assessment_type === 'performance' && empty($entry->source_item_id) && str_starts_with((string) $entry->item_key, 'F_'))
                ->groupBy('item_key')
                ->map(function ($entries) use ($users) {
                    $latest = $entries->sortByDesc(fn ($entry) => sprintf('%s-%010d', optional($entry->assessment_date)->toDateString() ?? '', $entry->id))->first();

                    return [
                        'rating' => $latest->rating,
                        'verified_dt' => optional($latest->assessment_date)->toDateString(),
                        'verified_by' => $latest->assessed_by,
                        'verified_by_name' => optional($users->firstWhere('id', $latest->assessed_by))->name ?? $latest->assessed_by,
                        'comments' => $latest->comments,
                    ];
                })
                ->all();

            foreach ($legacyPerformanceEntries as $legacyItemKey => $legacyEntryState) {
                if (!isset($assessmentItemStates[$legacyItemKey])) {
                    $assessmentItemStates[$legacyItemKey] = $legacyEntryState;
                }
            }

            if ($assessment) {
                if ($assessment->items) {
                    $legacyItems = $assessment->itemsArray();
                    foreach ($legacyItems as $itemKey => $itemData) {
                        if (!isset($assessmentItemStates[$itemKey])) {
                            $assessmentItemStates[$itemKey] = [
                                'rating' => \App\Models\EmployeePerformanceAssessment::itemRating($itemData),
                                'verified_dt' => optional($assessment->assessment_date)->toDateString(),
                                'verified_by' => $assessment->assessed_by,
                                'verified_by_name' => optional($users->firstWhere('id', $assessment->assessed_by))->name ?? $assessment->assessed_by,
                                'comments' => null,
                            ];
                        }

                        if (!isset($assessmentItemHistories[$itemKey])) {
                            $assessmentItemHistories[$itemKey] = [[
                                'id' => null,
                                'rating' => \App\Models\EmployeePerformanceAssessment::itemRating($itemData),
                                'verified_dt' => optional($assessment->assessment_date)->toDateString(),
                                'verified_by' => $assessment->assessed_by,
                                'verified_by_name' => optional($users->firstWhere('id', $assessment->assessed_by))->name ?? $assessment->assessed_by,
                                'comments' => null,
                                'assessment_period_id' => (int) $selectedAssessmentPeriodId,
                                'period_label' => optional($assessmentPeriodLabels->get($selectedAssessmentPeriodId), fn ($period) => $period->date_from . ' to ' . $period->date_to) ?? ('Period #' . $selectedAssessmentPeriodId),
                                'is_selected_period' => true,
                                'revoked_at' => null,
                                'revoked_by' => null,
                                'revoked_by_name' => null,
                            ]];
                        }
                    }
                }
                $reviewDate = $this->formatAssessmentDateForInput($assessment->review_dt);
                $employeeAcknowledgeDt = $this->formatAssessmentDateForInput($assessment->acknowledge_dt);
                if (\App\Support\AssessmentWorkflowStatus::employeeCanConfirm($assessment->workflowStatus())
                    && blank($employeeAcknowledgeDt)) {
                    $employeeAcknowledgeDt = now()->toDateString();
                }
                // Lookup reviewer name if assessed_by is set
                if ($assessment->assessed_by) {
                    $reviewerUser = \App\Models\User::find($assessment->assessed_by);
                    $reviewerName = $reviewerUser ? $reviewerUser->name : '';
                }
            }

            $legacyChecklistItems = optional($empChecklists->firstWhere('employee_num', $employee->employee_num))->items ?? [];
            foreach ($legacyChecklistItems as $legacyKey => $legacyItem) {
                if (!str_starts_with((string) $legacyKey, 'competency::')) {
                    continue;
                }

                $competencyItemId = (int) \Illuminate\Support\Str::after((string) $legacyKey, 'competency::');
                if ($competencyItemId <= 0) {
                    continue;
                }

                $itemKey = 'G_' . $competencyItemId;
                if (isset($assessmentItemStates[$itemKey])) {
                    continue;
                }

                $assessmentItemStates[$itemKey] = [
                    'rating' => 'S',
                    'verified_dt' => $legacyItem['verified_dt'] ?? null,
                    'verified_by' => $legacyItem['verified_by'] ?? null,
                    'verified_by_name' => optional($users->firstWhere('id', $legacyItem['verified_by'] ?? null))->name ?? ($legacyItem['verified_by'] ?? null),
                    'comments' => $legacyItem['comments'] ?? null,
                ];

                $assessmentItemHistories[$itemKey] = [[
                    'id' => null,
                    'rating' => 'S',
                    'verified_dt' => $legacyItem['verified_dt'] ?? null,
                    'verified_by' => $legacyItem['verified_by'] ?? null,
                    'verified_by_name' => optional($users->firstWhere('id', $legacyItem['verified_by'] ?? null))->name ?? ($legacyItem['verified_by'] ?? null),
                    'comments' => $legacyItem['comments'] ?? null,
                    'assessment_period_id' => (int) $selectedAssessmentPeriodId,
                    'period_label' => optional($assessmentPeriodLabels->get($selectedAssessmentPeriodId), fn ($period) => $period->date_from . ' to ' . $period->date_to) ?? ('Period #' . $selectedAssessmentPeriodId),
                    'is_selected_period' => true,
                    'revoked_at' => null,
                    'revoked_by' => null,
                    'revoked_by_name' => null,
                ]];
            }
        }

        $empPerformanceChecklist = collect($assessmentItemStates)
            ->filter(fn ($item, $itemKey) => str_starts_with((string) $itemKey, 'F_'))
            ->all();

        $empCompetencyAssessments = collect($assessmentItemStates)
            ->filter(fn ($item, $itemKey) => str_starts_with((string) $itemKey, 'G_'))
            ->all();

        // PART F: Load section comments for this employee and assessment period
        $sectionComments = [];
        if ($selectedAssessmentPeriodId) {
            $comments = \App\Models\EmployeePerformanceSectionComment::where('employee_num', $employee->employee_num)
                ->where('assessment_period_id', $selectedAssessmentPeriodId)
                ->get();
            foreach ($comments as $comment) {
                $sectionComments[$comment->doc_type_id] = $comment->comment;
            }
        }

        // Part F reviewer display name — never default to the employee's own name on self-service.
        $supervisorName = $this->resolvePerformanceReviewerDisplayName(
            $employee,
            $selectedPerformanceAssessment,
            $users,
            $reviewerName ?? ''
        );

        // Get doc_type_id for each section
        $areasDevDocType = \App\Models\DocType::where('name', 'Areas Requiring Further Development')->first();
        $devPlansDocType = \App\Models\DocType::where('name', 'Development Plans')->first();
        $empCommentsDocType = \App\Models\DocType::where('name', 'Employee Comments')->first();

        $areasForDevelopment = $areasDevDocType && isset($sectionComments[$areasDevDocType->id]) ? $sectionComments[$areasDevDocType->id] : '';
        $developmentPlans = $devPlansDocType && isset($sectionComments[$devPlansDocType->id]) ? $sectionComments[$devPlansDocType->id] : '';
        $employeeComments = $empCommentsDocType && isset($sectionComments[$empCommentsDocType->id]) ? $sectionComments[$empCommentsDocType->id] : '';

        $reviewDt = $reviewDate; // For PART F form field
        $isAddMode = false;
        $maritalOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Marital Status')->value('id'))
            ->orderBy('sort_order')->get();
        $ethnicOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Ethnic Group')->value('id'))
            ->orderBy('sort_order')->get();
        $militaryOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Military Status')->value('id'))
            ->orderBy('sort_order')->get();
        $citizenOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Citizenship Status')->value('id'))
            ->orderBy('sort_order')->get();
        $actionOptions = DB::table('selectoptions')
            ->where('type_id', DB::table('optionstypes')->where('name', 'Action')->value('id'))
            ->orderBy('sort_order')->get();
        $unionCodeOptions = \App\Models\BPBargainingUnit::query()
            ->whereNotNull('union_code')
            ->orderBy('union_code')
            ->pluck('union_code')
            ->unique()
            ->values();

        $evaluatorActionsDisabled = \App\Support\AssessmentEvaluatorAuthorization::isEvaluatorActionBlocked(
            auth()->user(),
            $employee->employee_num
        );

        $canManageJobData = \App\Support\EmployeeJobDataAuthorization::canManageJobData(
            auth()->user(),
            $employee
        );
        $canEditCoreTabs = $this->canEditCoreEmployeeTabs(auth()->user());

        $editOptions = $request->attributes->get('employee_edit_options', []);
        $isSelfService = (bool) ($editOptions['isSelfService'] ?? false);
        $viewName = $isSelfService ? 'employment.my-employment' : 'admin.facilities.employee.edit_employee';
        $employeeFormRoutes = $this->employeeFormRoutes($employee, $isSelfService);

        return view($viewName, compact(
            'employee',
            'employeesListFacility',
            'employeesListFacilityId',
            'evaluatorActionsDisabled',
            'departments',
            'positions',
            'facilities',
            'checklistItems',
            'employeeCompetencyItems',
            'employeeTrainingItems',
            'employeeTrainingHireCompletions',
            'employeeTrainingPeriodCompletions',
            'employeeTrainingLatestCompletedAt',
            'empChecklists',
            'users',
            'empPerformanceChecklist',
            'empCompetencyAssessments',
            'performanceAssessmentHistory',
            'performanceAssessmentStatuses',
            'selectedPerformanceAssessment',
            'competencyAssessmentHistory',
            'competencyAssessmentStatuses',
            'selectedCompetencyAssessment',
            'assessmentItemStates',
            'assessmentItemHistories',
            'assessmentPeriods',
            'selectedAssessmentPeriodId',
            'suggestedAssessmentPeriod',
            'sectionComments',
            'supervisorName',
            'areasForDevelopment',
            'developmentPlans',
            'employeeComments',
            'reviewDate',
            'employeeAcknowledgeDt',
            'reviewerName',
            'reviewType',
            'reviewDt',
            'states',
            'isAddMode',
            'maritalOptions',
            'ethnicOptions',
            'militaryOptions',
            'citizenOptions',
            'actionOptions',
            'unionCodeOptions',
            'uploadTypes',
            'draftResponses',
            'rawDraftRow',
            'isSelfService',
            'canManageJobData',
            'canEditCoreTabs',
            'employeeFormRoutes',
            'requiredDocumentChecklist',
        ));
    }
}
