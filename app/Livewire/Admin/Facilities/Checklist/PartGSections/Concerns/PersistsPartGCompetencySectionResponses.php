<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Models\EmployeeCompetencyAssessment;
use App\Models\BPEmployee;
use App\Services\AssessmentConfirmationNotificationService;
use App\Services\CompetencyAssessmentConfirmationService;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Support\Facades\Auth;

trait PersistsPartGCompetencySectionResponses
{
    protected function setDraftSaveFeedback(string $type, string $message): void
    {
        $this->draftSaveType = $type;
        $this->draftSaveMessage = $message;
    }

    protected function guardPartGSectionSubmit(): bool
    {
        if ($this->assessmentLocked) {
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

    protected function persistDraftIfPossible(): void
    {
        if ($this->assessmentLocked || ! $this->assessmentPeriodId) {
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

        EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            $updateData
        );

        $this->syncCompetencyItemEntriesFromResponses($payload);
        $this->maybeResetCompetencyAssessmentForEmployeeReconfirmation();
        $this->dispatchPartGSummaryUpdated();
    }

    protected function maybeResetCompetencyAssessmentForEmployeeReconfirmation(): void
    {
        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if (! $assessment || $assessment->workflowStatus() !== AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL) {
            return;
        }

        $confirmationService = app(CompetencyAssessmentConfirmationService::class);
        $assessment->refresh();

        if (! $confirmationService->hasChangedSinceEmployeeConfirmation($assessment)) {
            return;
        }

        $confirmationService->resetForEmployeeReconfirmation($assessment);
        $assessment->save();

        $employee = BPEmployee::query()
            ->where('employee_num', $this->employeeNum)
            ->first();

        if ($employee) {
            app(AssessmentConfirmationNotificationService::class)
                ->notifyCompetencyAssessmentResubmittedToEmployee($assessment, $employee);
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
        $snapshot['section_comments'][static::SECTION] = [
            'reviewer_comments' => $this->summaryComments,
            'employee_comments' => $this->employeeComments,
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

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $sectionComments = $snapshot['section_comments'][static::SECTION] ?? null;

        if (is_array($sectionComments)) {
            $this->summaryComments = (string) ($sectionComments['reviewer_comments'] ?? $this->summaryComments);
            $this->employeeComments = (string) ($sectionComments['employee_comments'] ?? $this->employeeComments);
        } else {
            $this->summaryComments = (string) ($assessment->comments ?? $this->summaryComments);
            $this->employeeComments = (string) ($assessment->employee_comments ?? $this->employeeComments);
        }

        if (property_exists($this, 'reviewSignDate')) {
            $this->reviewSignDate = optional($assessment->reviewer_signed_at ?? $assessment->review_date)->format('Y-m-d') ?? '';
        }

        if (property_exists($this, 'employeeSignDate')) {
            $this->employeeSignDate = $assessment->employee_signed_at?->format('Y-m-d') ?? '';
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
