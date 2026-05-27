<?php

namespace App\Livewire\Admin\Facilities\Checklist;

use App\Support\CompetencyAssessmentHistoryResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CompetencyAssessmentHistoryTable extends Component
{
    use WithPagination;

    public string $employeeNum;

    public ?int $selectedAssessmentPeriodId = null;

    public string $search = '';

    public string $filterPeriodId = 'all';

    public string $filterCompetency = 'all';

    public string $filterStatus = 'all';

    public int $perPage = 10;

    public string $sortColumn = 'assessment_date';

    public string $sortDirection = 'desc';

    /** @var list<array<string, mixed>> */
    public array $historyRows = [];

    /** @var list<string> */
    private const SORTABLE_COLUMNS = [
        'competency',
        'assessment_date',
        'reviewer',
        'rated',
        'total_score',
        'average_score',
        'overall_rating',
        'status',
    ];

    public function mount(string $employeeNum, ?int $selectedAssessmentPeriodId = null): void
    {
        $this->employeeNum = $employeeNum;
        $this->selectedAssessmentPeriodId = $selectedAssessmentPeriodId;
        $this->loadHistory();
    }

    #[On('partg-summary-updated')]
    public function refreshHistory(): void
    {
        $this->loadHistory();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPeriodId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCompetency(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterPeriodId = 'all';
        $this->filterCompetency = 'all';
        $this->filterStatus = 'all';
        $this->sortColumn = 'assessment_date';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->applyDefaultSortDirectionForColumn($column);
        }

        $this->resetPage();
    }

    public function updatedSortColumn(): void
    {
        if (! in_array($this->sortColumn, self::SORTABLE_COLUMNS, true)) {
            $this->sortColumn = 'assessment_date';
        }

        $this->applyDefaultSortDirectionForColumn($this->sortColumn);
        $this->resetPage();
    }

    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    protected function applyDefaultSortDirectionForColumn(string $column): void
    {
        $this->sortDirection = in_array($column, ['assessment_date', 'rated', 'total_score', 'average_score'], true)
            ? 'desc'
            : 'asc';
    }

    public function refreshTable(): void
    {
        $this->loadHistory();
        $this->resetPage();
    }

    protected function loadHistory(): void
    {
        $this->historyRows = CompetencyAssessmentHistoryResolver::resolveForEmployee(
            $this->employeeNum,
            $this->selectedAssessmentPeriodId,
        );
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function filteredHistory(): Collection
    {
        $needle = strtolower(trim($this->search));

        return collect($this->historyRows)
            ->filter(function (array $row) use ($needle) {
                if ($this->filterPeriodId !== 'all' && (int) ($row['assessment_period_id'] ?? 0) !== (int) $this->filterPeriodId) {
                    return false;
                }

                if ($this->filterCompetency !== 'all' && (string) ($row['competency_name'] ?? '') !== $this->filterCompetency) {
                    return false;
                }

                if ($this->filterStatus !== 'all' && (string) ($row['status'] ?? '') !== $this->filterStatus) {
                    return false;
                }

                if ($needle === '') {
                    return true;
                }

                $haystack = strtolower(implode(' ', array_filter([
                    (string) ($row['period_label'] ?? ''),
                    (string) ($row['competency_name'] ?? ''),
                    (string) ($row['reviewer_name'] ?? ''),
                    (string) ($row['status'] ?? ''),
                    (string) ($row['overall_rating'] ?? ''),
                    (string) ($row['assessment_date'] ?? ''),
                    (string) ($row['items_count'] ?? ''),
                    (string) ($row['total_score'] ?? ''),
                    (string) ($row['average_score'] ?? ''),
                ])));

                return str_contains($haystack, $needle);
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    #[Computed]
    public function sortedFilteredHistory(): Collection
    {
        $items = $this->filteredHistory;

        $sorted = $items->sortBy(function (array $row) {
            return match ($this->sortColumn) {
                'competency' => strtolower((string) ($row['competency_name'] ?? '')).'|'.strtolower((string) ($row['period_label'] ?? '')),
                'assessment_date' => (string) ($row['assessment_date'] ?? ''),
                'reviewer' => strtolower((string) ($row['reviewer_name'] ?? '')),
                'rated' => (int) ($row['items_count'] ?? 0),
                'total_score' => (int) ($row['total_score'] ?? 0),
                'average_score' => (float) ($row['average_score'] ?? 0),
                'overall_rating' => strtolower((string) ($row['overall_rating'] ?? '')),
                'status' => strtolower((string) ($row['status'] ?? '')),
                default => '',
            };
        }, SORT_REGULAR);

        return $this->sortDirection === 'desc'
            ? $sorted->reverse()->values()
            : $sorted->values();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function periodFilterOptions(): array
    {
        return collect($this->historyRows)
            ->map(fn (array $row) => [
                'id' => (int) ($row['assessment_period_id'] ?? 0),
                'label' => (string) ($row['period_label'] ?? ''),
            ])
            ->filter(fn (array $option) => $option['id'] > 0 && $option['label'] !== '')
            ->unique('id')
            ->sortByDesc('id')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function competencyFilterOptions(): array
    {
        return collect($this->historyRows)
            ->pluck('competency_name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function statusFilterOptions(): array
    {
        return collect($this->historyRows)
            ->pluck('status')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function paginatedHistory(): LengthAwarePaginator
    {
        $perPage = max(5, min(50, $this->perPage));
        $page = max(1, (int) $this->getPage('historyPage'));
        $items = $this->sortedFilteredHistory;
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'historyPage',
                'query' => request()->query(),
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.facilities.checklist.competency-assessment-history-table', [
            'paginatedHistory' => $this->paginatedHistory(),
        ]);
    }
}
