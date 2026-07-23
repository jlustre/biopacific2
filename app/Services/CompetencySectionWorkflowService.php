<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompetencySectionWorkflowService
{
    public const SNAPSHOT_KEY = 'section_workflow';

    /** @var array<string, string> */
    private const SECTION_ACCORDION_KEYS = [
        'HAND HYGIENE SKILLS' => 'hand-hygiene',
        'LICENSED NURSE COMPETENCY SKILLS' => 'ln',
        'LICENSED NURSE eMAR COMPETENCY' => 'ln-emar',
        'LICENSED NURSE POINT OF CARE COMPETENCY' => 'ln-poc',
        'MATRIXCARE PHYSICIAN ORDER AND DOCUMENTATION' => 'mc-phys-doc',
        'BLOOD ADMINISTRATION' => 'blood',
        'BLOOD GLUCOSE SYSTEM SKILLS' => 'blood-glucose',
        'TRACHEOSTOMY CARE' => 'tracheostomy',
        'NURSE TREATMENT SKILLS' => 'nurse-treatment',
        'VENTILATOR MANAGEMENT SKILLS' => 'ventilator',
        'PERSONAL PROTECTIVE EQUIPMENT (PPE)' => 'ppe',
        'MEDICATION ADMINISTRATION' => 'medication-admin',
        'USE OF HOYER LIFT' => 'hoyer-lift',
        'CNA SKILLS' => 'cna-skills',
        'PERINEAL CARE' => 'perineal-care',
        'DIRECTOR OF STAFF DEVELOPMENT' => 'dsd',
    ];

    public function sectionWorkflow(EmployeeCompetencyAssessment $assessment, string $sectionLabel): array
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $workflow = is_array($snapshot[self::SNAPSHOT_KEY] ?? null) ? $snapshot[self::SNAPSHOT_KEY] : [];

        return is_array($workflow[$sectionLabel] ?? null) ? $workflow[$sectionLabel] : [];
    }

    public function sectionStatus(EmployeeCompetencyAssessment $assessment, string $sectionLabel): string
    {
        $status = (string) ($this->sectionWorkflow($assessment, $sectionLabel)['status'] ?? AssessmentWorkflowStatus::DRAFT);

        return AssessmentWorkflowStatus::normalize($status);
    }

    public function sectionStatusLabel(EmployeeCompetencyAssessment $assessment, string $sectionLabel): string
    {
        return AssessmentWorkflowStatus::label($this->sectionStatus($assessment, $sectionLabel));
    }

    public function sectionDisplayStatusLabel(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        bool $isSubmitted = false,
        ?bool $hasRatedItems = null,
    ): string {
        if ($this->sectionIsExcluded($assessment, $sectionLabel)) {
            return 'Excluded';
        }

        $started = $this->sectionHasStarted($assessment, $sectionLabel, $isSubmitted, $hasRatedItems);

        if ($this->sectionWorkflow($assessment, $sectionLabel) !== []) {
            return match ($this->sectionStatus($assessment, $sectionLabel)) {
                AssessmentWorkflowStatus::COMPLETED => 'Completed',
                AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION => 'For Employee confirmation',
                AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL => 'For Reviewer approval',
                AssessmentWorkflowStatus::DRAFT => $this->sectionWasReturnedToReviewer($assessment, $sectionLabel)
                    ? 'Returned to Reviewer'
                    : ($isSubmitted
                        ? 'Section submitted'
                        : ($started ? 'In Progress' : 'Not Started')),
                default => AssessmentWorkflowStatus::label($this->sectionStatus($assessment, $sectionLabel)),
            };
        }

        if ($isSubmitted) {
            return match (AssessmentWorkflowStatus::normalize($assessment->workflowStatus())) {
                AssessmentWorkflowStatus::COMPLETED => 'Completed',
                AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION => 'For Employee confirmation',
                AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL => 'For Reviewer approval',
                AssessmentWorkflowStatus::DRAFT => 'Section submitted',
                default => AssessmentWorkflowStatus::label($assessment->workflowStatus()),
            };
        }

        return $started ? 'In Progress' : 'Not Started';
    }

    /**
     * @return array{reviewer_comments: string, employee_comments: string}
     */
    public function resolveSectionComments(EmployeeCompetencyAssessment $assessment, string $sectionLabel): array
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $sectionComments = $snapshot['section_comments'][$sectionLabel] ?? null;
        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);

        $reviewerComments = is_array($sectionComments)
            ? (string) ($sectionComments['reviewer_comments'] ?? '')
            : '';

        $employeeComments = is_array($sectionComments)
            ? (string) ($sectionComments['employee_comments'] ?? '')
            : '';

        if ($employeeComments === '' && $workflow !== []) {
            $employeeComments = (string) ($workflow['employee_comments'] ?? '');
        }

        return [
            'reviewer_comments' => $reviewerComments,
            'employee_comments' => $employeeComments,
        ];
    }

    /**
     * @param  list<int>  $sectionItemIds
     */
    public function clearSectionProgress(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        array $sectionItemIds,
        ?int $revokedBy = null,
    ): void {
        if (! $this->sectionCanBeCleared(
            $assessment,
            $sectionLabel,
            $this->resolveSectionHasRatedItems($assessment, $sectionLabel, $sectionItemIds),
        )) {
            return;
        }

        if ($this->sectionWorkflow($assessment, $sectionLabel) !== []) {
            $this->resetSectionSignatures($assessment, $sectionLabel);
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];

        $workflow = is_array($snapshot[self::SNAPSHOT_KEY] ?? null) ? $snapshot[self::SNAPSHOT_KEY] : [];
        unset($workflow[$sectionLabel]);
        $snapshot[self::SNAPSHOT_KEY] = $workflow;

        unset($snapshot['section_comments'][$sectionLabel], $snapshot['section_summaries'][$sectionLabel]);

        $snapshot['submitted_section_labels'] = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '' && $label !== $sectionLabel)
            ->values()
            ->all();

        $snapshot['excluded_section_labels'] = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn (string $label) => $label !== '' && $label !== $sectionLabel)
            ->values()
            ->all();

        if ($sectionLabel === 'TRACHEOSTOMY CARE') {
            unset($snapshot['tracheostomy_equipment_checks'], $snapshot['tracheostomy_procedure_reviews']);
        }

        $assessment->snapshot_json = $snapshot;

        $responses = is_array($assessment->responses) ? $assessment->responses : [];
        if (is_string($assessment->responses)) {
            $decoded = json_decode($assessment->responses, true);
            $responses = is_array($decoded) ? $decoded : [];
        }

        foreach ($sectionItemIds as $itemId) {
            unset($responses[(int) $itemId], $responses[(string) $itemId]);
        }

        $assessment->responses = $responses;

        if ($sectionItemIds !== [] && $assessment->exists) {
            EmployeeAssessmentItemEntry::query()
                ->where('employee_num', $assessment->employee_num)
                ->where('assessment_period_id', $assessment->assessment_period_id)
                ->where('assessment_type', 'competency')
                ->whereIn('source_item_id', $sectionItemIds)
                ->whereNull('revoked_at')
                ->update([
                    'revoked_at' => now(),
                    'revoked_by' => $revokedBy,
                ]);
        }

        app(CompetencyAssessmentPdfStorage::class)->deletePdfFile(
            app(CompetencyAssessmentPdfStorage::class)->sectionPdfPath($assessment, $sectionLabel)
        );

        $this->syncAggregateAssessmentStatus($assessment);
    }

    public function sectionCanBeCleared(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        ?bool $hasRatedItems = null,
    ): bool {
        return $this->sectionDisplayStatusLabel(
            $assessment,
            $sectionLabel,
            $this->sectionIsSubmitted($assessment, $sectionLabel),
            $hasRatedItems,
        ) === 'In Progress';
    }

    protected function sectionHasStarted(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        bool $isSubmitted,
        ?bool $hasRatedItems,
    ): bool {
        if ($isSubmitted) {
            return true;
        }

        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);
        if ($workflow !== []) {
            $status = $this->sectionStatus($assessment, $sectionLabel);
            if ($status !== AssessmentWorkflowStatus::DRAFT) {
                return true;
            }

            if (filled($workflow['returned_at'] ?? null) || filled($workflow['submitted_at'] ?? null)) {
                return true;
            }
        }

        if ($hasRatedItems !== null) {
            return $hasRatedItems;
        }

        return $this->sectionHasRatedItemEntries($assessment, $sectionLabel);
    }

    protected function sectionHasRatedItemEntries(EmployeeCompetencyAssessment $assessment, string $sectionLabel): bool
    {
        if (! $assessment->exists) {
            return false;
        }

        $itemIds = \App\Models\EmployeeCompetencyItem::query()
            ->where('section', $sectionLabel)
            ->pluck('id');

        if ($itemIds->isEmpty()) {
            return false;
        }

        return \App\Models\EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $assessment->employee_num)
            ->where('assessment_period_id', $assessment->assessment_period_id)
            ->where('assessment_type', 'competency')
            ->whereIn('source_item_id', $itemIds)
            ->whereNotNull('rating')
            ->where('rating', '!=', '')
            ->exists();
    }

    /**
     * @param  list<int>  $sectionItemIds
     */
    protected function resolveSectionHasRatedItems(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        array $sectionItemIds = [],
    ): bool {
        $responses = is_array($assessment->responses) ? $assessment->responses : [];
        if (is_string($assessment->responses)) {
            $decoded = json_decode($assessment->responses, true);
            $responses = is_array($decoded) ? $decoded : [];
        }

        if ($responses !== []) {
            $itemIds = $sectionItemIds !== []
                ? collect($sectionItemIds)
                : ($assessment->exists
                    ? \App\Models\EmployeeCompetencyItem::query()
                        ->where('section', $sectionLabel)
                        ->pluck('id')
                    : collect());

            foreach ($itemIds as $itemId) {
                $response = $responses[(int) $itemId] ?? $responses[(string) $itemId] ?? null;
                if (is_array($response)) {
                    if (filled($response['response'] ?? $response['rating'] ?? null)) {
                        return true;
                    }

                    continue;
                }

                if (filled($response)) {
                    return true;
                }
            }
        }

        return $this->sectionHasRatedItemEntries($assessment, $sectionLabel);
    }

    public function sectionIsExcluded(EmployeeCompetencyAssessment $assessment, string $sectionLabel): bool
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $excluded = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label));

        return $excluded->contains($sectionLabel);
    }

    public function sectionIsSubmitted(EmployeeCompetencyAssessment $assessment, string $sectionLabel): bool
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submitted = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label));

        return $submitted->contains($sectionLabel);
    }

    /**
     * @return list<string>
     */
    public function sectionsAwaitingEmployeeConfirmation(EmployeeCompetencyAssessment $assessment): array
    {
        return $this->collectSectionsByStatus($assessment, AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION);
    }

    /**
     * @return list<string>
     */
    public function sectionsAwaitingReviewerApproval(EmployeeCompetencyAssessment $assessment): array
    {
        return $this->collectSectionsByStatus($assessment, AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL);
    }

    /**
     * @return list<array{section:string, assessment:EmployeeCompetencyAssessment}>
     */
    public function pendingEmployeeConfirmationItems(Collection $assessments): array
    {
        $items = [];

        foreach ($assessments as $assessment) {
            foreach ($this->sectionsAwaitingEmployeeConfirmation($assessment) as $sectionLabel) {
                $items[] = [
                    'section' => $sectionLabel,
                    'assessment' => $assessment,
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<array{section:string, assessment:EmployeeCompetencyAssessment}>
     */
    public function pendingReviewerApprovalItems(Collection $assessments, int $reviewerUserId): array
    {
        $items = [];

        foreach ($assessments as $assessment) {
            if ((int) $assessment->submitted_by !== $reviewerUserId) {
                continue;
            }

            foreach ($this->sectionsAwaitingReviewerApproval($assessment) as $sectionLabel) {
                $items[] = [
                    'section' => $sectionLabel,
                    'assessment' => $assessment,
                ];
            }
        }

        return $items;
    }

    public function submitSectionForEmployeeConfirmation(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        ?int $submittedBy = null,
    ): bool {
        if ($this->sectionIsExcluded($assessment, $sectionLabel)) {
            return false;
        }

        if (! $this->sectionIsSubmitted($assessment, $sectionLabel)) {
            return false;
        }

        $current = $this->sectionStatus($assessment, $sectionLabel);
        $wasResubmit = in_array($current, [
            AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
        ], true);

        $this->resetSectionSignatures($assessment, $sectionLabel);

        $this->writeSectionWorkflow($assessment, $sectionLabel, [
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'submitted_at' => now()->toDateTimeString(),
            'submitted_by' => $submittedBy ?? Auth::id(),
            'employee_signed_at' => null,
            'employee_signature_path' => null,
            'employee_comments' => (string) ($this->sectionComments($assessment, $sectionLabel)['employee_comments'] ?? ''),
            'reviewer_signed_at' => null,
            'reviewer_signature_path' => null,
            'employee_confirmation_snapshot' => null,
        ]);

        if (! $assessment->submitted_at) {
            $assessment->submitted_at = now();
        }

        if (! $assessment->submitted_by) {
            $assessment->submitted_by = $submittedBy ?? Auth::id();
        }

        $this->syncAggregateAssessmentStatus($assessment);
        $assessment->save();

        return $wasResubmit;
    }

    public function employeeAcknowledgeSection(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        string $employeeSignaturePath,
        ?string $employeeComments = null,
    ): void {
        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);
        $workflow['employee_signature_path'] = $employeeSignaturePath;
        $workflow['employee_signed_at'] = now()->toDateTimeString();
        $workflow['employee_comments'] = $employeeComments ?? (string) ($workflow['employee_comments'] ?? '');
        $workflow['status'] = AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL;

        // Persist comments before fingerprinting so the snapshot matches what
        // sectionHasChangedSinceEmployeeConfirmation will compare against later.
        $this->updateSectionComments($assessment, $sectionLabel, employeeComments: $workflow['employee_comments']);
        $workflow['employee_confirmation_snapshot'] = $this->buildSectionSnapshotFingerprint($assessment, $sectionLabel);

        $this->writeSectionWorkflow($assessment, $sectionLabel, $workflow);
        $this->syncAggregateAssessmentStatus($assessment);
        $assessment->save();
    }

    public function employeeSendBackSection(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        ?string $employeeComments = null,
    ): void {
        $this->resetSectionSignatures($assessment, $sectionLabel);

        $existing = $this->sectionWorkflow($assessment, $sectionLabel);
        $comments = $employeeComments ?? (string) ($existing['employee_comments'] ?? '');

        $this->writeSectionWorkflow($assessment, $sectionLabel, [
            'status' => AssessmentWorkflowStatus::DRAFT,
            'returned_at' => now()->toDateTimeString(),
            'reviewer_updated_after_return_at' => null,
            'employee_comments' => $comments,
            'employee_signed_at' => null,
            'employee_signature_path' => null,
            'employee_confirmation_snapshot' => null,
        ]);

        $this->updateSectionComments($assessment, $sectionLabel, employeeComments: $comments);
        $this->syncAggregateAssessmentStatus($assessment);
        $assessment->save();
    }

    public function reviewerApproveSection(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        string $reviewerSignaturePath,
    ): void {
        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);
        $workflow['reviewer_signature_path'] = $reviewerSignaturePath;
        $workflow['reviewer_signed_at'] = now()->toDateTimeString();
        $workflow['status'] = AssessmentWorkflowStatus::COMPLETED;

        $this->writeSectionWorkflow($assessment, $sectionLabel, $workflow);
        $this->syncAggregateAssessmentStatus($assessment);
        $assessment->save();
    }

    public function reviewerResubmitSection(EmployeeCompetencyAssessment $assessment, string $sectionLabel): bool
    {
        return $this->submitSectionForEmployeeConfirmation($assessment, $sectionLabel);
    }

    public function reviewerCanResubmitReturnedSection(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): bool {
        if (! $this->sectionWasReturnedToReviewer($assessment, $sectionLabel)) {
            return true;
        }

        return filled($this->sectionWorkflow($assessment, $sectionLabel)['reviewer_updated_after_return_at'] ?? null);
    }

    public function recordReviewerUpdateAfterReturn(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): void {
        if (! $this->sectionWasReturnedToReviewer($assessment, $sectionLabel)) {
            return;
        }

        $this->writeSectionWorkflow($assessment, $sectionLabel, [
            'reviewer_updated_after_return_at' => now()->toDateTimeString(),
        ]);
        $assessment->save();
    }

    public function sectionHasChangedSinceEmployeeConfirmation(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): bool {
        if ($this->sectionStatus($assessment, $sectionLabel) !== AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL) {
            return false;
        }

        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);
        $stored = $workflow['employee_confirmation_snapshot'] ?? null;

        if (! is_string($stored) || $stored === '') {
            return false;
        }

        $current = $this->buildSectionSnapshotFingerprint($assessment, $sectionLabel);
        if ($stored === $current) {
            return false;
        }

        // Heal legacy / ordering mismatches when ratings have not actually changed
        // after the employee signed — otherwise Complete Section stays hidden.
        $signedAt = $workflow['employee_signed_at'] ?? null;
        if (filled($signedAt) && ! $this->sectionRatingsChangedAfter($assessment, $signedAt)) {
            $this->writeSectionWorkflow($assessment, $sectionLabel, [
                'employee_confirmation_snapshot' => $current,
            ]);
            $assessment->saveQuietly();

            return false;
        }

        return true;
    }

    public function syncSubmittedSectionsWithoutWorkflow(EmployeeCompetencyAssessment $assessment): void
    {
        if (AssessmentWorkflowStatus::isCompleted($assessment->workflowStatus())) {
            return;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submitted = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter();

        foreach ($submitted as $sectionLabel) {
            if ($this->sectionIsExcluded($assessment, $sectionLabel)) {
                continue;
            }

            if ($this->sectionWorkflow($assessment, $sectionLabel) !== []) {
                continue;
            }

            $this->submitSectionForEmployeeConfirmation($assessment, $sectionLabel, $assessment->submitted_by);
        }

        $assessment->refresh();
    }

    public function syncAggregateAssessmentStatus(EmployeeCompetencyAssessment $assessment): void
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submitted = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter();
        $excluded = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter();

        $tracked = $submitted->merge($excluded)->unique()->values();

        if ($tracked->isEmpty()) {
            $assessment->status = AssessmentWorkflowStatus::DRAFT;

            return;
        }

        $statuses = $tracked
            ->map(fn (string $label) => $this->sectionIsExcluded($assessment, $label)
                ? AssessmentWorkflowStatus::COMPLETED
                : $this->sectionStatus($assessment, $label));

        if ($statuses->every(fn (string $status) => $status === AssessmentWorkflowStatus::COMPLETED)) {
            $assessment->status = AssessmentWorkflowStatus::COMPLETED;
            $assessment->completed_at ??= now();

            return;
        }

        if ($statuses->contains(AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION)) {
            $assessment->status = AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;

            return;
        }

        if ($statuses->contains(AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL)) {
            $assessment->status = AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL;

            return;
        }

        $assessment->status = AssessmentWorkflowStatus::DRAFT;
        $assessment->completed_at = null;
    }

    public function buildSectionChecklistUrl(
        BPEmployee $employee,
        int $assessmentPeriodId,
        string $sectionLabel,
    ): string {
        $notificationService = app(AssessmentConfirmationNotificationService::class);
        $base = $notificationService->buildEmployeeChecklistUrl($employee, 'partG', $assessmentPeriodId);

        return $base.(str_contains($base, '?') ? '&' : '?').'checklist_section='.urlencode($sectionLabel);
    }

    public function accordionKeyForSectionLabel(string $sectionLabel): ?string
    {
        $normalized = trim($sectionLabel);

        return self::SECTION_ACCORDION_KEYS[$normalized] ?? null;
    }

    public function buildReviewerSectionChecklistUrl(
        BPEmployee $employee,
        int $assessmentPeriodId,
        string $sectionLabel,
    ): string {
        $notificationService = app(AssessmentConfirmationNotificationService::class);
        $base = $notificationService->buildReviewerChecklistUrl($employee, 'partG', $assessmentPeriodId);

        return $base.(str_contains($base, '?') ? '&' : '?').'checklist_section='.urlencode($sectionLabel);
    }

    /**
     * @return list<string>
     */
    public function sectionsReturnedToReviewer(EmployeeCompetencyAssessment $assessment): array
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $workflow = is_array($snapshot[self::SNAPSHOT_KEY] ?? null) ? $snapshot[self::SNAPSHOT_KEY] : [];
        $sections = [];

        foreach ($workflow as $sectionLabel => $state) {
            if (! is_array($state)) {
                continue;
            }

            $label = (string) $sectionLabel;

            if ($this->sectionIsExcluded($assessment, $label)) {
                continue;
            }

            if (AssessmentWorkflowStatus::normalize((string) ($state['status'] ?? '')) !== AssessmentWorkflowStatus::DRAFT) {
                continue;
            }

            if (blank($state['returned_at'] ?? null)) {
                continue;
            }

            if (! $this->sectionIsSubmitted($assessment, $label)) {
                continue;
            }

            $sections[] = $label;
        }

        return $sections;
    }

    /**
     * @return list<array{section:string, assessment:EmployeeCompetencyAssessment}>
     */
    public function sectionsReturnedToReviewerItems(Collection $assessments, int $reviewerUserId): array
    {
        $items = [];

        foreach ($assessments as $assessment) {
            if ((int) $assessment->submitted_by !== $reviewerUserId) {
                continue;
            }

            foreach ($this->sectionsReturnedToReviewer($assessment) as $sectionLabel) {
                $items[] = [
                    'section' => $sectionLabel,
                    'assessment' => $assessment,
                ];
            }
        }

        return $items;
    }

    public function sectionWasReturnedToReviewer(EmployeeCompetencyAssessment $assessment, string $sectionLabel): bool
    {
        if ($this->sectionStatus($assessment, $sectionLabel) !== AssessmentWorkflowStatus::DRAFT) {
            return false;
        }

        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);

        return filled($workflow['returned_at'] ?? null);
    }

    public function sectionEmployeeSignaturePath(EmployeeCompetencyAssessment $assessment, string $sectionLabel): ?string
    {
        $path = (string) ($this->sectionWorkflow($assessment, $sectionLabel)['employee_signature_path'] ?? '');

        return $path !== '' ? $path : null;
    }

    public function sectionReviewerSignaturePath(EmployeeCompetencyAssessment $assessment, string $sectionLabel): ?string
    {
        $path = (string) ($this->sectionWorkflow($assessment, $sectionLabel)['reviewer_signature_path'] ?? '');

        return $path !== '' ? $path : null;
    }

    /**
     * Resolve section-scoped signature assets for compact section PDFs.
     * Prevents assessment-level signatures from prior competencies bleeding into
     * sections awaiting employee confirmation.
     *
     * @return array{
     *     employee_signature_path: ?string,
     *     reviewer_signature_path: ?string,
     *     employee_signed_at: ?string,
     *     reviewer_signed_at: ?string
     * }
     */
    public function sectionPdfSignatureFields(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): array {
        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);

        if ($workflow !== []) {
            $status = $this->sectionStatus($assessment, $sectionLabel);

            return match ($status) {
                AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                AssessmentWorkflowStatus::DRAFT => [
                    'employee_signature_path' => null,
                    'reviewer_signature_path' => null,
                    'employee_signed_at' => null,
                    'reviewer_signed_at' => null,
                ],
                AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL => [
                    'employee_signature_path' => $this->sectionEmployeeSignaturePath($assessment, $sectionLabel),
                    'reviewer_signature_path' => null,
                    'employee_signed_at' => filled($workflow['employee_signed_at'] ?? null)
                        ? (string) $workflow['employee_signed_at']
                        : null,
                    'reviewer_signed_at' => null,
                ],
                AssessmentWorkflowStatus::COMPLETED => [
                    'employee_signature_path' => $this->sectionEmployeeSignaturePath($assessment, $sectionLabel),
                    'reviewer_signature_path' => $this->sectionReviewerSignaturePath($assessment, $sectionLabel),
                    'employee_signed_at' => filled($workflow['employee_signed_at'] ?? null)
                        ? (string) $workflow['employee_signed_at']
                        : null,
                    'reviewer_signed_at' => filled($workflow['reviewer_signed_at'] ?? null)
                        ? (string) $workflow['reviewer_signed_at']
                        : null,
                ],
                default => [
                    'employee_signature_path' => null,
                    'reviewer_signature_path' => null,
                    'employee_signed_at' => null,
                    'reviewer_signed_at' => null,
                ],
            };
        }

        return [
            'employee_signature_path' => filled($assessment->employee_signature_path)
                ? (string) $assessment->employee_signature_path
                : null,
            'reviewer_signature_path' => filled($assessment->reviewer_signature_path)
                ? (string) $assessment->reviewer_signature_path
                : null,
            'employee_signed_at' => optional($assessment->employee_signed_at)->toDateTimeString(),
            'reviewer_signed_at' => optional($assessment->reviewer_signed_at)->toDateTimeString()
                ?? optional($assessment->review_date)->toDateString(),
        ];
    }

  protected function collectSectionsByStatus(EmployeeCompetencyAssessment $assessment, string $status): array
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $workflow = is_array($snapshot[self::SNAPSHOT_KEY] ?? null) ? $snapshot[self::SNAPSHOT_KEY] : [];
        $sections = [];

        foreach ($workflow as $sectionLabel => $state) {
            if (! is_array($state)) {
                continue;
            }

            if ($this->sectionIsExcluded($assessment, (string) $sectionLabel)) {
                continue;
            }

            if (AssessmentWorkflowStatus::normalize((string) ($state['status'] ?? '')) === $status) {
                $sections[] = (string) $sectionLabel;
            }
        }

        return $sections;
    }

    protected function syncLegacySectionIfNeeded(EmployeeCompetencyAssessment $assessment, string $sectionLabel): void
    {
        // Legacy migration handled by syncSubmittedSectionsWithoutWorkflow().
    }

    /**
     * @param  array<string, mixed>  $values
     */
    protected function writeSectionWorkflow(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        array $values,
    ): void {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $workflow = is_array($snapshot[self::SNAPSHOT_KEY] ?? null) ? $snapshot[self::SNAPSHOT_KEY] : [];
        $existing = is_array($workflow[$sectionLabel] ?? null) ? $workflow[$sectionLabel] : [];

        $workflow[$sectionLabel] = array_merge($existing, $values);
        $snapshot[self::SNAPSHOT_KEY] = $workflow;
        $assessment->snapshot_json = $snapshot;
    }

    protected function resetSectionSignatures(EmployeeCompetencyAssessment $assessment, string $sectionLabel): void
    {
        $workflow = $this->sectionWorkflow($assessment, $sectionLabel);

        foreach (['employee_signature_path', 'reviewer_signature_path'] as $pathKey) {
            $path = (string) ($workflow[$pathKey] ?? '');
            if ($path !== '' && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * @return array{reviewer_comments?:string,employee_comments?:string}
     */
    protected function sectionComments(EmployeeCompetencyAssessment $assessment, string $sectionLabel): array
    {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $comments = $snapshot['section_comments'][$sectionLabel] ?? [];

        return is_array($comments) ? $comments : [];
    }

    protected function updateSectionComments(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
        ?string $employeeComments = null,
    ): void {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $snapshot['section_comments'] ??= [];
        $snapshot['section_comments'][$sectionLabel] ??= [];
        if ($employeeComments !== null) {
            $snapshot['section_comments'][$sectionLabel]['employee_comments'] = $employeeComments;
        }
        $assessment->snapshot_json = $snapshot;
    }

    protected function buildSectionSnapshotFingerprint(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): string {
        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $summary = is_array($snapshot['section_summaries'][$sectionLabel] ?? null)
            ? $snapshot['section_summaries'][$sectionLabel]
            : [];
        $comments = is_array($snapshot['section_comments'][$sectionLabel] ?? null)
            ? $snapshot['section_comments'][$sectionLabel]
            : [];

        // Only fingerprint employee-confirmed competency content. Volatile fields
        // (reviewer comments, review_date, submitted_at) must not hide Approve.
        $payload = [
            'summary' => [
                'total_score' => $this->normalizeFingerprintNumber($summary['total_score'] ?? null),
                'average_score' => $this->normalizeFingerprintNumber($summary['average_score'] ?? null),
                'overall_rating' => (string) ($summary['overall_rating'] ?? ''),
            ],
            'employee_comments' => trim((string) ($comments['employee_comments'] ?? '')),
        ];

        return hash('sha256', json_encode($payload));
    }

    protected function normalizeFingerprintNumber(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return (string) $value;
        }

        return number_format((float) $value, 4, '.', '');
    }

    protected function sectionRatingsChangedAfter(
        EmployeeCompetencyAssessment $assessment,
        string $signedAt,
    ): bool {
        try {
            $signed = Carbon::parse($signedAt);
        } catch (\Throwable) {
            return true;
        }

        try {
            return EmployeeAssessmentItemEntry::query()
                ->where('employee_num', $assessment->employee_num)
                ->where('assessment_period_id', $assessment->assessment_period_id)
                ->where('assessment_type', 'competency')
                ->whereNull('revoked_at')
                ->where('created_at', '>', $signed)
                ->exists();
        } catch (\Throwable) {
            // Without a usable DB connection, do not heal mismatched fingerprints.
            return true;
        }
    }
}
