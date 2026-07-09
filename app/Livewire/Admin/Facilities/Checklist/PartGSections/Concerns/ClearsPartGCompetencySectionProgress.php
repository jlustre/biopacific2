<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Models\EmployeeCompetencyAssessment;
use App\Services\CompetencySectionWorkflowService;
use Illuminate\Support\Facades\Auth;

trait ClearsPartGCompetencySectionProgress
{
    public function canClearSectionProgress(): bool
    {
        if ($this->evaluatorActionsDisabled ?? false) {
            return false;
        }

        if (! ($this->assessmentPeriodId ?? null)) {
            return false;
        }

        if (! defined('static::SECTION')) {
            return false;
        }

        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment) {
            return false;
        }

        return app(CompetencySectionWorkflowService::class)
            ->sectionCanBeCleared(
                $assessment,
                static::SECTION,
                $this->resolveClearSectionHasRatedItems(),
            );
    }

    protected function resolveClearSectionHasRatedItems(): ?bool
    {
        if (! method_exists($this, 'sectionHasAtLeastOneReviewedItem')) {
            return null;
        }

        $sectionItems = method_exists($this, 'resolveCompetencySectionItems')
            ? $this->resolveCompetencySectionItems()
            : (property_exists($this, 'items') && is_array($this->items) ? $this->items : []);

        if ($sectionItems === []) {
            return null;
        }

        return $this->sectionHasAtLeastOneReviewedItem($sectionItems);
    }

    public function clearSectionProgress(): void
    {
        if ($this->denyEvaluatorAction()) {
            return;
        }

        if (! $this->canClearSectionProgress()) {
            $this->notifySectionClearFeedback('error', 'This competency section cannot be cleared right now.');

            return;
        }

        $itemIds = $this->resolveClearableSectionItemIds();
        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if ($assessment) {
            app(CompetencySectionWorkflowService::class)->clearSectionProgress(
                $assessment,
                static::SECTION,
                $itemIds,
                Auth::id(),
            );
            $assessment->save();
        }

        $this->resetLocalSectionProgressState();

        if (method_exists($this, 'refreshPublishedSummaryState')) {
            $this->refreshPublishedSummaryState();
        }

        if (method_exists($this, 'dispatchSectionResponsesUpdated')) {
            $this->dispatchSectionResponsesUpdated();
        }

        if (method_exists($this, 'dispatchPartGSummaryUpdated')) {
            $this->dispatchPartGSummaryUpdated();
        }

        $this->notifySectionClearFeedback('success', static::SECTION.' was cleared. You can start this competency over.');
    }

    /**
     * @return list<int>
     */
    protected function resolveClearableSectionItemIds(): array
    {
        if (method_exists($this, 'scorableItemIds')) {
            return $this->scorableItemIds();
        }

        $items = method_exists($this, 'resolveCompetencySectionItems')
            ? $this->resolveCompetencySectionItems()
            : [];

        return collect($items)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }

    protected function resetLocalSectionProgressState(): void
    {
        if (property_exists($this, 'responses') && is_array($this->responses)) {
            $this->responses = [];
        }

        if (property_exists($this, 'itemReviewMeta') && is_array($this->itemReviewMeta)) {
            $this->itemReviewMeta = [];
        }

        if (property_exists($this, 'summaryComments')) {
            $this->summaryComments = '';
        }

        if (property_exists($this, 'employeeComments')) {
            $this->employeeComments = '';
        }

        if (property_exists($this, 'sectionExcluded')) {
            $this->sectionExcluded = false;
        }

        if (property_exists($this, 'reviewSignDate')) {
            $this->reviewSignDate = '';
        }

        if (property_exists($this, 'employeeSignDate')) {
            $this->employeeSignDate = '';
        }

        if (property_exists($this, 'equipmentChecks') && is_array($this->equipmentChecks)) {
            $this->equipmentChecks = [];
        }

        if (property_exists($this, 'draftSaveMessage')) {
            $this->draftSaveMessage = null;
        }

        if (property_exists($this, 'draftSaveType')) {
            $this->draftSaveType = '';
        }
    }

    protected function notifySectionClearFeedback(string $type, string $message): void
    {
        if (method_exists($this, 'setDraftSaveFeedback')) {
            $this->setDraftSaveFeedback($type, $message);

            return;
        }

        if (property_exists($this, 'draftSaveMessage')) {
            $this->draftSaveMessage = $message;
        }

        if (property_exists($this, 'draftSaveType')) {
            $this->draftSaveType = $type;
        }
    }
}
