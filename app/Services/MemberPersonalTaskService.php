<?php

namespace App\Services;

use App\Models\ChecklistItem;
use App\Models\PersonalTask;
use App\Models\User;
use Illuminate\Support\Collection;

class MemberPersonalTaskService
{
    private const APPLICANT_INFORMATION_DOC_TYPE = 'Applicant Information';

    /**
     * @var list<string>
     */
    private const APPLICANT_ONLY_TITLES = [
        'Application Form',
        'Applicant Disclosure',
        'Reference Check #1',
        'Reference Check #2',
        'Offer Letter (if applicable)',
        'Job Data: Hire / Rehire',
        'Emergency Contact Information',
        'Job Description',
    ];

    public function __construct(
        protected MemberProfileHrReviewService $profileHrReview,
    ) {}

    /**
     * @param  array<string, mixed>  $dashboardPayload
     * @return list<array<string, mixed>>
     */
    public function buildStaffTasks(
        User $user,
        array $dashboardPayload,
        ?int $limit = 8,
        bool $includeAssignedPersonalTasks = true
    ): array {
        $employee = $dashboardPayload['bpEmployee'] ?? $user->resolvedBpEmployee();
        $profileTasks = $this->build($user);
        $assigned = $includeAssignedPersonalTasks
            ? $this->buildAssignedPersonalTasks($user)
            : collect();

        if (! $employee) {
            return $this->prioritizeTasks(
                collect($profileTasks)
                    ->merge($this->filterApplicantTasks($dashboardPayload['todos'] ?? []))
                    ->merge($assigned),
                $limit
            );
        }

        return $this->prioritizeTasks(
            collect($profileTasks)
                ->merge($this->buildEmployeeWorkTasks($dashboardPayload))
                ->merge($this->buildAssessmentConfirmationTasks($user, $employee))
                ->merge($assigned),
            $limit
        );
    }

    /**
     * Open personal tasks assigned to the user (e.g. DSD training reviews).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function buildAssignedPersonalTasks(User $user): Collection
    {
        app(EmployeeTrainingWorkflowService::class)->syncCompletedAssignmentTasksForUser($user);

        return PersonalTask::query()
            ->where('assigned_to', $user->id)
            ->where('status', PersonalTask::STATUS_PENDING)
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->latest('due_at')
            ->limit(40)
            ->get()
            ->map(function (PersonalTask $task) {
                $rawDescription = (string) ($task->description ?? '');
                $description = trim((string) preg_replace(
                    '/^\[(?:training_completion_id|upload_verification_id|upload_correction_id):\d+\]\s*/',
                    '',
                    $rawDescription
                ));
                $description = trim((string) preg_replace(
                    '/^\[training_assignment:\d+:[^\]]+\]\s*/',
                    '',
                    $description
                ));
                $route = filled($task->action_url)
                    ? (string) $task->action_url
                    : route('member.tasks');
                $title = (string) $task->title;
                $isTrainingReview = str_contains($rawDescription, '[training_completion_id:')
                    || str_starts_with($title, 'Review training:');
                $isDocumentReview = str_contains($rawDescription, '[upload_verification_id:')
                    || str_starts_with($title, 'Verify document');
                $isDocumentCorrection = str_contains($rawDescription, '[upload_correction_id:')
                    || str_starts_with($title, 'Correct & resubmit');

                $category = match (true) {
                    $isTrainingReview => 'training-review',
                    $isDocumentReview, $isDocumentCorrection => 'document-review',
                    default => 'assigned-task',
                };

                // Document verification / correction tasks always surface as high priority.
                $priority = ($isDocumentReview || $isDocumentCorrection)
                    ? 'high'
                    : (in_array($task->priority, PersonalTask::PRIORITIES, true) ? $task->priority : 'medium');

                return array_merge(
                    $this->task(
                        'personal-'.$task->id,
                        $title,
                        $description !== ''
                            ? $description
                            : 'Assigned task requiring your attention.',
                        $category,
                        $priority,
                        $route,
                        false,
                        'open',
                    ),
                    [
                        'action_label' => filled($task->action_url)
                            ? (string) ($task->action_label ?: 'Open')
                            : 'View',
                    ]
                );
            });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(User $user): array
    {
        $employee = $user->resolvedBpEmployee(['phone', 'address', 'phones']);
        $assessment = $this->profileHrReview->assess($user, $employee);
        $profileUrl = route('settings.profile');
        $emergencyUrl = route('settings.profile') . '#emergency-contacts';
        $tasks = [];

        if (! $assessment['emergency_complete']) {
            $tasks[] = $this->task(
                'todo-emergency-contact',
                'Add your emergency contact',
                'Provide at least one primary emergency contact with a phone number.',
                'profile',
                'high',
                $emergencyUrl,
            );
        }

        if (! $assessment['portal_complete']) {
            $tasks[] = $this->task(
                'todo-complete-profile',
                'Complete your profile information',
                'Add your display name, email, and verify your email address under My Profile.',
                'profile',
                'high',
                $profileUrl . '#account',
            );
        }

        if ($assessment['ready_to_submit'] && ! $assessment['pending_hr'] && ! $assessment['hr_confirmed']) {
            $tasks[] = $this->task(
                'todo-submit-profile-hr',
                'Submit profile for HR confirmation',
                'Your portal details and emergency contact are ready. Submit them for HR to review and confirm.',
                'profile',
                'high',
                route('settings.profile.submit-hr-review'),
                false,
                'submit',
            );
        }

        if ($assessment['pending_hr']) {
            $tasks[] = $this->task(
                'todo-profile-pending-hr',
                'Profile awaiting HR confirmation',
                'HR has been notified to review and confirm your profile information.',
                'profile',
                'medium',
                $profileUrl,
            );
        }

        return $tasks;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     */
    protected function buildEmployeeWorkTasks(array $payload): array
    {
        $tasks = [];
        $coveredKeys = [];
        $certificationsUrl = route('member.certifications');
        $documentsPageUrl = route('member.documents');
        $employmentPortalUrl = route('employment.portal');
        $trainingsUrl = route('member.checklists');

        $requiredDocuments = $payload['documentsCenter']['compliance_missing']
            ?? $payload['documentsNeeded']
            ?? [];

        $applicantChecklistIds = $this->applicantChecklistItemIds($requiredDocuments);

        foreach ($requiredDocuments as $doc) {
            if ($this->isApplicantOnlyDocument($doc, $applicantChecklistIds)) {
                continue;
            }

            $status = (string) ($doc['status'] ?? '');

            $dedupeKey = $this->documentDedupeKey($doc);
            if (isset($coveredKeys[$dedupeKey])) {
                continue;
            }
            $coveredKeys[$dedupeKey] = true;

            $uploadTypeId = $doc['upload_type_id'] ?? null;
            $needsUpload = in_array($status, ['missing', 'not_on_file', 'rejected', 'expired', 'expiry_missing'], true);
            $route = $employmentPortalUrl;

            if ($needsUpload && $uploadTypeId) {
                $route = $documentsPageUrl . '?' . http_build_query(['upload_type_id' => $uploadTypeId]);
            } elseif ($uploadTypeId || ($doc['section'] ?? '') === 'Required for your position') {
                $route = $documentsPageUrl;
            }

            $title = (string) ($doc['title'] ?? 'Required document');
            if ($needsUpload && $uploadTypeId) {
                $title = 'Upload: ' . $title;
            }

            $tasks[] = $this->task(
                'todo-' . ($doc['id'] ?? uniqid('doc-')),
                $title,
                ($doc['section'] ?? 'Employee file') . ' — ' . ($doc['status_label'] ?? 'Action needed'),
                ($needsUpload && $uploadTypeId) ? 'upload' : 'document',
                (string) ($doc['priority'] ?? 'high'),
                $route,
                false,
                ($needsUpload && $uploadTypeId) ? 'upload' : null,
            );
        }

        foreach ($payload['certificationItems'] ?? [] as $cert) {
            $status = (string) ($cert['status'] ?? '');
            if ($status === 'valid') {
                continue;
            }

            $dedupeKey = $this->documentDedupeKey([
                'upload_type_id' => $cert['upload_type_id'] ?? null,
                'checklist_item_id' => $cert['checklist_item_id'] ?? null,
                'id' => $cert['id'] ?? null,
                'title' => $cert['title'] ?? null,
            ]);
            if (isset($coveredKeys[$dedupeKey])) {
                continue;
            }
            $coveredKeys[$dedupeKey] = true;

            $priority = in_array($status, ['expired', 'expires_today', 'expiring_urgent', 'not_on_file', 'not_verified'], true)
                ? 'high'
                : 'medium';

            $uploadTypeId = $cert['upload_type_id'] ?? null;
            $needsUpload = in_array($status, ['not_on_file', 'not_verified', 'expired', 'missing_expiry'], true);
            $route = $certificationsUrl;
            $action = null;

            if ($needsUpload && $uploadTypeId) {
                $route = $documentsPageUrl . '?' . http_build_query(['upload_type_id' => $uploadTypeId]);
                $action = 'upload';
            }

            $section = filled($cert['section'] ?? null) ? (string) $cert['section'] : 'Certification';
            $title = (string) ($cert['title'] ?? 'Certification');
            if ($action === 'upload') {
                $title = 'Upload: ' . $title;
            }

            $tasks[] = $this->task(
                'todo-' . ($cert['id'] ?? uniqid('cert-')),
                $title,
                ($cert['status_label'] ?? 'Action needed') . ' · ' . $section,
                $action === 'upload' ? 'upload' : 'certification',
                $priority,
                $route,
                false,
                $action,
            );
        }

        foreach ($payload['signaturesNeeded'] ?? [] as $signature) {
            $tasks[] = $this->task(
                (string) ($signature['id'] ?? uniqid('sig-')),
                'Sign: ' . ($signature['title'] ?? 'Document'),
                (string) ($signature['description'] ?? 'Your signature is required.'),
                'signature',
                (string) ($signature['priority'] ?? 'high'),
                (string) ($signature['action_url'] ?? $employmentPortalUrl),
            );
        }

        foreach (($payload['trainingsCenter']['items'] ?? []) as $training) {
            if (($training['category'] ?? '') === 'pre_employment') {
                continue;
            }

            $status = (string) ($training['status'] ?? '');
            if (! in_array($status, ['pending_signature', 'overdue', 'not_started'], true)) {
                continue;
            }

            $priority = in_array($status, ['pending_signature', 'overdue'], true) ? 'high' : 'medium';
            $tasks[] = array_merge(
                $this->task(
                    'todo-' . ($training['id'] ?? uniqid('training-')),
                    (string) ($training['title'] ?? 'Training item'),
                    (string) ($training['status_label'] ?? $training['subtitle'] ?? 'Complete this training requirement.'),
                    $status === 'pending_signature' ? 'signature' : 'training',
                    $priority,
                    (string) ($training['action_url'] ?? $trainingsUrl),
                ),
                ['status' => $status],
            );
        }

        foreach (array_slice($payload['reminders'] ?? [], 0, 3) as $reminder) {
            if (($reminder['type'] ?? '') !== 'danger') {
                continue;
            }

            $tasks[] = $this->task(
                'todo-' . ($reminder['id'] ?? uniqid('reminder-')),
                (string) ($reminder['title'] ?? 'Reminder'),
                (string) ($reminder['message'] ?? 'Immediate attention required.'),
                'reminder',
                'high',
                $documentsPageUrl,
            );
        }

        return $tasks;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function filterApplicantTasks(array $tasks): array
    {
        return collect($tasks)
            ->reject(fn (array $task) => $this->shouldHideApplicantTask($task))
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $documents
     * @return array<int, true>
     */
    protected function applicantChecklistItemIds(array $documents): array
    {
        $ids = collect($documents)
            ->map(fn (array $doc) => $this->resolveChecklistItemId($doc))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return ChecklistItem::query()
            ->with('docType')
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (ChecklistItem $item) => ($item->docType?->name ?? '') === self::APPLICANT_INFORMATION_DOC_TYPE)
            ->mapWithKeys(fn (ChecklistItem $item) => [(int) $item->id => true])
            ->all();
    }

    /**
     * @param  array<int, true>  $applicantChecklistIds
     */
    protected function isApplicantOnlyDocument(array $doc, array $applicantChecklistIds): bool
    {
        $checklistItemId = $this->resolveChecklistItemId($doc);
        if ($checklistItemId && isset($applicantChecklistIds[$checklistItemId])) {
            return true;
        }

        return in_array((string) ($doc['title'] ?? ''), self::APPLICANT_ONLY_TITLES, true);
    }

    protected function shouldHideApplicantTask(array $task): bool
    {
        if (($task['category'] ?? '') === 'pre-employment') {
            return true;
        }

        if (str_starts_with((string) ($task['id'] ?? ''), 'todo-pe-')) {
            return true;
        }

        if (($task['route'] ?? '') === route('pre-employment.portal')) {
            return true;
        }

        if (str_contains(strtolower((string) ($task['title'] ?? '')), 'pre-employment')) {
            return true;
        }

        if (! in_array(($task['category'] ?? ''), ['document', 'upload'], true)) {
            return false;
        }

        $doc = [
            'id' => preg_replace('/^todo-/', '', (string) ($task['id'] ?? '')),
            'title' => preg_replace('/^Upload:\s*/', '', (string) ($task['title'] ?? '')),
        ];

        return $this->isApplicantOnlyDocument($doc, $this->applicantChecklistItemIds([$doc]));
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    protected function resolveChecklistItemId(array $doc): ?int
    {
        if (! empty($doc['checklist_item_id'])) {
            return (int) $doc['checklist_item_id'];
        }

        $id = (string) ($doc['id'] ?? '');
        if (preg_match('/^(?:todo-)?doc(?:-exp)?-(\d+)$/', $id, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    protected function documentDedupeKey(array $doc): string
    {
        if (! empty($doc['upload_type_id'])) {
            return 'upload:' . $doc['upload_type_id'];
        }

        $checklistItemId = $this->resolveChecklistItemId($doc);
        if ($checklistItemId) {
            return 'checklist:' . $checklistItemId;
        }

        return 'doc:' . ($doc['id'] ?? $doc['title'] ?? uniqid());
    }

    /**
     * Assessment confirmation/review tasks for the signed-in user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildAssessmentConfirmationTasks(User $user, ?\App\Models\BPEmployee $employee): Collection
    {
        $dashboard = app(MemberDashboardService::class);

        return collect($dashboard->pendingEmployeeAssessmentConfirmationTodos($user, $employee))
            ->merge($dashboard->pendingReviewerAssessmentTasks($user))
            ->map(fn (array $todo) => array_merge($todo, [
                'route' => $todo['route'] ?? $todo['url'] ?? null,
            ]));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $tasks
     * @return list<array<string, mixed>>
     */
    protected function prioritizeTasks(Collection $tasks, ?int $limit = 8): array
    {
        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
        $categoryOrder = [
            'signature' => 0,
            'competency-confirmation' => 0,
            'performance-confirmation' => 0,
            'assessment-review' => 0,
            'training-review' => 0,
            'document-review' => 0,
            'assigned-task' => 0,
            'profile' => 1,
            'upload' => 2,
            'certification' => 3,
            'document' => 4,
            'reminder' => 5,
            'training' => 6,
            'security' => 7,
            'account' => 8,
        ];

        $sorted = $tasks
            ->where('done', false)
            ->sortBy(fn (array $task) => [
                $this->taskDisplayTier($task),
                $priorityOrder[$task['priority'] ?? 'medium'] ?? 2,
                $categoryOrder[$task['category'] ?? ''] ?? 9,
                strtolower((string) ($task['title'] ?? '')),
            ])
            ->unique('id');

        if ($limit !== null) {
            $signatureTasks = $sorted->filter(fn (array $task) => $this->isSignatureOrConfirmationTask($task));
            $otherTasks = $sorted->reject(fn (array $task) => $this->isSignatureOrConfirmationTask($task));
            $otherLimit = max(0, $limit - $signatureTasks->count());
            $sorted = $signatureTasks->concat($otherTasks->take($otherLimit));
        }

        return $sorted
            ->map(fn (array $task) => array_merge($task, ['route' => $task['route'] ?? $task['url'] ?? null]))
            ->values()
            ->all();
    }

    protected function taskDisplayTier(array $task): int
    {
        if ($this->isSignatureOrConfirmationTask($task)) {
            return 0;
        }

        if ($this->isRequiredFieldTask($task)) {
            return 1;
        }

        return 2;
    }

    protected function isRequiredFieldTask(array $task): bool
    {
        if (($task['action'] ?? '') === 'upload') {
            return true;
        }

        $title = strtolower((string) ($task['title'] ?? ''));
        if (str_starts_with($title, 'upload:')) {
            return true;
        }

        $category = (string) ($task['category'] ?? '');
        if (! in_array($category, ['upload', 'document', 'profile', 'certification'], true)) {
            return false;
        }

        if ($category === 'profile' && (string) ($task['id'] ?? '') === 'todo-profile-pending-hr') {
            return false;
        }

        return true;
    }

    protected function isSignatureOrConfirmationTask(array $task): bool
    {
        if (in_array(($task['category'] ?? ''), [
            'signature',
            'competency-confirmation',
            'performance-confirmation',
            'assessment-review',
            'training-review',
            'document-review',
            'assigned-task',
        ], true)) {
            return true;
        }

        if (($task['action'] ?? '') === 'submit') {
            return true;
        }

        if (($task['action'] ?? '') === 'sign') {
            return true;
        }

        if (($task['status'] ?? '') === 'pending_signature') {
            return true;
        }

        if (str_starts_with((string) ($task['id'] ?? ''), 'sig-')) {
            return true;
        }

        if (str_starts_with((string) ($task['id'] ?? ''), 'confirm-')) {
            return true;
        }

        $title = strtolower((string) ($task['title'] ?? ''));
        if (str_starts_with($title, 'sign:')) {
            return true;
        }

        if (str_contains($title, 'confirm competency assessment')
            || str_contains($title, 'confirm performance appraisal')
            || str_contains($title, 'review updated competency assessment')
            || str_contains($title, 'review updated performance appraisal')) {
            return true;
        }

        if (str_contains($title, 'reviewer approval')
            || str_contains($title, 'for reviewer approval')) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function task(
        string $id,
        string $title,
        string $description,
        string $category,
        string $priority,
        string $route,
        bool $done = false,
        ?string $action = null,
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'priority' => $priority,
            'route' => $route,
            'url' => $route,
            'done' => $done,
            'action' => $action,
        ];
    }
}

