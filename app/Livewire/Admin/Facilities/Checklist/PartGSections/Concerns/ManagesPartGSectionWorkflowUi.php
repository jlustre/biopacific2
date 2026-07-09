<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Support\AssessmentWorkflowStatus;
use App\Support\PartGCompetencyScoring;
use Livewire\Attributes\Computed;

trait ManagesPartGSectionWorkflowUi
{
    public bool $returnedSectionResubmitEnabled = false;

    public function updated($property): void
    {
        if (! is_string($property)) {
            return;
        }

        if ($property === 'summaryComments' || $property === 'responses' || str_starts_with($property, 'responses.')) {
            $this->markReturnedSectionResubmitPending();
        }
    }

    public function initializeReturnedSectionResubmitState(): void
    {
        if (! $this->sectionWasReturnedToReviewer()) {
            $this->returnedSectionResubmitEnabled = true;

            return;
        }

        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment || ! defined('static::SECTION')) {
            $this->returnedSectionResubmitEnabled = false;

            return;
        }

        $this->returnedSectionResubmitEnabled = app(\App\Services\CompetencySectionWorkflowService::class)
            ->reviewerCanResubmitReturnedSection($assessment, static::SECTION);
    }

    public function markReturnedSectionResubmitPending(): void
    {
        if ($this->sectionWasReturnedToReviewer()) {
            $this->returnedSectionResubmitEnabled = false;
        }
    }

    public function markReturnedSectionResubmitReady(): void
    {
        $this->returnedSectionResubmitEnabled = true;
    }

    protected function finalizeReturnedSectionDraftSave(?\App\Models\EmployeeCompetencyAssessment $assessment): void
    {
        if (! $assessment || ! defined('static::SECTION')) {
            return;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        if (! $sectionWorkflow->sectionWasReturnedToReviewer($assessment, static::SECTION)) {
            return;
        }

        $sectionWorkflow->recordReviewerUpdateAfterReturn($assessment->fresh(), static::SECTION);
        $this->markReturnedSectionResubmitReady();
    }

    #[Computed]
    public function sectionWorkflowStatus(): string
    {
        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment || ! defined('static::SECTION')) {
            return AssessmentWorkflowStatus::DRAFT;
        }

        return app(\App\Services\CompetencySectionWorkflowService::class)
            ->sectionStatus($assessment, static::SECTION);
    }

    #[Computed]
    public function assessmentWorkflowStatus(): string
    {
        return $this->sectionWorkflowStatus();
    }

    #[Computed]
    public function sectionWorkflowIsCompleted(): bool
    {
        return AssessmentWorkflowStatus::isCompleted($this->sectionWorkflowStatus());
    }

    #[Computed]
    public function assessmentIsCompleted(): bool
    {
        return $this->sectionWorkflowIsCompleted();
    }

    #[Computed]
    public function showDraftSubmitActions(): bool
    {
        if ($this->evaluatorActionsDisabled ?? false) {
            return false;
        }

        if ($this->sectionItemReviewsLocked()) {
            return false;
        }

        if ($this->sectionWasReturnedToReviewer()) {
            return false;
        }

        return $this->sectionWorkflowStatus() === AssessmentWorkflowStatus::DRAFT;
    }

    #[Computed]
    public function sectionWasReturnedToReviewer(): bool
    {
        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment || ! defined('static::SECTION')) {
            return false;
        }

        return app(\App\Services\CompetencySectionWorkflowService::class)
            ->sectionWasReturnedToReviewer($assessment, static::SECTION);
    }

    #[Computed]
    public function summaryFieldsLocked(): bool
    {
        $status = $this->sectionWorkflowStatus();

        return $this->sectionItemReviewsLocked()
            || AssessmentWorkflowStatus::isCompleted($status)
            || AssessmentWorkflowStatus::reviewerCanApprove($status)
            || AssessmentWorkflowStatus::employeeCanConfirm($status);
    }

    /**
     * Reviewer summary comments stay editable while the reviewer can still edit
     * the section (draft or returned for approval), including before section submit.
     */
    #[Computed]
    public function reviewerSummaryCommentsLocked(): bool
    {
        if ($this->evaluatorActionsDisabled ?? false) {
            return true;
        }

        if ($this->sectionExcluded ?? false) {
            return true;
        }

        if (! ($this->assessmentPeriodId ?? null)) {
            return true;
        }

        $status = $this->sectionWorkflowStatus();

        if (AssessmentWorkflowStatus::isCompleted($status)) {
            return true;
        }

        return ! AssessmentWorkflowStatus::reviewerCanEdit($status);
    }

    #[Computed]
    public function storedEmployeeName(): string
    {
        $stored = trim((string) ($this->resolveCompetencyAssessmentForStatus()?->employee_name ?? ''));

        return $stored !== '' ? $stored : (string) ($this->employeeName ?? '');
    }

    #[Computed]
    public function storedReviewerName(): string
    {
        $stored = trim((string) ($this->resolveCompetencyAssessmentForStatus()?->reviewer_name ?? ''));

        return $stored !== '' ? $stored : (string) ($this->reviewerName ?? '');
    }

    #[Computed]
    public function displayReviewSignDate(): string
    {
        $workflow = $this->sectionWorkflowState();

        return $this->formatSectionDisplaySignDate($workflow['reviewer_signed_at'] ?? null);
    }

    #[Computed]
    public function displayEmployeeSignDate(): string
    {
        $workflow = $this->sectionWorkflowState();

        return $this->formatSectionDisplaySignDate($workflow['employee_signed_at'] ?? null);
    }

    /**
     * Reviewer sign dates are captured when the section is completed in acknowledgement,
     * not when the section is first submitted for employee confirmation.
     */
    #[Computed]
    public function reviewerSignDateRequiredOnSubmit(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function sectionWorkflowState(): array
    {
        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment || ! defined('static::SECTION')) {
            return [];
        }

        return app(\App\Services\CompetencySectionWorkflowService::class)
            ->sectionWorkflow($assessment, static::SECTION);
    }

    protected function formatSectionDisplaySignDate(mixed $value): string
    {
        if (! filled($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse((string) $value)->format('M j, Y g:i A');
        } catch (\Throwable) {
            return '';
        }
    }

    public function partGSectionLabel(): string
    {
        return static::SECTION;
    }

    public function partGAccordionKey(): string
    {
        $key = app(\App\Services\CompetencySectionWorkflowService::class)
            ->accordionKeyForSectionLabel(static::SECTION);

        if (! is_string($key) || $key === '') {
            throw new \RuntimeException('No Part G accordion key configured for section: '.static::SECTION);
        }

        return $key;
    }
}
