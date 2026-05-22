<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGItemReviews;
use App\Support\PartGCompetencyScoring;
use App\Support\PreventsSelfAssessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Clean-slate Livewire component for the LICENSED NURSE eMAR COMPETENCY section.
 *
 * Design rules (kept narrow to avoid Alpine / window-event conflicts):
 *  - Ratings use wire:model.live on responses.{id} (reliable Livewire binding).
 *  - Section + global summaries are #[Computed] so every render reflects current responses.
 *  - Persistence and re-render happen entirely on the server; no JS bridges.
 */
class LicensedNurseEmarCompetency extends Component
{
    use ManagesPartGItemReviews;

    public const SECTION = 'LICENSED NURSE eMAR COMPETENCY';

    public string $employeeNum = '';

    public ?int $assessmentPeriodId = null;

    public bool $assessmentLocked = false;

    public bool $evaluatorActionsDisabled = false;

    public bool $sectionExcluded = false;

    /** @var list<array{id:int,item:string,rawItem:string,indentLevel:int,isParent:bool}> */
    public array $items = [];


    /** @var array<int,string> source_item_id => 'E'|'S'|'U'|'N' */
    public array $responses = [];

    // For draft save feedback
    public ?string $draftSaveMessage = null;
    public ?string $draftSaveType = null;

    public string $employeeName = '';

    public string $employeeTitle = '';

    public string $reviewerName = '';

    public string $reviewerTitle = '';

    public function mount(
        string $employeeNum,
        ?int $assessmentPeriodId = null,
        bool $assessmentLocked = false,
    ): void {
        $this->employeeNum = $employeeNum;
        $this->assessmentPeriodId = $assessmentPeriodId;
        $this->assessmentLocked = $assessmentLocked;

        $this->evaluatorActionsDisabled = PreventsSelfAssessment::isSelfAssessment(
            Auth::user(),
            $this->employeeNum
        );

        $employee = BPEmployee::with('currentAssignment.position')
            ->where('employee_num', $employeeNum)
            ->first();

        if ($employee) {
            $this->employeeName = trim(($employee->last_name ?? '').', '.($employee->first_name ?? ''), ', ');
            $this->employeeTitle = $employee->currentAssignment?->position?->title
                ?? ($employee->position ?? '');
        }

        $user = Auth::user();
        $this->reviewerName = $user?->name ?? '';
        $this->reviewerTitle = $user?->title ?? '';

        $this->items = $this->buildCompetencyItems();
        $this->loadResponsesFromStorage();
        $this->loadExclusionFromStorage();
        $this->normalizeResponseKeys();
    }

    /**
     * @return array{totalItems:int,checkedOfTotal:string,totalPoints:int|float,average:string,overallRating:string}
     */
    #[Computed]
    public function sectionSummaryMetrics(): array
    {
        return $this->buildSectionSummaryMetrics();
    }

    /**
     * @return array{total_score:int,average_score:float,overall_rating:string,average_score_formatted:string}
     */
    #[Computed]
    public function globalSummaryMetrics(): array
    {
        $global = $this->buildGlobalSummaryMetrics();
        $global['average_score_formatted'] = number_format((float) $global['average_score'], 2, '.', '');

        return $global;
    }

    public function updatedResponses(mixed $value, string $key): void
    {
        if ($this->cannotRate()) {
            unset($this->responses[$key]);

            return;
        }

        $itemId = (int) $key;
        $rating = strtoupper(trim((string) $value));

        if (! in_array($rating, ['E', 'S', 'U', 'N'], true)) {
            unset($this->responses[$itemId]);

            return;
        }

        $this->responses[$itemId] = $rating;
        $this->normalizeResponseKeys();
        $this->persistRating($itemId, $rating);
    }

    public function setRating(int $itemId, string $rating): void
    {
        if ($this->cannotRate()) {
            return;
        }

        $rating = strtoupper(trim($rating));

        if (! in_array($rating, ['E', 'S', 'U', 'N'], true)) {
            return;
        }

        $this->responses[$itemId] = $rating;
        $this->normalizeResponseKeys();
        $this->persistRating($itemId, $rating);
    }

    public function updatedSectionExcluded(): void
    {
        if (! $this->canPersist()) {
            $this->sectionExcluded = ! $this->sectionExcluded;

            return;
        }

        $this->persistResponses();
    }

    protected function cannotRate(): bool
    {
        return $this->assessmentLocked
            || $this->sectionExcluded
            || $this->evaluatorActionsDisabled
            || ! $this->assessmentPeriodId;
    }

    protected function denyEvaluatorAction(): bool
    {
        return $this->evaluatorActionsDisabled;
    }

    protected function persistRating(int $itemId, string $rating): void
    {
        if (! $this->canPersist() || $this->sectionExcluded) {
            return;
        }

        $this->persistResponses();
    }

    protected function normalizeResponseKeys(): void
    {
        $normalized = [];

        foreach ($this->responses as $itemId => $rating) {
            if ($rating === null || $rating === '') {
                continue;
            }

            $normalized[(int) $itemId] = $rating;
        }

        $this->responses = $normalized;
    }

    /**
     * @return array{totalItems:int,checkedOfTotal:string,totalPoints:int|float,average:string,overallRating:string}
     */
    protected function buildSectionSummaryMetrics(): array
    {
        if ($this->sectionExcluded) {
            return [
                'totalItems' => 0,
                'checkedOfTotal' => '',
                'totalPoints' => 0,
                'average' => '0',
                'overallRating' => 'Excluded',
            ];
        }

        $total = 0;
        $rated = 0;
        $notApplicable = 0;
        $points = 0;

        foreach ($this->items as $item) {
            if ($item['isParent'] ?? false) {
                continue;
            }

            $total++;
            $rating = $this->responses[$item['id']] ?? null;

            if ($rating === null || $rating === '') {
                continue;
            }

            if ($rating === 'N') {
                $notApplicable++;

                continue;
            }

            if (in_array($rating, ['E', 'S', 'U'], true)) {
                $rated++;
                $points += match ($rating) {
                    'E' => 3,
                    'S' => 2,
                    'U' => 1,
                };
            }
        }

        $checkedOfTotal = $notApplicable > 0
            ? $rated.' of '.$total.' rated ('.$notApplicable.' N/A)'
            : $rated.' of '.$total.' rated';

        $average = $rated > 0 ? round($points / $rated, 2) : 0.0;

        return [
            'totalItems' => $total,
            'checkedOfTotal' => $checkedOfTotal,
            'totalPoints' => $points,
            'average' => $rated > 0 ? number_format($average, 2, '.', '') : '—',
            'overallRating' => match (true) {
                $rated === 0 => '—',
                $average >= 2.5 => 'Excellent',
                $average >= 1.5 => 'Satisfactory',
                $average > 0 => 'Unsatisfactory',
                default => 'Needs Improvement',
            },
        ];
    }

    /**
     * @return array{total_score:int,average_score:float,overall_rating:string}
     */
    protected function buildGlobalSummaryMetrics(): array
    {
        $ratings = [];

        if ($this->assessmentPeriodId) {
            $latestEntries = EmployeeAssessmentItemEntry::query()
                ->where('employee_num', $this->employeeNum)
                ->where('assessment_period_id', $this->assessmentPeriodId)
                ->where('assessment_type', 'competency')
                ->whereNull('revoked_at')
                ->orderByDesc('assessment_date')
                ->orderByDesc('id')
                ->get()
                ->groupBy(fn (EmployeeAssessmentItemEntry $entry) => (int) $entry->source_item_id)
                ->map(fn ($entries) => $entries->first());

            foreach ($latestEntries as $entry) {
                $sourceItemId = (int) $entry->source_item_id;
                if ($sourceItemId <= 0) {
                    continue;
                }

                $rating = strtoupper(trim((string) $entry->rating));
                if (in_array($rating, ['E', 'S', 'U', 'N'], true)) {
                    $ratings[$sourceItemId] = $rating;
                }
            }
        }

        if (! $this->sectionExcluded) {
            foreach ($this->responses as $itemId => $rating) {
                if (! is_string($rating)) {
                    continue;
                }
                $normalized = strtoupper(trim($rating));
                if (in_array($normalized, ['E', 'S', 'U', 'N'], true)) {
                    $ratings[(int) $itemId] = $normalized;
                }
            }
        } else {
            foreach ($this->items as $item) {
                if ($item['isParent'] ?? false) {
                    continue;
                }
                unset($ratings[(int) $item['id']]);
            }
        }

        return PartGCompetencyScoring::summarize($ratings);
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.part-g-sections.licensed-nurse-emar-competency');
    }

    /**
     * @return list<array{id:int,item:string,rawItem:string,indentLevel:int,isParent:bool}>
     */
    protected function buildCompetencyItems(): array
    {
        $rawItems = EmployeeCompetencyItem::query()
            ->where('section', self::SECTION)
            ->orderBy('order')
            ->get();

        $items = [];

        foreach ($rawItems as $index => $item) {
            $raw = (string) $item->item;
            $indentLevel = 0;

            if (preg_match('/^(-+)/', $raw, $matches)) {
                $indentLevel = strlen($matches[1]);
            }

            // Parent only if the immediate next row is a deeper child.
            $isParent = false;
            if (isset($rawItems[$index + 1])) {
                $nextRaw = (string) $rawItems[$index + 1]->item;
                $nextIndent = 0;
                if (preg_match('/^(-+)/', $nextRaw, $nextMatches)) {
                    $nextIndent = strlen($nextMatches[1]);
                }
                $isParent = $nextIndent > $indentLevel;
            }

            $items[] = [
                'id' => (int) $item->id,
                'item' => ltrim($raw, '-'),
                'rawItem' => $raw,
                'indentLevel' => $indentLevel,
                'isParent' => $isParent,
            ];
        }

        return $items;
    }

    protected function loadResponsesFromStorage(): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        $sectionItemIds = collect($this->items)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($sectionItemIds === []) {
            return;
        }

        $assessment = $this->loadAssessment();

        if ($assessment) {
            $decoded = $this->decodeResponses($assessment->responses);
            $this->hydrateItemReviewMetaFromPayload($decoded);

            foreach ($decoded as $itemKey => $entry) {
                $sourceItemId = $this->normalizeKey($itemKey);
                if (! in_array($sourceItemId, $sectionItemIds, true)) {
                    continue;
                }

                $rating = is_array($entry) ? ($entry['response'] ?? null) : $entry;
                if (is_string($rating) && $rating !== '') {
                    $rating = strtoupper(trim($rating));
                    if (in_array($rating, ['E', 'S', 'U', 'N'], true)) {
                        $this->responses[$sourceItemId] = $rating;
                    }
                }
            }
        }

        // Latest item entries trump the JSON blob when both are present.
        $latestEntries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->whereIn('source_item_id', $sectionItemIds)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (EmployeeAssessmentItemEntry $entry) => (int) $entry->source_item_id)
            ->map(fn ($entries) => $entries->first());

        foreach ($latestEntries as $entry) {
            $sourceItemId = (int) $entry->source_item_id;
            if ($sourceItemId <= 0) {
                continue;
            }

            $rating = strtoupper(trim((string) $entry->rating));
            if (in_array($rating, ['E', 'S', 'U', 'N'], true)) {
                $this->responses[$sourceItemId] = $rating;
            }

            $this->itemReviewMeta[$sourceItemId] = [
                'review_date' => $entry->assessment_date?->format('Y-m-d'),
                'reviewer_id' => $entry->assessed_by,
                'reviewer_name' => $entry->assessed_by
                    ? (\App\Models\User::query()->find($entry->assessed_by)?->name ?? '')
                    : '',
                'comments' => $entry->comments,
            ];
        }
    }

    protected function loadExclusionFromStorage(): void
    {
        $assessment = $this->loadAssessment();
        if (! $assessment) {
            return;
        }

        $snapshot = is_array($assessment->snapshot_json) ? $assessment->snapshot_json : [];
        $excluded = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => (string) $label)
            ->all();

        $this->sectionExcluded = in_array(self::SECTION, $excluded, true);
    }

    protected function loadAssessment(): ?EmployeeCompetencyAssessment
    {
        if (! $this->assessmentPeriodId) {
            return null;
        }

        return EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();
    }

    protected function canPersist(): bool
    {
        if ($this->assessmentLocked) {
            return false;
        }

        if (! $this->assessmentPeriodId) {
            return false;
        }

        if ($this->evaluatorActionsDisabled) {
            return false;
        }

        return true;
    }

    /**
     * Persist current section state:
     *  - upsert the EmployeeCompetencyAssessment row (responses JSON + snapshot exclusion + reviewer info)
     *  - create a new EmployeeAssessmentItemEntry for any rating that actually changed
     *  - refresh the assessment's denormalized total/average/overall columns
     */
    protected function persistResponses(): void
    {
        if (! $this->canPersist()) {
            return;
        }

        $row = $this->loadAssessment();
        $existing = $this->decodeResponses($row?->responses);

        $payload = $this->mergeItemReviewMetaIntoResponsesPayload($existing);

        $snapshot = is_array($row?->snapshot_json) ? $row->snapshot_json : [];
        $snapshot = $this->applyExclusionToSnapshot($snapshot);

        $updateData = [
            'responses' => $payload,
            'snapshot_json' => $snapshot,
            'reviewer_name' => $this->reviewerName,
            'reviewer_title' => $this->reviewerTitle,
            'employee_name' => $this->employeeName,
            'employee_title' => $this->employeeTitle,
            'submitted_by' => Auth::id(),
            'status' => $this->resolveStatus($row),
            'submitted_at' => $row?->submitted_at,
        ];

        $row = EmployeeCompetencyAssessment::updateOrCreate(
            [
                'employee_num' => $this->employeeNum,
                'assessment_period_id' => $this->assessmentPeriodId,
            ],
            $updateData
        );

        // Append entries only for this section's items (not the entire payload).
        $sectionItemIds = collect($this->items)
            ->filter(fn (array $item) => ! ($item['isParent'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($sectionItemIds as $itemId) {
            $rating = $this->responses[$itemId] ?? null;
            if (! is_string($rating) || $rating === '') {
                continue;
            }
            $this->upsertItemEntry($itemId, $rating);
        }

        $this->syncAssessmentSummaryColumns($row);
        $this->dispatchPartGSummaryUpdated();
    }

    protected function upsertItemEntry(int $itemId, string $rating): void
    {
        $latest = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->where('source_item_id', $itemId)
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->first();

        $meta = $this->itemReviewMeta[$itemId] ?? [];
        $assessmentDate = (string) ($meta['review_date'] ?? now()->toDateString());
        $assessedBy = isset($meta['reviewer_id']) ? (int) $meta['reviewer_id'] : Auth::id();
        $comments = filled($meta['comments'] ?? null) ? (string) $meta['comments'] : null;

        if (
            $latest
            && strtoupper((string) $latest->rating) === $rating
            && (string) ($latest->comments ?? '') === (string) ($comments ?? '')
            && $latest->assessment_date?->format('Y-m-d') === substr($assessmentDate, 0, 10)
        ) {
            return;
        }

        EmployeeAssessmentItemEntry::create([
            'employee_num' => $this->employeeNum,
            'assessment_period_id' => $this->assessmentPeriodId,
            'assessment_type' => 'competency',
            'item_key' => 'G_'.$itemId,
            'item_label' => $this->labelForItem($itemId),
            'source_item_id' => $itemId,
            'rating' => $rating,
            'assessment_date' => $assessmentDate,
            'assessed_by' => $assessedBy,
            'comments' => $comments,
        ]);
    }

    protected function dispatchPartGSummaryUpdated(): void
    {
        $summary = $this->buildGlobalSummaryMetrics();

        $payload = [
            'totalScore' => $summary['total_score'],
            'averageScore' => $summary['average_score'],
            'overallRating' => $summary['overall_rating'],
        ];

        $this->dispatch(
            'partg-summary-updated',
            totalScore: $payload['totalScore'],
            averageScore: $payload['averageScore'],
            overallRating: $payload['overallRating'],
        );

        $this->js('window.updatePartGSummaryScores && window.updatePartGSummaryScores('.json_encode($payload).')');
    }

    protected function syncAssessmentSummaryColumns(EmployeeCompetencyAssessment $row): void
    {
        // Pull fresh ratings straight from the DB so the denormalized columns
        // never drift from the source-of-truth item-entry table.
        $latestEntries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->where('assessment_type', 'competency')
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (EmployeeAssessmentItemEntry $entry) => (int) $entry->source_item_id)
            ->map(fn ($entries) => $entries->first());

        $ratings = [];
        foreach ($latestEntries as $entry) {
            $sourceItemId = (int) $entry->source_item_id;
            if ($sourceItemId <= 0) {
                continue;
            }
            $rating = strtoupper(trim((string) $entry->rating));
            if (in_array($rating, ['E', 'S', 'U', 'N'], true)) {
                $ratings[$sourceItemId] = $rating;
            }
        }

        // Excluded sections should not contribute to the totals.
        if ($this->sectionExcluded) {
            foreach ($this->items as $item) {
                if ($item['isParent'] ?? false) {
                    continue;
                }
                unset($ratings[(int) $item['id']]);
            }
        }

        $summary = PartGCompetencyScoring::summarize($ratings);

        $row->update([
            'total_score' => $summary['total_score'],
            'average_score' => $summary['average_score'],
            'overall_rating' => $summary['overall_rating'],
        ]);
    }

    /**
     * @param  array<string,mixed>  $snapshot
     * @return array<string,mixed>
     */
    protected function applyExclusionToSnapshot(array $snapshot): array
    {
        $labels = collect($snapshot['excluded_section_labels'] ?? [])
            ->map(fn ($label) => trim((string) $label))
            ->filter(fn ($label) => $label !== '' && $label !== self::SECTION)
            ->values()
            ->all();

        if ($this->sectionExcluded) {
            $labels[] = self::SECTION;
        }

        $snapshot['excluded_section_labels'] = array_values(array_unique($labels));

        return $snapshot;
    }

    protected function resolveStatus(?EmployeeCompetencyAssessment $row): string
    {
        $status = (string) ($row?->status ?? 'draft');

        if (in_array($status, ['completed', 'for_employee_signature', 'for_reviewer_signature'], true)) {
            return $status;
        }

        return 'draft';
    }

    protected function labelForItem(int $itemId): ?string
    {
        foreach ($this->items as $item) {
            if ((int) $item['id'] === $itemId) {
                return Str::limit(ltrim((string) $item['item'], '-'), 255);
            }
        }

        return null;
    }

    /**
     * @return array<int|string,mixed>
     */
    protected function decodeResponses(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    protected function normalizeKey(mixed $key): int
    {
        if (is_int($key)) {
            return $key;
        }

        $value = trim((string) $key);
        if ($value === '') {
            return 0;
        }

        if (preg_match('/^G[_-]?(\d+)$/i', $value, $matches)) {
            return (int) $matches[1];
        }

        return (int) $value;
    }

}
