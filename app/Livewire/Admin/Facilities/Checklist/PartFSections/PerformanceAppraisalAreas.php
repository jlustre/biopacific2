<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartFSections;

use App\Models\DocType;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeePerformanceAssessment;
use App\Models\EmployeePerformanceItem;
use App\Models\EmployeePerformanceSectionComment;
use App\Models\Position;
use App\Livewire\Concerns\GuardsAgainstSelfAssessment;
use App\Support\PartFPerformanceScoring;
use App\Support\PerformanceAppraisalTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class PerformanceAppraisalAreas extends Component
{
    use GuardsAgainstSelfAssessment;

    public string $employeeNum;

    public ?int $assessmentPeriodId = null;

    public bool $assessmentLocked = false;

    /**
     * @var list<array{section: string, accordion_key: string, doc_type_id: ?int, rows: list<array<string, mixed>>}>
     */
    public array $sections = [];

    /** @var array<int, string> */
    public array $latestRatings = [];

    /** @var array<int, true> */
    public array $scorableItemIds = [];

    public int $summaryTotalScore = 0;

    public string $summaryAverageScore = '0.00';

    public string $summaryOverallRating = 'Not Rated';

    /** @var array<int, string> */
    public array $sectionComments = [];

    public ?int $positionId = null;

    public ?string $positionTitle = null;

    public ?string $appraisalTemplateKey = null;

    public ?string $appraisalTemplateLabel = null;

    public int $totalRateableItems = 0;

    public function mount(
        string $employeeNum,
        ?int $assessmentPeriodId = null,
        bool $assessmentLocked = false,
        ?int $positionId = null,
        ?string $positionTitle = null,
    ): void {
        $this->employeeNum = $employeeNum;
        $this->assessmentPeriodId = $assessmentPeriodId;
        $this->assessmentLocked = $assessmentLocked;
        $this->positionId = $positionId;
        $this->positionTitle = $positionTitle;

        $this->resolvePositionContext();
        $this->buildSections();
        $this->loadLatestItemStates();
        $this->loadSummaryFromAssessment();
        $this->loadSectionComments();
    }

    public function updatedLatestRatings(mixed $value, string $key): void
    {
        $sourceItemId = (int) $key;
        $rating = is_string($value) ? strtoupper(trim($value)) : '';

        $rating = PartFPerformanceScoring::normalizeItemRating($rating) ?? '';
        if ($rating === '') {
            $this->reloadRatingState($sourceItemId);

            return;
        }

        if (! $this->persistRating($sourceItemId, $rating)) {
            $this->reloadRatingState($sourceItemId);

            return;
        }

        $this->dispatch('partf-summary-updated');
    }

    #[On('partf-sync-ratings')]
    public function syncAllRatings(): void
    {
        if (! $this->canPersistRatings() || $this->latestRatings === []) {
            return;
        }

        $assessmentDate = now()->toDateString();
        $assessedBy = Auth::id();

        DB::transaction(function () use ($assessmentDate, $assessedBy): void {
            $assessment = $this->firstOrCreateAssessment($assessmentDate, $assessedBy);
            $items = $assessment->itemsArray();

            foreach ($this->latestRatings as $sourceItemId => $rating) {
                if (! isset($this->scorableItemIds[(int) $sourceItemId])) {
                    continue;
                }

                $normalizedRating = PartFPerformanceScoring::normalizeItemRating($rating);
                if ($normalizedRating === null) {
                    continue;
                }

                $items[$this->itemKeyFor((int) $sourceItemId)] = ['rating' => $normalizedRating];
            }

            $assessment->items = $items;
            $this->applySummaryToAssessment($assessment, $items);
            $assessment->save();

            foreach ($this->latestRatings as $sourceItemId => $rating) {
                if (! isset($this->scorableItemIds[(int) $sourceItemId])) {
                    continue;
                }

                $normalizedRating = PartFPerformanceScoring::normalizeItemRating($rating);
                if ($normalizedRating === null) {
                    continue;
                }

                $this->createItemEntryIfChanged((int) $sourceItemId, $normalizedRating, $assessmentDate, $assessedBy);
            }
        });

        $this->dispatch('partf-summary-updated');
    }

    protected function persistRating(int $sourceItemId, string $rating): bool
    {
        $rating = PartFPerformanceScoring::normalizeItemRating($rating) ?? '';
        if (! isset($this->scorableItemIds[$sourceItemId])) {
            return false;
        }

        if (! $this->canPersistRatings() || $rating === '') {
            return false;
        }

        $assessmentDate = now()->toDateString();
        $assessedBy = Auth::id();

        DB::transaction(function () use ($sourceItemId, $rating, $assessmentDate, $assessedBy): void {
            $assessment = $this->firstOrCreateAssessment($assessmentDate, $assessedBy);
            $items = $assessment->itemsArray();
            $items[$this->itemKeyFor($sourceItemId)] = ['rating' => $rating];
            $assessment->items = $items;
            $this->applySummaryToAssessment($assessment, $items);
            $assessment->save();

            $this->createItemEntry((int) $sourceItemId, $rating, $assessmentDate, $assessedBy);
        });

        return true;
    }

    protected function applySummaryToAssessment(EmployeePerformanceAssessment $assessment, array $items): void
    {
        $ratings = [];
        foreach ($items as $itemKey => $itemData) {
            if (! preg_match('/^F_(\d+)$/', (string) $itemKey, $matches)) {
                continue;
            }

            $rating = EmployeePerformanceAssessment::itemRating($itemData);
            if ($rating !== null) {
                $ratings[(int) $matches[1]] = $rating;
            }
        }

        $summary = PartFPerformanceScoring::summarize($ratings, $this->scorableItemIds);
        $assessment->total_score = $summary['total_score'];
        $assessment->average_score = $summary['average_score'];
        $assessment->overall_rating = $summary['overall_rating'];
        $this->syncSummaryState($summary);
    }

    protected function syncSummaryState(array $summary): void
    {
        $this->summaryTotalScore = (int) $summary['total_score'];
        $this->summaryAverageScore = number_format((float) $summary['average_score'], 2, '.', '');
        $this->summaryOverallRating = (string) $summary['overall_rating'];
    }

    protected function reloadRatingState(int $sourceItemId): void
    {
        unset($this->latestRatings[$sourceItemId]);
        $this->loadLatestItemStates();
        $this->loadSummaryFromAssessment();
    }

    protected function loadSummaryFromAssessment(): void
    {
        if (! $this->assessmentPeriodId) {
            $this->syncSummaryState(PartFPerformanceScoring::summarize($this->latestRatings, $this->scorableItemIds));

            return;
        }

        $assessment = EmployeePerformanceAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if ($assessment && $assessment->overall_rating !== null) {
            $this->syncSummaryState([
                'total_score' => (int) ($assessment->total_score ?? 0),
                'average_score' => (float) ($assessment->average_score ?? 0),
                'overall_rating' => (string) $assessment->overall_rating,
            ]);

            return;
        }

        $this->syncSummaryState(PartFPerformanceScoring::summarize($this->latestRatings, $this->scorableItemIds));
    }

    protected function canPersistRatings(): bool
    {
        if ($this->assessmentLocked || ! $this->assessmentPeriodId) {
            return false;
        }

        if ($this->denyEvaluatorAction()) {
            return false;
        }

        return ! $this->performanceFinalized();
    }

    protected function firstOrCreateAssessment(string $assessmentDate, ?int $assessedBy): EmployeePerformanceAssessment
    {
        $assessment = EmployeePerformanceAssessment::query()->firstOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            [
                'items' => [],
                'assessment_date' => $assessmentDate,
                'assessed_by' => $assessedBy,
            ]
        );

        $assessment->assessment_date = $assessmentDate;
        $assessment->assessed_by = $assessedBy;

        return $assessment;
    }

    protected function itemKeyFor(int $sourceItemId): string
    {
        return 'F_'.$sourceItemId;
    }

    protected function itemLabelFor(int $sourceItemId): ?string
    {
        $item = EmployeePerformanceItem::query()->find($sourceItemId);

        return $item ? Str::limit(strip_tags((string) $item->item), 255) : null;
    }

    protected function createItemEntry(int $sourceItemId, string $rating, string $assessmentDate, ?int $assessedBy): void
    {
        EmployeeAssessmentItemEntry::query()->create([
            'employee_num' => $this->employeeNum,
            'assessment_period_id' => $this->assessmentPeriodId,
            'assessment_type' => 'performance',
            'item_key' => $this->itemKeyFor($sourceItemId),
            'item_label' => $this->itemLabelFor($sourceItemId),
            'source_item_id' => $sourceItemId,
            'rating' => $rating,
            'assessment_date' => $assessmentDate,
            'assessed_by' => $assessedBy,
            'comments' => null,
        ]);
    }

    protected function createItemEntryIfChanged(int $sourceItemId, string $rating, string $assessmentDate, ?int $assessedBy): void
    {
        $latest = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'performance')
            ->where('source_item_id', $sourceItemId)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->first();

        if ($latest && (string) $latest->rating === $rating) {
            return;
        }

        $this->createItemEntry($sourceItemId, $rating, $assessmentDate, $assessedBy);
    }

    public function render(): View
    {
        return view('livewire.admin.facilities.checklist.part-f-sections.performance-appraisal-areas');
    }

    protected function performanceFinalized(): bool
    {
        if (! $this->assessmentPeriodId) {
            return false;
        }

        return EmployeePerformanceAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('finalized', true)
            ->exists();
    }

    protected function buildSections(): void
    {
        $this->sections = [];
        $this->scorableItemIds = PartFPerformanceScoring::scorableItemIds(
            $this->positionId,
            $this->positionTitle
        );
        $this->totalRateableItems = count($this->scorableItemIds);

        $grouped = PartFPerformanceScoring::itemsQuery($this->positionId, $this->positionTitle)
            ->orderBy('order')
            ->get()
            ->groupBy('section');

        foreach ($grouped as $sectionLabel => $items) {
            $docTypeId = DocType::query()->where('name', $sectionLabel)->value('id');
            $accordionKey = 'pfa-'.substr(md5((string) $sectionLabel), 0, 10);

            $rows = [];
            $values = $items->values();

            foreach ($values as $itemIdx => $item) {
                $rawItemText = trim(strip_tags((string) ($item->item ?? '')));
                preg_match('/^(-+)/', $rawItemText, $itemIndentMatches);
                $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);
                $displayItem = ltrim((string) preg_replace('/^(-+)/', '', $rawItemText), '-');
                $nextItem = $values->get($itemIdx + 1);
                $nextRawItemText = trim(strip_tags((string) ($nextItem?->item ?? '')));
                preg_match('/^(-+)/', $nextRawItemText, $nextItemIndentMatches);
                $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);
                $hasChildItems = (bool) ($nextItem && $nextIndentLevel > $indentLevel);
                $collapsibleParentItems = ['PERINEAL CARE', 'CNA SKILLS'];
                $isMainParentItem = $indentLevel === 0 && $hasChildItems && in_array($displayItem, $collapsibleParentItems, true);
                $isStructuralParent = $hasChildItems && ! $isMainParentItem;

                $row = [
                    'id' => (int) $item->id,
                    'label' => (string) ($item->label ?? ''),
                    'display' => $displayItem,
                    'indentLevel' => $indentLevel,
                    'hasChildItems' => $hasChildItems,
                    'isMainParentItem' => $isMainParentItem,
                    'isStructuralParent' => $isStructuralParent,
                ];

                $rows[] = $row;
            }

            $this->sections[] = [
                'section' => (string) $sectionLabel,
                'accordion_key' => $accordionKey,
                'doc_type_id' => $docTypeId ? (int) $docTypeId : null,
                'rows' => $rows,
            ];
        }
    }

    protected function loadLatestItemStates(): void
    {
        $this->latestRatings = [];

        if (! $this->assessmentPeriodId) {
            return;
        }

        $entries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'performance')
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get();

        $grouped = $entries->groupBy(function (EmployeeAssessmentItemEntry $entry): string {
            if (! empty($entry->source_item_id)) {
                return 'F_'.$entry->source_item_id;
            }

            return (string) $entry->item_key;
        });

        foreach ($grouped as $itemKey => $group) {
            /** @var EmployeeAssessmentItemEntry $latest */
            $latest = $group->first();
            $sourceId = $latest->source_item_id;
            if (! $sourceId && preg_match('/^F_(\d+)$/', (string) $itemKey, $m)) {
                $sourceId = (int) $m[1];
            }
            if (! $sourceId || ! $latest->rating) {
                continue;
            }

            if (! isset($this->scorableItemIds[$sourceId])) {
                continue;
            }

            $normalizedRating = PartFPerformanceScoring::normalizeItemRating((string) $latest->rating);
            if ($normalizedRating !== null) {
                $this->latestRatings[$sourceId] = $normalizedRating;
            }
        }

        $assessment = EmployeePerformanceAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        if ($assessment) {
            $legacyItems = $assessment->itemsArray();
            foreach ($legacyItems as $key => $itemData) {
                if (! preg_match('/^F_(\d+)$/', (string) $key, $m)) {
                    continue;
                }
                $id = (int) $m[1];
                if (! isset($this->scorableItemIds[$id])) {
                    continue;
                }

                $rating = EmployeePerformanceAssessment::itemRating($itemData);
                if (! isset($this->latestRatings[$id]) && $rating !== null) {
                    $this->latestRatings[$id] = $rating;
                }
            }
        }
    }

    protected function loadSectionComments(): void
    {
        $this->sectionComments = [];

        if (! $this->assessmentPeriodId) {
            return;
        }

        $comments = EmployeePerformanceSectionComment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->get();

        foreach ($comments as $comment) {
            $this->sectionComments[(int) $comment->doc_type_id] = (string) $comment->comment;
        }
    }

    /**
     * Leaf rows: E/M/B radios. Structural parents use colspan headers (Part G style).
     */
    public function rowIsRatingRow(array $row): bool
    {
        return empty($row['isMainParentItem']) && empty($row['isStructuralParent']);
    }

    protected function resolvePositionContext(): void
    {
        if (! filled($this->positionTitle) && $this->positionId) {
            $this->positionTitle = Position::query()->whereKey($this->positionId)->value('title');
        }

        if (! filled($this->positionTitle)) {
            return;
        }

        $this->positionTitle = trim($this->positionTitle);
        $this->positionId ??= PerformanceAppraisalTemplate::positionIdForTitle($this->positionTitle);
        $this->appraisalTemplateKey = PerformanceAppraisalTemplate::templateForPositionTitle($this->positionTitle);
        $this->appraisalTemplateLabel = PerformanceAppraisalTemplate::displayLabelForPositionTitle($this->positionTitle);
    }
}
