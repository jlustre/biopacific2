<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Models\EmployeeCompetencyAssessment;
use App\Models\BPEmployee;
use App\Services\AssessmentConfirmationNotificationService;
use App\Services\CompetencyAssessmentConfirmationService;
use App\Support\AssessmentWorkflowStatus;
use App\Support\PartGCompetencyScoring;
use Illuminate\Support\Facades\Auth;

trait PersistsPartGCompetencySectionResponses
{
    public function getSectionLabelProperty(): string
    {
        return static::SECTION;
    }

    protected function setDraftSaveFeedback(string $type, string $message): void
    {
        $this->draftSaveType = $type;
        $this->draftSaveMessage = $message;
    }

    protected function guardPartGSectionSubmit(): bool
    {
        if ($this->sectionItemReviewsLocked()) {
            $this->setDraftSaveFeedback('error', 'This assessment is read-only and cannot be submitted.');

            return false;
        }

        if (! $this->assessmentPeriodId) {
            $this->setDraftSaveFeedback('error', 'Please select an assessment period before submitting this section.');

            return false;
        }

        if ($this->denyEvaluatorAction()) {
            return false;
        }

        return true;
    }

    public function submitAssessment(): void
    {
        $this->draftSaveMessage = null;
        $this->draftSaveType = '';

        if (! $this->guardPartGSectionSubmit()) {
            return;
        }

        if (! $this->validatePartGSectionRatingsBeforeSubmit()) {
            return;
        }

        try {
            $this->persistResponses('section_submit');
            $this->setDraftSaveFeedback('success', static::SECTION.' submitted successfully.');
        } catch (\Throwable $e) {
            report($e);
            $this->setDraftSaveFeedback('error', 'Failed to submit this section. Please try again.');
        }
    }

    protected function validatePartGSectionRatingsBeforeSubmit(): bool
    {
        $rules = [
            'responses' => 'required|array',
        ];

        if (method_exists($this, 'reviewerSignDateRequiredOnSubmit')
            && $this->reviewerSignDateRequiredOnSubmit()) {
            $rules['reviewSignDate'] = 'required|date';
        }

        $this->validate($rules);

        if ($this->sectionExcluded ?? false) {
            return true;
        }

        if (! method_exists($this, 'scorableItemIds')) {
            return true;
        }

        foreach ($this->scorableItemIds() as $itemId) {
            $response = $this->responses[$itemId] ?? null;
            if (! PartGCompetencyScoring::isValidItemRating($response)) {
                $this->addError('responses', 'Please rate all competency items before submitting.');

                return false;
            }
        }

        return true;
    }

    protected function persistDraftIfPossible(): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        if ($this->sectionItemReviewsLocked() && $this->reviewerSummaryCommentsLocked()) {
            return;
        }

        $this->persistResponses('draft');
    }

    /**
     * @param  'draft'|'section_submit'  $intent
     */
    protected function persistResponses(string $intent): void
    {
        if ($this->abortPersistIfSelfAssessment()) {
            return;
        }

        if (method_exists($this, 'refreshReviewerIdentityForPersist')) {
            $this->refreshReviewerIdentityForPersist();
        }

        $row = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        $existing = $this->decodeResponses($row?->responses);
        $payload = $this->mergeSectionRatingsIntoResponses($existing);

        $updateData = [
            'responses' => $payload,
            'reviewer_name' => $this->reviewerName,
            'reviewer_title' => $this->reviewerTitle,
            'employee_name' => $this->employeeName,
            'employee_title' => $this->employeeTitle,
            'submitted_by' => Auth::id(),
        ];

        $updateData = $this->applySectionScopedFormFields($updateData, $row);
        $updateData = $this->upsertSectionSummarySnapshot($updateData, $row, $intent === 'section_submit');
        $updateData = $this->withExcludedSnapshot($updateData, $row);

        if ($intent === 'section_submit') {
            $updateData = $this->withSectionSubmissionSnapshot($updateData, $row);
        } else {
            $updateData['status'] = $this->resolveAssessmentStatusForDraft($row);
            $updateData['submitted_at'] = $row?->submitted_at;
        }

        $row = EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            $updateData
        );

        $this->syncCompetencyItemEntriesFromResponses($payload);

        if ($intent === 'section_submit') {
            $this->finalizeIndependentSectionSubmission($row->fresh());
        } else {
            $this->maybeResetCompetencyAssessmentForEmployeeReconfirmation();

            if ($intent === 'draft') {
                $this->finalizeReturnedSectionDraftSave($row->fresh());
            }
        }

        $this->dispatchPartGSummaryUpdated();
    }

    protected function finalizeIndependentSectionSubmission(?EmployeeCompetencyAssessment $assessment): void
    {
        if (! $assessment || ! defined('static::SECTION')) {
            return;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);
        $wasResubmit = $sectionWorkflow->submitSectionForEmployeeConfirmation(
            $assessment,
            static::SECTION,
            Auth::id(),
        );

        $employee = BPEmployee::query()
            ->where('employee_num', $this->employeeNum)
            ->first();

        if (! $employee) {
            return;
        }

        $notificationService = app(AssessmentConfirmationNotificationService::class);
        if ($wasResubmit) {
            $notificationService->notifyCompetencySectionResubmittedToEmployee($assessment->fresh(), $employee, static::SECTION);
        } else {
            $notificationService->notifyCompetencySectionSubmittedToEmployee($assessment->fresh(), $employee, static::SECTION);
        }

        $this->regenerateCompetencySectionPdf($assessment->fresh(), static::SECTION);
    }

    protected function regenerateCompetencySectionPdf(
        EmployeeCompetencyAssessment $assessment,
        string $sectionLabel,
    ): void {
        try {
            app(\App\Http\Controllers\EmployeePerformanceAssessmentController::class)
                ->persistCompetencySectionPdf($assessment, $sectionLabel);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    protected function maybeResetCompetencyAssessmentForEmployeeReconfirmation(): void
    {
        if (! defined('static::SECTION')) {
            return;
        }

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if (! $assessment) {
            return;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        if (! $sectionWorkflow->sectionHasChangedSinceEmployeeConfirmation($assessment, static::SECTION)) {
            return;
        }

        $wasResubmit = $sectionWorkflow->reviewerResubmitSection($assessment, static::SECTION);

        $employee = BPEmployee::query()
            ->where('employee_num', $this->employeeNum)
            ->first();

        if (! $employee) {
            return;
        }

        $notificationService = app(AssessmentConfirmationNotificationService::class);
        if ($wasResubmit) {
            $notificationService->notifyCompetencySectionResubmittedToEmployee($assessment->fresh(), $employee, static::SECTION);
        }
    }

    /**
     * @param  array<int|string, mixed>  $existing
     * @return array<int|string, mixed>
     */
    protected function mergeSectionRatingsIntoResponses(array $existing): array
    {
        $payload = $existing;

        if (method_exists($this, 'mergeItemReviewMetaIntoResponsesPayload')) {
            return $this->mergeItemReviewMetaIntoResponsesPayload($payload);
        }

        if (property_exists($this, 'responses') && is_array($this->responses)) {
            foreach ($this->responses as $itemId => $response) {
                if ($response === null || $response === '') {
                    continue;
                }

                $payload[(int) $itemId] = ['response' => $response];
            }
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function applySectionScopedFormFields(array $updateData, ?EmployeeCompetencyAssessment $row): array
    {
        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        if (isset($updateData['snapshot_json']) && is_array($updateData['snapshot_json'])) {
            $snapshot = $updateData['snapshot_json'];
        }

        $snapshot['section_comments'] ??= [];

        $existingComments = $row
            ? app(\App\Services\CompetencySectionWorkflowService::class)
                ->resolveSectionComments($row, static::SECTION)
            : ['reviewer_comments' => '', 'employee_comments' => ''];

        $employeeComments = (string) ($existingComments['employee_comments'] ?? '');

        $snapshot['section_comments'][static::SECTION] = [
            'reviewer_comments' => $this->summaryComments,
            'employee_comments' => $employeeComments,
            'review_date' => optional($row?->reviewer_signed_at ?? $row?->review_date)->format('Y-m-d'),
            'employee_signed_at' => optional($row?->employee_signed_at)->format('Y-m-d'),
        ];

        $updateData['snapshot_json'] = $snapshot;

        return $updateData;
    }

    /**
     * Keep per-section summary metrics current for history/PDF,
     * even during draft/review updates after initial section submit.
     *
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function upsertSectionSummarySnapshot(
        array $updateData,
        ?EmployeeCompetencyAssessment $row,
        bool $markSubmittedAt = false,
    ): array {
        $snapshot = is_array($updateData['snapshot_json'] ?? null)
            ? $updateData['snapshot_json']
            : (is_array($row?->snapshot_json) ? $row->snapshot_json : []);

        $scores = $this->calculateScores();
        $existing = is_array($snapshot['section_summaries'][static::SECTION] ?? null)
            ? $snapshot['section_summaries'][static::SECTION]
            : [];

        $snapshot['section_summaries'] ??= [];
        $snapshot['section_summaries'][static::SECTION] = array_merge($existing, [
            'total_score' => $scores['totalPoints'],
            'average_score' => $scores['average'],
            'overall_rating' => $scores['overallRating'],
            'review_date' => optional($row?->reviewer_signed_at ?? $row?->review_date)->format('Y-m-d'),
        ]);

        if ($markSubmittedAt || empty($snapshot['section_summaries'][static::SECTION]['submitted_at'])) {
            $snapshot['section_summaries'][static::SECTION]['submitted_at'] = now()->toDateTimeString();
        }

        $updateData['snapshot_json'] = $snapshot;

        return $updateData;
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    protected function withSectionSubmissionSnapshot(array $updateData, ?EmployeeCompetencyAssessment $row): array
    {
        $snapshot = is_array($updateData['snapshot_json'] ?? null)
            ? $updateData['snapshot_json']
            : (is_array($row?->snapshot_json) ? $row->snapshot_json : []);

        $updateData['snapshot_json'] = $snapshot;
        $updateData = $this->upsertSectionSummarySnapshot($updateData, $row, true);
        $snapshot = is_array($updateData['snapshot_json'] ?? null) ? $updateData['snapshot_json'] : [];

        $submittedLabels = collect($snapshot['submitted_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn ($label) => $label !== '')
            ->values()
            ->all();

        if (! in_array(static::SECTION, $submittedLabels, true)) {
            $submittedLabels[] = static::SECTION;
        }

        $snapshot['submitted_section_labels'] = array_values(array_unique($submittedLabels));
        $updateData['snapshot_json'] = $snapshot;
        $updateData['status'] = $this->resolveAssessmentStatusForDraft($row);
        $updateData['submitted_at'] = $row?->submitted_at;

        return $updateData;
    }

    protected function resolveAssessmentStatusForDraft(?EmployeeCompetencyAssessment $row): string
    {
        $status = (string) ($row?->status ?? 'draft');

        if (in_array($status, [
            AssessmentWorkflowStatus::COMPLETED,
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
        ], true)) {
            return $status;
        }

        return 'draft';
    }

    protected function loadSectionCommentsFromAssessment(?EmployeeCompetencyAssessment $assessment): void
    {
        if (! $assessment) {
            return;
        }

        $comments = app(\App\Services\CompetencySectionWorkflowService::class)
            ->resolveSectionComments($assessment, static::SECTION);

        $this->summaryComments = $comments['reviewer_comments'];
        $this->employeeComments = $comments['employee_comments'];

        $workflow = app(\App\Services\CompetencySectionWorkflowService::class)
            ->sectionWorkflow($assessment, static::SECTION);

        if (property_exists($this, 'reviewSignDate')) {
            $this->reviewSignDate = $this->formatSectionSignDate($workflow['reviewer_signed_at'] ?? null);
        }

        if (property_exists($this, 'employeeSignDate')) {
            $this->employeeSignDate = $this->formatSectionSignDate($workflow['employee_signed_at'] ?? null);
        }
    }

    protected function formatSectionSignDate(mixed $value): string
    {
        if (! filled($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    protected function normalizeResponseKeys(): void
    {
        $normalized = [];

        if (! property_exists($this, 'responses') || ! is_array($this->responses)) {
            return;
        }

        foreach ($this->responses as $itemId => $response) {
            if ($response === null || $response === '') {
                continue;
            }

            $normalized[(int) $itemId] = $response;
        }

        $this->responses = $normalized;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function decodeResponses(mixed $raw): array
    {
        return $this->decodeCompetencyResponses($raw);
    }

    public function sectionIsSubmitted(): bool
    {
        if (! $this->assessmentPeriodId) {
            return false;
        }

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if (! $assessment) {
            return false;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $submittedLabels = $snapshot['submitted_section_labels'] ?? [];

        return in_array(static::SECTION, $submittedLabels, true);
    }
}
