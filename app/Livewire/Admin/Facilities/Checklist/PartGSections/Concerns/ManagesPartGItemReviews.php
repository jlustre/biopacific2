<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Support\AssessmentWorkflowStatus;
use App\Support\PartGCompetencyScoring;

use Illuminate\Support\Facades\Auth;

trait ManagesPartGItemReviews
{
    /**
     * @return array<int|string, mixed>
     */
    protected function partGResponses(): array
    {
        return property_exists($this, 'responses') && is_array($this->responses)
            ? $this->responses
            : [];
    }

    public bool $reviewModalOpen = false;

    public ?int $reviewModalItemId = null;

    public string $reviewModalRating = '';

    public string $reviewModalComments = '';

    public string $reviewModalDate = '';

    public string $reviewModalReviewerName = '';

    /**
     * @var array<int, array{review_date?: string, reviewer_id?: int|null, reviewer_name?: string, comments?: string|null}>
     */
    public array $itemReviewMeta = [];

    public function openItemReview(int $itemId): void
    {
        if ($this->sectionItemReviewsLocked() || $this->denyEvaluatorAction()) {
            return;
        }

        $this->clearReviewModalValidation();

        $user = Auth::user();
        $this->reviewModalItemId = $itemId;
        $this->reviewModalOpen = true;
        $this->reviewModalDate = now()->toDateString();
        $this->reviewModalReviewerName = $user?->name ?? '';
        $this->reviewModalRating = $this->extractRatingValue($this->partGResponses()[$itemId] ?? null);
        $this->reviewModalComments = (string) ($this->itemReviewMeta[$itemId]['comments'] ?? '');
    }

    public function closeItemReview(): void
    {
        $this->clearReviewModalValidation();
        $this->reviewModalOpen = false;
        $this->reviewModalItemId = null;
        $this->reviewModalRating = '';
        $this->reviewModalComments = '';
    }

    public function updatedReviewModalRating(): void
    {
        if ($this->denyEvaluatorAction()) {
            $itemId = $this->reviewModalItemId;
            $this->reviewModalRating = $itemId
                ? $this->extractRatingValue($this->partGResponses()[$itemId] ?? null)
                : '';

            return;
        }

        $this->resetErrorBag('reviewModalRating');
    }

    public function updatedReviewModalComments(): void
    {
        if (trim($this->reviewModalComments) !== '') {
            $this->resetErrorBag('reviewModalComments');
        }
    }

    public function saveItemReview(): void
    {
        if ($this->sectionItemReviewsLocked() || $this->denyEvaluatorAction()) {
            return;
        }

        if (! $this->reviewModalItemId) {
            return;
        }

        $rating = PartGCompetencyScoring::normalizeItemRating($this->reviewModalRating);
        if ($rating === null) {
            $this->addError('reviewModalRating', 'Please select a rating.');

            return;
        }

        if (PartGCompetencyScoring::isBelowExpectationsItemRating($rating) && trim($this->reviewModalComments) === '') {
            $this->addError('reviewModalComments', 'Comments are required when rating is Below Expectations (B).');

            return;
        }

        $itemId = (int) $this->reviewModalItemId;
        $user = Auth::user();
        $trimmedComments = trim($this->reviewModalComments);

        if (property_exists($this, 'responses') && is_array($this->responses)) {
            $this->responses[$itemId] = $rating;
        }
        $this->itemReviewMeta[$itemId] = [
            'review_date' => $this->reviewModalDate ?: now()->toDateString(),
            'reviewer_id' => $user?->id,
            'reviewer_name' => $user?->name ?? $this->reviewModalReviewerName,
            'comments' => $trimmedComments !== '' ? $trimmedComments : null,
        ];

        if (method_exists($this, 'normalizeResponseKeys')) {
            $this->normalizeResponseKeys();
        }

        if (method_exists($this, 'refreshPublishedSummaryState')) {
            $this->refreshPublishedSummaryState();
        }

        if (method_exists($this, 'dispatchSectionResponsesUpdated')) {
            $this->dispatchSectionResponsesUpdated();
        }

        if (method_exists($this, 'dispatchPartGSummaryUpdated')) {
            $this->dispatchPartGSummaryUpdated();
        }

        if (method_exists($this, 'persistDraftIfPossible')) {
            $this->persistDraftIfPossible();
        } elseif (method_exists($this, 'persistResponses')) {
            $this->persistResponses();
        }

        $this->closeItemReview();
    }

    protected function clearReviewModalValidation(): void
    {
        $this->resetErrorBag('reviewModalRating');
        $this->resetErrorBag('reviewModalComments');
    }

    public function undoItemReview(int $itemId): void
    {
        if ($this->sectionItemReviewsLocked() || $this->denyEvaluatorAction()) {
            return;
        }

        if (property_exists($this, 'responses') && is_array($this->responses)) {
            unset($this->responses[$itemId]);
        }
        unset($this->itemReviewMeta[$itemId]);

        if (method_exists($this, 'normalizeResponseKeys')) {
            $this->normalizeResponseKeys();
        }

        if (method_exists($this, 'refreshPublishedSummaryState')) {
            $this->refreshPublishedSummaryState();
        }

        if (method_exists($this, 'dispatchSectionResponsesUpdated')) {
            $this->dispatchSectionResponsesUpdated();
        }

        if (method_exists($this, 'dispatchPartGSummaryUpdated')) {
            $this->dispatchPartGSummaryUpdated();
        }

        if (method_exists($this, 'persistDraftIfPossible')) {
            $this->persistDraftIfPossible();
        } elseif (method_exists($this, 'persistResponses')) {
            $this->persistResponses();
        }
    }

    protected function normalizePartGReviewItemKey(mixed $itemId): int
    {
        if (method_exists($this, 'normalizeCompetencyResponseKey')) {
            return $this->normalizeCompetencyResponseKey($itemId);
        }

        if (method_exists($this, 'normalizeKey')) {
            return $this->normalizeKey($itemId);
        }

        return (int) $itemId;
    }

    protected function extractRatingValue(mixed $value): string
    {
        $raw = is_array($value)
            ? strtoupper(trim((string) ($value['response'] ?? '')))
            : strtoupper(trim((string) $value));

        return PartGCompetencyScoring::normalizeItemRating($raw) ?? '';
    }

    protected function hydrateItemReviewMetaFromPayload(array $payload): void
    {
        foreach ($payload as $itemId => $data) {
            $sourceItemId = $this->normalizePartGReviewItemKey($itemId);
            if ($sourceItemId <= 0) {
                continue;
            }

            if (! is_array($data)) {
                continue;
            }

            $this->itemReviewMeta[$sourceItemId] = [
                'review_date' => $data['review_date'] ?? null,
                'reviewer_id' => $data['reviewer_id'] ?? null,
                'reviewer_name' => $data['reviewer_name'] ?? null,
                'comments' => $data['comments'] ?? null,
            ];
        }
    }

    protected function mergeItemReviewMetaIntoResponsesPayload(array $payload): array
    {
        foreach ($this->partGResponses() as $itemId => $rating) {
            $rating = $this->extractRatingValue($rating);
            if ($rating === '') {
                continue;
            }

            $meta = $this->itemReviewMeta[(int) $itemId] ?? [];
            $payload[(int) $itemId] = array_filter([
                'response' => PartGCompetencyScoring::normalizeItemRating($rating) ?? $rating,
                'review_date' => $meta['review_date'] ?? now()->toDateString(),
                'reviewer_id' => $meta['reviewer_id'] ?? Auth::id(),
                'reviewer_name' => $meta['reviewer_name'] ?? (Auth::user()?->name ?? ''),
                'comments' => filled($meta['comments'] ?? null) ? $meta['comments'] : null,
            ], fn ($value) => $value !== null && $value !== '');
        }

        return $payload;
    }

    public function itemHasReview(int $itemId): bool
    {
        $rating = $this->extractRatingValue($this->partGResponses()[$itemId] ?? null);

        return PartGCompetencyScoring::isValidItemRating($rating);
    }

    public function itemReviewVisibleToUser(int $itemId): bool
    {
        return $this->itemReviewsVisibleToCurrentUser() && $this->itemHasReview($itemId);
    }

    public function itemReviewsVisibleToCurrentUser(): bool
    {
        if (! ($this->evaluatorActionsDisabled ?? false)) {
            return true;
        }

        return $this->sectionReviewsReleasedToEmployee();
    }

    protected function sectionReviewsReleasedToEmployee(): bool
    {
        if (! defined('static::SECTION')) {
            return false;
        }

        if (! ($this->assessmentPeriodId ?? null)) {
            return false;
        }

        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment) {
            return false;
        }

        $service = app(\App\Services\CompetencySectionWorkflowService::class);
        $label = $service->sectionDisplayStatusLabel(
            $assessment,
            static::SECTION,
            $service->sectionIsSubmitted($assessment, static::SECTION),
        );

        return ! in_array($label, ['In Progress', 'Not Started'], true);
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function buildPartGJsSummaryItem(array $item, array $extra = []): array
    {
        $itemId = (int) ($item['id'] ?? 0);

        return array_merge([
            'id' => $itemId,
            'label' => $item['item'] ?? '',
            'isParent' => (bool) ($item['isParent'] ?? false),
            'indentLevel' => $item['indentLevel'] ?? 0,
            'response' => $this->itemReviewsVisibleToCurrentUser()
                ? ($this->partGResponses()[$itemId] ?? null)
                : null,
        ], $extra);
    }

    /**
     * @param  list<array<string, mixed>>  $sectionItems
     * @return list<array<string, mixed>>
     */
    public function buildPartGJsSummaryItems(array $sectionItems): array
    {
        return collect($sectionItems)
            ->map(fn (array $item) => $this->buildPartGJsSummaryItem($item))
            ->values()
            ->all();
    }

    /**
     * @return array{totalItems: int, checkedOfTotal: string, totalPoints: int, average: string, overallRating: string, pointsOfTotal: string}
     */
    protected function hiddenItemReviewsSummaryMetrics(int $totalItems): array
    {
        return [
            'totalItems' => $totalItems,
            'checkedOfTotal' => '0 of '.$totalItems.' rated',
            'totalPoints' => 0,
            'average' => '—',
            'overallRating' => '—',
            'pointsOfTotal' => PartGCompetencyScoring::pointsOfTotalLabel(0, $totalItems),
        ];
    }

    public function sectionItemReviewsLocked(): bool
    {
        if ($this->sectionExcluded ?? false) {
            return true;
        }

        if (! ($this->assessmentPeriodId ?? null)) {
            return true;
        }

        if ($this->evaluatorActionsDisabled ?? false) {
            return true;
        }

        if (! defined('static::SECTION')) {
            return (bool) ($this->assessmentLocked ?? false);
        }

        $assessment = $this->resolveCompetencyAssessmentForStatus();
        if (! $assessment) {
            return false;
        }

        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);
        if ($sectionWorkflow->sectionWorkflow($assessment, static::SECTION) === []) {
            return false;
        }

        $status = $sectionWorkflow->sectionStatus($assessment, static::SECTION);

        if (AssessmentWorkflowStatus::isCompleted($status)) {
            return true;
        }

        if (AssessmentWorkflowStatus::employeeCanConfirm($status)) {
            return true;
        }

        if (AssessmentWorkflowStatus::reviewerCanApprove($status)) {
            return ! $sectionWorkflow->sectionHasChangedSinceEmployeeConfirmation($assessment, static::SECTION);
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $sectionItems
     */
    public function sectionProgressStatusLabel(array $sectionItems): string
    {
        if ($this->sectionExcluded ?? false) {
            return 'Excluded';
        }

        if (! ($this->assessmentPeriodId ?? null)) {
            return 'Not Started';
        }

        if (defined('static::SECTION')) {
            $assessment = $this->resolveCompetencyAssessmentForStatus();
            if ($assessment) {
                $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);
                $sectionWorkflow->syncSubmittedSectionsWithoutWorkflow($assessment);
                $assessment->refresh();

                return $sectionWorkflow->sectionDisplayStatusLabel(
                    $assessment,
                    static::SECTION,
                    $sectionWorkflow->sectionIsSubmitted($assessment, static::SECTION),
                    $this->sectionHasAtLeastOneReviewedItem($sectionItems),
                );
            }
        }

        return $this->sectionHasAtLeastOneReviewedItem($sectionItems)
            ? 'In Progress'
            : 'Not Started';
    }

    protected function resolveCompetencyAssessmentForStatus(): ?\App\Models\EmployeeCompetencyAssessment
    {
        if (! property_exists($this, 'employeeNum') || ! ($this->assessmentPeriodId ?? null)) {
            return null;
        }

        if (method_exists($this, 'loadAssessment')) {
            return $this->loadAssessment();
        }

        return \App\Models\EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();
    }

    /**
     * @param  list<array<string, mixed>>  $sectionItems
     */
    protected function sectionHasAtLeastOneReviewedItem(array $sectionItems): bool
    {
        foreach ($sectionItems as $item) {
            if ($item['isParent'] ?? false) {
                continue;
            }

            $itemId = (int) ($item['id'] ?? 0);
            if ($itemId > 0 && $this->itemHasReview($itemId)) {
                return true;
            }
        }

        if (property_exists($this, 'procedureCompetencyItems') && is_array($this->procedureCompetencyItems)) {
            foreach ($this->procedureCompetencyItems as $item) {
                $itemId = (int) ($item['id'] ?? 0);
                if ($itemId > 0 && $this->itemHasReview($itemId)) {
                    return true;
                }
            }
        }

        if (property_exists($this, 'equipmentChecks') && is_array($this->equipmentChecks) && $this->equipmentChecks !== []) {
            return true;
        }

        return false;
    }

    public function itemReviewDisplayDate(int $itemId): string
    {
        if (! $this->itemReviewsVisibleToCurrentUser()) {
            return '';
        }

        $meta = $this->itemReviewMeta[$itemId] ?? [];

        return (string) ($meta['review_date'] ?? '');
    }

    public function itemReviewDisplayReviewer(int $itemId): string
    {
        if (! $this->itemReviewsVisibleToCurrentUser()) {
            return '';
        }

        $meta = $this->itemReviewMeta[$itemId] ?? [];

        return (string) ($meta['reviewer_name'] ?? '');
    }

    public function itemReviewDisplayRating(int $itemId): string
    {
        if (! $this->itemReviewsVisibleToCurrentUser()) {
            return '';
        }

        return $this->extractRatingValue($this->partGResponses()[$itemId] ?? null);
    }
}
