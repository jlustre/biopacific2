<?php

namespace App\Services;

use App\Models\ChecklistItem;
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
    public function buildStaffTasks(User $user, array $dashboardPayload): array
    {
        $employee = $dashboardPayload['bpEmployee'] ?? $user->resolvedBpEmployee();
        $profileTasks = $this->build($user);

        if (! $employee) {
            return $this->prioritizeTasks(
                collect($profileTasks)->merge($this->filterApplicantTasks($dashboardPayload['todos'] ?? []))
            );
        }

        return $this->prioritizeTasks(
            collect($profileTasks)->merge($this->buildEmployeeWorkTasks($dashboardPayload))
        );
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
        $trainingsUrl = route('member.trainings');

        $requiredDocuments = $payload['documentsCenter']['compliance_missing']
            ?? $payload['documentsNeeded']
            ?? [];

        $applicantChecklistIds = $this->applicantChecklistItemIds($requiredDocuments);

        foreach ($requiredDocuments as $doc) {
            if ($this->isApplicantOnlyDocument($doc, $applicantChecklistIds)) {
                continue;
            }

            $status = (string) ($doc['status'] ?? '');
            if ($status === 'pending_review') {
                continue;
            }

            $dedupeKey = $this->documentDedupeKey($doc);
            if (isset($coveredKeys[$dedupeKey])) {
                continue;
            }
            $coveredKeys[$dedupeKey] = true;

            $uploadTypeId = $doc['upload_type_id'] ?? null;
            $needsUpload = in_array($status, ['missing', 'not_on_file', 'expired', 'expiry_missing'], true);
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
     * @param  Collection<int, array<string, mixed>>  $tasks
     * @return list<array<string, mixed>>
     */
    protected function prioritizeTasks(Collection $tasks): array
    {
        $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
        $categoryOrder = [
            'signature' => 0,
            'profile' => 1,
            'upload' => 2,
            'certification' => 3,
            'reminder' => 4,
            'document' => 5,
            'training' => 6,
            'security' => 7,
            'account' => 8,
        ];

        return $tasks
            ->where('done', false)
            ->sortBy(fn (array $task) => [
                $this->isSignatureOrConfirmationTask($task) ? 0 : 1,
                $priorityOrder[$task['priority'] ?? 'medium'] ?? 2,
                $categoryOrder[$task['category'] ?? ''] ?? 9,
                strtolower((string) ($task['title'] ?? '')),
            ])
            ->unique('id')
            ->take(8)
            ->map(fn (array $task) => array_merge($task, ['route' => $task['route'] ?? $task['url'] ?? null]))
            ->values()
            ->all();
    }

    protected function isSignatureOrConfirmationTask(array $task): bool
    {
        if (($task['category'] ?? '') === 'signature') {
            return true;
        }

        if (($task['action'] ?? '') === 'submit') {
            return true;
        }

        if (($task['status'] ?? '') === 'pending_signature') {
            return true;
        }

        if (str_starts_with((string) ($task['id'] ?? ''), 'sig-')) {
            return true;
        }

        $title = strtolower((string) ($task['title'] ?? ''));
        if (str_starts_with($title, 'sign:')) {
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

