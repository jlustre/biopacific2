<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

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
        if ($this->assessmentLocked || ($this->sectionExcluded ?? false) || $this->denyEvaluatorAction()) {
            return;
        }

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
        $this->reviewModalOpen = false;
        $this->reviewModalItemId = null;
        $this->reviewModalRating = '';
        $this->reviewModalComments = '';
    }

    public function saveItemReview(): void
    {
        if ($this->assessmentLocked || ($this->sectionExcluded ?? false) || $this->denyEvaluatorAction()) {
            return;
        }

        if (! $this->reviewModalItemId) {
            return;
        }

        $rating = strtoupper(trim($this->reviewModalRating));
        if (! in_array($rating, ['E', 'S', 'U', 'N'], true)) {
            $this->addError('reviewModalRating', 'Please select a rating.');

            return;
        }

        if ($rating === 'U' && trim($this->reviewModalComments) === '') {
            $this->addError('reviewModalComments', 'Comments are required when rating is Unsatisfactory (U).');

            return;
        }

        $itemId = (int) $this->reviewModalItemId;
        $user = Auth::user();

        if (property_exists($this, 'responses') && is_array($this->responses)) {
            $this->responses[$itemId] = $rating;
        }
        $this->itemReviewMeta[$itemId] = [
            'review_date' => $this->reviewModalDate ?: now()->toDateString(),
            'reviewer_id' => $user?->id,
            'reviewer_name' => $user?->name ?? $this->reviewModalReviewerName,
            'comments' => $rating === 'U' ? trim($this->reviewModalComments) : null,
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

    public function undoItemReview(int $itemId): void
    {
        if ($this->assessmentLocked || ($this->sectionExcluded ?? false) || $this->denyEvaluatorAction()) {
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
        if (is_array($value)) {
            return strtoupper(trim((string) ($value['response'] ?? '')));
        }

        return strtoupper(trim((string) $value));
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
                'response' => $rating,
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

        return in_array($rating, ['E', 'S', 'U', 'N'], true);
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

        return $this->sectionHasAtLeastOneReviewedItem($sectionItems)
            ? 'In Progress'
            : 'Not Started';
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

        if (property_exists($this, 'procedureReviews') && is_array($this->procedureReviews)) {
            foreach ($this->procedureReviews as $rating) {
                if (in_array(strtoupper(trim((string) $rating)), ['E', 'S', 'U'], true)) {
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
        $meta = $this->itemReviewMeta[$itemId] ?? [];

        return (string) ($meta['review_date'] ?? '');
    }

    public function itemReviewDisplayReviewer(int $itemId): string
    {
        $meta = $this->itemReviewMeta[$itemId] ?? [];

        return (string) ($meta['reviewer_name'] ?? '');
    }

    public function itemReviewDisplayRating(int $itemId): string
    {
        return $this->extractRatingValue($this->partGResponses()[$itemId] ?? null);
    }
}
