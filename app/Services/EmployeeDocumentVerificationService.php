<?php

namespace App\Services;

use App\Mail\EmployeeDocumentApprovedMail;
use App\Mail\EmployeeDocumentSubmissionMail;
use App\Models\BPEmployee;
use App\Models\Facility;
use App\Models\PersonalTask;
use App\Models\Upload;
use App\Models\User;
use App\Support\UploadNotificationContext;
use App\Support\UploadSubmissionReason;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmployeeDocumentVerificationService
{
    /**
     * DSD / DON / facility-admin at the facility, plus the employee's direct supervisor(s).
     *
     * @return Collection<int, User>
     */
    public function resolveReviewers(BPEmployee $employee): Collection
    {
        $employee->loadMissing([
            'currentAssignment.facility',
            'currentAssignment.position.reportsToPosition',
            'user',
        ]);

        $facility = $employee->currentAssignment?->facility;
        $facilityId = $employee->currentAssignment?->facility_id;
        $reviewers = collect();

        if ($facility instanceof Facility) {
            $reviewers = $reviewers->merge(UploadNotificationContext::facilityDocumentReviewerUsers($facility));
        }

        $supervisorPositionId = $employee->currentAssignment?->reports_to
            ?: $employee->currentAssignment?->position?->reports_to_position_id;

        if ($supervisorPositionId) {
            $supervisorUsers = User::query()
                ->whereHas('bpEmployee.currentAssignment', function ($query) use ($supervisorPositionId, $facilityId) {
                    $query->where('position_id', $supervisorPositionId);
                    if ($facilityId) {
                        $query->where('facility_id', $facilityId);
                    }
                })
                ->orderBy('name')
                ->get();
            $reviewers = $reviewers->merge($supervisorUsers);
        }

        $employeeUserId = $employee->user?->id;

        return $reviewers
            ->unique('id')
            ->reject(fn (User $user) => $employeeUserId && (int) $user->id === (int) $employeeUserId)
            ->values();
    }

    public function actorIsEmployee(User $actor, BPEmployee $employee): bool
    {
        $actorEmployee = method_exists($actor, 'resolvedBpEmployee')
            ? $actor->resolvedBpEmployee()
            : null;

        return $actorEmployee
            && (string) $actorEmployee->employee_num === (string) $employee->employee_num;
    }

    public function actorCanReview(User $actor, BPEmployee $employee): bool
    {
        if ($this->actorIsEmployee($actor, $employee)) {
            return false;
        }

        if ($actor->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            return true;
        }

        return $this->resolveReviewers($employee)->contains(fn (User $user) => (int) $user->id === (int) $actor->id);
    }

    /**
     * DSD / facility leadership / supervisors who can browse team document history.
     */
    public function actorCanBrowseTeamDocumentHistory(User $actor): bool
    {
        if ($actor->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            return true;
        }

        return $this->supervisedEmployeesQuery($actor)->exists();
    }

    /**
     * Employees visible to the actor for document history browsing (role-scoped).
     */
    public function employeesInDocumentHistoryScopeQuery(User $actor)
    {
        $query = BPEmployee::query()
            ->with(['currentAssignment.position', 'currentAssignment.facility'])
            ->whereHas('currentAssignment');

        if ($actor->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return $query->orderedByName();
        }

        if ($actor->hasRole(['facility-admin', 'facility-dsd', 'don'])) {
            $facilityId = $this->actorFacilityId($actor);
            if (! $facilityId) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->whereHas('currentAssignment', fn ($q) => $q->where('facility_id', $facilityId))
                ->orderedByName();
        }

        return $this->supervisedEmployeesQuery($actor)->orderedByName();
    }

    /**
     * @return Collection<int, BPEmployee>
     */
    public function searchEmployeesForDocumentHistory(User $actor, string $search, int $limit = 20): Collection
    {
        $search = trim($search);
        if ($search === '' || mb_strlen($search) < 2) {
            return collect();
        }

        if (! $this->actorCanBrowseTeamDocumentHistory($actor)) {
            return collect();
        }

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';

        return $this->employeesInDocumentHistoryScopeQuery($actor)
            ->where(function ($scope) use ($like, $search) {
                $scope->where('employee_num', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", [$like]);

                if (ctype_digit($search)) {
                    $scope->orWhere('id', (int) $search);
                }
            })
            ->limit($limit)
            ->get();
    }

    public function actorCanViewEmployeeDocumentHistory(User $actor, BPEmployee $employee): bool
    {
        if (! $this->actorCanBrowseTeamDocumentHistory($actor)) {
            return false;
        }

        if ($this->actorIsEmployee($actor, $employee)) {
            return false;
        }

        return $this->employeesInDocumentHistoryScopeQuery($actor)
            ->whereKey($employee->id)
            ->exists();
    }

    protected function actorFacilityId(User $actor): ?int
    {
        if ($actor->facility_id) {
            return (int) $actor->facility_id;
        }

        $actorEmployee = method_exists($actor, 'resolvedBpEmployee')
            ? $actor->resolvedBpEmployee(['currentAssignment'])
            : null;

        $facilityId = $actorEmployee?->currentAssignment?->facility_id;

        return $facilityId ? (int) $facilityId : null;
    }

    protected function supervisedEmployeesQuery(User $actor)
    {
        $actorEmployee = method_exists($actor, 'resolvedBpEmployee')
            ? $actor->resolvedBpEmployee(['currentAssignment'])
            : null;

        $positionId = $actorEmployee?->currentAssignment?->position_id;
        $facilityId = $actorEmployee?->currentAssignment?->facility_id;

        $query = BPEmployee::query()
            ->with(['currentAssignment.position', 'currentAssignment.facility'])
            ->whereHas('currentAssignment');

        if (! $positionId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('currentAssignment', function ($assignment) use ($positionId, $facilityId) {
            $assignment->where(function ($scope) use ($positionId) {
                $scope->where('reports_to', $positionId)
                    ->orWhereHas('position', fn ($position) => $position->where('reports_to_position_id', $positionId));
            });

            if ($facilityId) {
                $assignment->where('facility_id', $facilityId);
            }
        });
    }

    /**
     * Assign review tasks to DSD / leadership / direct supervisors for a pending upload.
     * Existing open review tasks for this upload are kept until the document is approved.
     *
     * @return Collection<int, PersonalTask>
     */
    public function assignReviewTasks(Upload $upload, BPEmployee $employee, User $submittedBy): Collection
    {
        $reviewers = $this->resolveReviewers($employee);
        if ($reviewers->isEmpty()) {
            return collect();
        }

        $reviewUrl = $this->reviewTaskUrl($employee, $upload);
        $description = $this->reviewTaskDescription($employee, $upload);
        $documentName = $this->documentLabel($upload);
        $existing = $this->openReviewTasksQuery($upload)->get();
        $tasks = collect();
        $coveredReviewerIds = [];

        foreach ($existing as $task) {
            $assigneeId = (int) $task->assigned_to;
            $coveredReviewerIds[$assigneeId] = true;
            $task->update([
                'title' => 'Verify document: '.$documentName,
                'description' => $description,
                'action_url' => $reviewUrl,
                'action_label' => 'View & approve',
                'priority' => 'high',
                'status' => PersonalTask::STATUS_PENDING,
                'due_at' => now()->addDays(7),
                // Keep original assignee; do not reopen cancelled as new rows when already pending.
            ]);
            $tasks->push($task->fresh());
        }

        foreach ($reviewers as $reviewer) {
            if (isset($coveredReviewerIds[(int) $reviewer->id])) {
                continue;
            }

            $tasks->push(PersonalTask::query()->create([
                'created_by' => $submittedBy->id,
                'assigned_to' => $reviewer->id,
                'title' => 'Verify document: '.$documentName,
                'description' => $description,
                'action_url' => $reviewUrl,
                'action_label' => 'View & approve',
                'priority' => 'high',
                'status' => PersonalTask::STATUS_PENDING,
                'due_at' => now()->addDays(7),
            ]));
        }

        return $tasks->values();
    }

    /**
     * Email facility reviewers (and supervisors who have email) about the submission.
     */
    public function notifyReviewersOfSubmission(
        Upload $upload,
        BPEmployee $employee,
        User $submittedBy,
        ?string $submissionReason = null,
        ?string $customSubject = null,
        ?string $customMessage = null,
    ): void {
        $upload->loadMissing(['employee.user', 'facility', 'uploadType']);
        $facility = $upload->facility ?? $employee->currentAssignment?->facility;

        if (! $facility) {
            throw new \RuntimeException('No facility is linked to this upload.');
        }

        $emails = $this->resolveReviewers($employee)
            ->pluck('email')
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn (string $email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($emails === []) {
            // Fall back to classic facility leadership emails.
            $emails = UploadNotificationContext::facilityDocumentReviewerEmails($facility);
        }

        if ($emails === []) {
            throw new \RuntimeException('No DSD, DON, supervisor, or administrator contact email is configured for this facility.');
        }

        $reason = $submissionReason ?? $upload->submission_reason ?? UploadSubmissionReason::INITIAL;
        $mail = new EmployeeDocumentSubmissionMail(
            $upload,
            $employee,
            $facility,
            $submittedBy,
            $reason,
            $customSubject,
            $customMessage,
        );

        foreach ($emails as $email) {
            Mail::to($email)->send($mail);
        }
    }

    /**
     * After an employee submits: create reviewer tasks, then email reviewers.
     * Task creation is best-effort and must not block the pending status.
     */
    public function handlePendingSubmission(
        Upload $upload,
        BPEmployee $employee,
        User $submittedBy,
        ?string $submissionReason = null,
        ?string $customSubject = null,
        ?string $customMessage = null,
    ): void {
        try {
            $this->cancelOpenUploaderCorrectionTasks($upload);
            // Close review tasks for superseded uploads of the same type; keep this upload's tasks until approved.
            $this->cancelReviewTasksForOtherUploads($employee, $upload);
            $this->assignReviewTasks($upload, $employee, $submittedBy);
        } catch (Throwable $e) {
            Log::error('Failed to assign document verification tasks', [
                'upload_id' => $upload->id,
                'employee_id' => $employee->id,
                'exception' => $e,
            ]);
        }

        $this->notifyReviewersOfSubmission(
            $upload,
            $employee,
            $submittedBy,
            $submissionReason,
            $customSubject,
            $customMessage,
        );
    }

    /**
     * After rejection: keep reviewer tasks open until approval, open a correction task for the uploader, and notify them.
     */
    public function handleRejection(Upload $upload, BPEmployee $employee, User $reviewer, string $notes): ?PersonalTask
    {
        $this->refreshOpenReviewTasksAfterRejection($upload, $employee, $notes);
        $task = $this->assignUploaderCorrectionTask($upload, $employee, $reviewer, $notes);
        $this->notifyEmployeeRejected($upload, $employee, $notes);

        return $task;
    }

    /**
     * Keep DSD/supervisor verification tasks pending after a rejection (awaiting corrected resubmission).
     * These tasks are only completed/cancelled when the upload is approved.
     */
    public function refreshOpenReviewTasksAfterRejection(Upload $upload, BPEmployee $employee, string $notes): void
    {
        $documentName = $this->documentLabel($upload);
        $reviewUrl = $this->reviewTaskUrl($employee, $upload);
        $marker = $this->reviewTaskMarker($upload);
        $trimmedNotes = trim($notes);
        $description = $marker."\n"
            ."Document \"{$documentName}\" was returned for correction.\n"
            .'Reason: '.($trimmedNotes !== '' ? $trimmedNotes : 'Needs correction')."\n"
            .'Keep this task open. Re-check and approve after the employee resubmits.';

        $this->openReviewTasksQuery($upload)->update([
            'title' => 'Verify document (awaiting correction): '.$documentName,
            'description' => $description,
            'action_url' => $reviewUrl,
            'action_label' => 'View & approve',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => now()->addDays(7),
        ]);
    }

    /**
     * Create/replace an open task for the uploader to fix and resubmit the rejected document.
     */
    public function assignUploaderCorrectionTask(
        Upload $upload,
        BPEmployee $employee,
        User $reviewer,
        string $notes
    ): ?PersonalTask {
        $this->cancelOpenUploaderCorrectionTasks($upload);

        $employee->loadMissing('user');
        $uploader = $upload->user_id
            ? User::query()->find($upload->user_id)
            : $employee->user;

        if (! $uploader) {
            Log::warning('Document rejected but no uploader user account to assign a correction task', [
                'upload_id' => $upload->id,
                'employee_num' => $employee->employee_num,
            ]);

            return null;
        }

        $documentName = $this->documentLabel($upload);
        $notes = trim($notes);
        $isLicense = (bool) ($upload->uploadType?->is_license_or_certification ?? false);
        $actionUrl = $this->uploaderCorrectionUrl($upload, $isLicense);

        return PersonalTask::query()->create([
            'created_by' => $reviewer->id,
            'assigned_to' => $uploader->id,
            'title' => 'Correct & resubmit: '.$documentName,
            'description' => $this->uploaderCorrectionDescription($upload, $documentName, $reviewer, $notes),
            'action_url' => $actionUrl,
            'action_label' => 'Upload corrected file',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => now()->addDays(7),
        ]);
    }

    public function cancelOpenUploaderCorrectionTasks(Upload $upload): void
    {
        $this->openUploaderCorrectionTasksQuery($upload)->update([
            'status' => PersonalTask::STATUS_CANCELLED,
        ]);
    }

    public function completeOpenReviewTasks(Upload $upload, User $reviewer): void
    {
        $this->cancelOpenUploaderCorrectionTasks($upload);
        $tasks = $this->openReviewTasksQuery($upload)->get();

        foreach ($tasks as $task) {
            if ((int) $task->assigned_to === (int) $reviewer->id) {
                $task->update([
                    'status' => PersonalTask::STATUS_CONFIRMED,
                    'completed_at' => now(),
                    'completed_by' => $reviewer->id,
                    'confirmed_at' => now(),
                    'confirmed_by' => $reviewer->id,
                ]);
            } else {
                $task->update(['status' => PersonalTask::STATUS_CANCELLED]);
            }
        }
    }

    public function cancelOpenReviewTasks(Upload $upload): void
    {
        $this->openReviewTasksQuery($upload)->update([
            'status' => PersonalTask::STATUS_CANCELLED,
        ]);
    }

    /**
     * Email the employee that their document was approved (Messages uses verified_at separately).
     */
    public function notifyEmployeeApproved(Upload $upload, BPEmployee $employee): bool
    {
        $email = UploadNotificationContext::resolveEmployeeEmail($employee);
        if (! $email) {
            Log::warning('Document approved but employee has no email', [
                'upload_id' => $upload->id,
                'employee_num' => $employee->employee_num,
            ]);

            return false;
        }

        try {
            Mail::to($email)->send(new EmployeeDocumentApprovedMail(
                $employee->loadMissing('user'),
                $upload->loadMissing(['uploadType', 'checklistItem', 'verifiedBy']),
            ));

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to send document approval email to employee', [
                'upload_id' => $upload->id,
                'employee_num' => $employee->employee_num,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Email the employee that their document was rejected and needs correction.
     */
    public function notifyEmployeeRejected(Upload $upload, BPEmployee $employee, string $notes): bool
    {
        $email = UploadNotificationContext::resolveEmployeeEmail($employee);
        if (! $email) {
            Log::warning('Document rejected but employee has no email', [
                'upload_id' => $upload->id,
                'employee_num' => $employee->employee_num,
            ]);

            return false;
        }

        try {
            Mail::to($email)->send(new \App\Mail\EmployeeDocumentRejectedMail(
                $employee->loadMissing('user'),
                $upload->loadMissing(['uploadType', 'checklistItem', 'verifiedBy']),
                trim($notes),
            ));

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to send document rejection email to employee', [
                'upload_id' => $upload->id,
                'employee_num' => $employee->employee_num,
                'exception' => $e,
            ]);

            return false;
        }
    }

    public function documentLabel(Upload $upload): string
    {
        $upload->loadMissing(['uploadType', 'checklistItem']);

        return (string) (
            $upload->checklistItem?->name
            ?? $upload->uploadType?->name
            ?? $upload->original_filename
            ?? 'Document'
        );
    }

    protected function reviewTaskUrl(BPEmployee $employee, Upload $upload): string
    {
        return route('admin.employees.edit', $employee->id).'?'.http_build_query([
            'tab' => 'documents',
            'highlight_upload' => $upload->id,
        ]);
    }

    protected function reviewTaskDescription(BPEmployee $employee, Upload $upload): string
    {
        $name = trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) ?: $employee->employee_num;
        $documentName = $this->documentLabel($upload);
        $reason = $upload->submissionReasonLabel() ?: 'Submitted';
        $marker = $this->reviewTaskMarker($upload);

        return "{$marker}\nEmployee {$name} (#{$employee->employee_num}) submitted \"{$documentName}\" for verification ({$reason}). Open the employee documents tab to view and approve the file.";
    }

    protected function reviewTaskMarker(Upload $upload): string
    {
        return '[upload_verification_id:'.(int) $upload->id.']';
    }

    /**
     * When a newer upload of the same type is submitted, close review tasks for older
     * pending/rejected uploads so only the active submission's tasks remain open.
     */
    protected function cancelReviewTasksForOtherUploads(BPEmployee $employee, Upload $current): void
    {
        $typeId = (int) ($current->upload_type_id ?? 0);

        $otherIds = Upload::query()
            ->where('employee_num', $employee->employee_num)
            ->where('id', '!=', $current->id)
            ->when($typeId > 0, fn ($query) => $query->where('upload_type_id', $typeId))
            ->whereIn('verification_status', [
                Upload::VERIFICATION_PENDING,
                Upload::VERIFICATION_REJECTED,
            ])
            ->pluck('id');

        foreach ($otherIds as $otherId) {
            PersonalTask::query()
                ->where('status', PersonalTask::STATUS_PENDING)
                ->where('description', 'like', '%[upload_verification_id:'.(int) $otherId.']%')
                ->update(['status' => PersonalTask::STATUS_CANCELLED]);
        }
    }

    protected function openReviewTasksQuery(Upload $upload)
    {
        $marker = $this->reviewTaskMarker($upload);

        return PersonalTask::query()
            ->where('status', PersonalTask::STATUS_PENDING)
            ->where('description', 'like', '%'.$marker.'%');
    }

    protected function uploaderCorrectionMarker(Upload $upload): string
    {
        return '[upload_correction_id:'.(int) $upload->id.']';
    }

    protected function openUploaderCorrectionTasksQuery(Upload $upload)
    {
        $marker = $this->uploaderCorrectionMarker($upload);

        return PersonalTask::query()
            ->where('status', PersonalTask::STATUS_PENDING)
            ->where('description', 'like', '%'.$marker.'%');
    }

    protected function uploaderCorrectionUrl(Upload $upload, bool $isLicense): string
    {
        $params = array_filter([
            'upload_type_id' => $upload->upload_type_id,
        ]);

        if ($isLicense && \Illuminate\Support\Facades\Route::has('member.certifications')) {
            return route('member.certifications').($params ? '?'.http_build_query($params) : '');
        }

        if (\Illuminate\Support\Facades\Route::has('member.documents')) {
            return route('member.documents').($params ? '?'.http_build_query($params) : '');
        }

        return route('employment.portal', ['tab' => 'documents']);
    }

    protected function uploaderCorrectionDescription(
        Upload $upload,
        string $documentName,
        User $reviewer,
        string $notes
    ): string {
        $marker = $this->uploaderCorrectionMarker($upload);
        $reviewerName = $reviewer->name ?: 'Your reviewer';
        $notesLine = $notes !== '' ? $notes : 'Please upload a corrected version and resubmit for approval.';

        return "{$marker}\n{$reviewerName} returned \"{$documentName}\" for correction.\nReason: {$notesLine}\nUpload a corrected file and submit it again for approval.";
    }
}
