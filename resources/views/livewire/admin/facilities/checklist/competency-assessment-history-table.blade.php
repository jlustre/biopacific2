<div class="mt-4 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment History</h3>
            <p class="text-[11px] text-slate-700">Review submitted competency sections by period. Use the PDF icon to view or generate a compact section report.</p>
        </div>
        @if(count($historyRows) > 0)
        <p class="text-[11px] text-slate-600">
            Showing {{ $paginatedHistory->firstItem() ?? 0 }}–{{ $paginatedHistory->lastItem() ?? 0 }} of {{ $paginatedHistory->total() }} records
        </p>
        @endif
    </div>

    @if(count($historyRows) > 0)
    <div class="mb-3 rounded-md border border-slate-300 bg-slate-50 p-2">
        <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label for="competency-history-search" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Search</label>
                <input
                    id="competency-history-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Period, competency, reviewer, status..."
                    class="w-full rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] text-slate-900 focus:border-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-500"
                />
            </div>
            <div>
                <label for="competency-history-period" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Assessment Period</label>
                <select
                    id="competency-history-period"
                    wire:model.live="filterPeriodId"
                    class="w-full rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] text-slate-900 focus:border-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="all">All periods</option>
                    @foreach($this->periodFilterOptions as $periodOption)
                    <option value="{{ $periodOption['id'] }}">{{ $periodOption['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="competency-history-competency" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Competency</label>
                <select
                    id="competency-history-competency"
                    wire:model.live="filterCompetency"
                    class="w-full rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] text-slate-900 focus:border-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="all">All competencies</option>
                    @foreach($this->competencyFilterOptions as $competencyName)
                    <option value="{{ $competencyName }}">{{ $competencyName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="competency-history-status" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Status</label>
                <select
                    id="competency-history-status"
                    wire:model.live="filterStatus"
                    class="w-full rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] text-slate-900 focus:border-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="all">All statuses</option>
                    @foreach($this->statusFilterOptions as $statusLabel)
                    <option value="{{ $statusLabel }}">{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="competency-history-per-page" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Per page</label>
                <select
                    id="competency-history-per-page"
                    wire:model.live="perPage"
                    class="w-full rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] text-slate-900 focus:border-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-500"
                >
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div class="col-span-full grid gap-2 border-t border-slate-300 pt-2 sm:grid-cols-2 md:hidden">
                <div>
                    <label for="competency-history-sort" class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Sort by</label>
                    <select
                        id="competency-history-sort"
                        wire:model.live="sortColumn"
                        class="w-full rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] text-slate-900 focus:border-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-500"
                    >
                        <option value="assessment_date">Assessed Date</option>
                        <option value="competency">Competency</option>
                        <option value="reviewer">Reviewer</option>
                        <option value="rated">Items Rated</option>
                        <option value="total_score">Total Points</option>
                        <option value="average_score">Ave. Points</option>
                        <option value="overall_rating">Overall Rating</option>
                        <option value="status">Status</option>
                    </select>
                </div>
                <div>
                    <label class="mb-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-700">Order</label>
                    <button
                        type="button"
                        wire:click="toggleSortDirection"
                        class="flex w-full items-center justify-center gap-1.5 rounded border border-slate-400 bg-white px-2 py-1.5 text-[11px] font-semibold text-slate-800 hover:bg-slate-100"
                    >
                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-[10px]" aria-hidden="true"></i>
                        {{ $sortDirection === 'asc' ? 'Ascending' : 'Descending' }}
                    </button>
                </div>
            </div>
            <div class="col-span-full flex flex-nowrap items-center justify-end gap-2 border-t border-slate-300 pt-2">
                <button
                    type="button"
                    wire:click="refreshTable"
                    wire:loading.attr="disabled"
                    wire:target="refreshTable"
                    class="shrink-0 whitespace-nowrap rounded border border-slate-500 bg-slate-700 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800 disabled:cursor-wait disabled:opacity-70"
                >
                    <span wire:loading.remove wire:target="refreshTable">Refresh Table</span>
                    <span wire:loading wire:target="refreshTable" class="whitespace-nowrap">Refreshing…</span>
                </button>
                <button
                    type="button"
                    wire:click="clearFilters"
                    wire:loading.attr="disabled"
                    wire:target="clearFilters"
                    class="shrink-0 whitespace-nowrap rounded border border-slate-400 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-wait disabled:opacity-70"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Mobile card layout --}}
    <div class="space-y-2 md:hidden">
        @forelse ($paginatedHistory as $historyRow)
        @php
            $periodLabel = (string) ($historyRow['period_label'] ?? '');
            $periodParts = explode(' to ', $periodLabel);
            $formattedPeriodLabel = $periodLabel;
            if (count($periodParts) === 2 && !empty($periodParts[0]) && !empty($periodParts[1])) {
                try {
                    $formattedPeriodLabel = \Illuminate\Support\Carbon::parse($periodParts[0])->format('m-d-y')
                        .' to '.
                        \Illuminate\Support\Carbon::parse($periodParts[1])->format('m-d-y');
                } catch (\Throwable) {
                    $formattedPeriodLabel = $periodLabel;
                }
            }
        @endphp
        <article
            class="rounded-md border border-slate-400 bg-white p-2.5 text-[11px] text-slate-900 shadow-sm"
            wire:key="competency-history-mobile-{{ $historyRow['assessment_period_id'] }}-{{ $historyRow['competency_section'] ?? $historyRow['competency_name'] }}"
        >
            <div class="mb-2 flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold leading-snug">{{ $historyRow['competency_name'] ?? '—' }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-700">{{ $formattedPeriodLabel }}</p>
                    @if((int) ($historyRow['assessment_period_id'] ?? 0) === (int) ($selectedAssessmentPeriodId ?? 0))
                    <span class="mt-1 inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-slate-700">Current Period</span>
                    @endif
                </div>
                <div class="shrink-0">
                    @if(!empty($historyRow['can_view_pdf']) && !empty($historyRow['competency_assessment_id']) && !empty($historyRow['competency_section']))
                    <a
                        href="{{ route('admin.employees.competency-section.pdf', ['assessment' => $historyRow['competency_assessment_id'], 'section' => $historyRow['competency_section']]) }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex h-8 w-8 items-center justify-center rounded border border-slate-400 bg-white text-red-700 hover:bg-red-50"
                        title="View compact PDF"
                        aria-label="View compact PDF for {{ $historyRow['competency_name'] ?? 'competency' }}"
                    >
                        <i class="fas fa-file-pdf text-sm" aria-hidden="true"></i>
                    </a>
                    @endif
                </div>
            </div>
            <dl class="grid grid-cols-2 gap-x-2 gap-y-1 text-[10px]">
                <div><dt class="font-semibold text-slate-600">Review Date</dt><dd>{{ !empty($historyRow['assessment_date']) ? \Illuminate\Support\Carbon::parse($historyRow['assessment_date'])->format('m-d-y') : '—' }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Reviewer</dt><dd class="truncate">{{ $historyRow['reviewer_name'] ?: '—' }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Rated Items</dt><dd>{{ $historyRow['items_count'] }}/{{ $historyRow['total_items'] ?? $historyRow['items_count'] }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Total Points</dt><dd class="font-semibold">{{ $historyRow['total_score'] }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Ave. Pts</dt><dd class="font-semibold">{{ $historyRow['average_score'] }}</dd></div>
                <div><dt class="font-semibold text-slate-600">Overall</dt><dd class="font-semibold">{{ $historyRow['overall_rating'] }}</dd></div>
                <div class="col-span-2"><dt class="font-semibold text-slate-600">Status</dt><dd class="font-semibold">{{ $historyRow['status'] ?? 'Draft' }}</dd></div>
            </dl>
        </article>
        @empty
        <p class="rounded-md border border-slate-500 bg-slate-50 px-4 py-6 text-center text-[11px] text-slate-700">
            @if(count($historyRows) === 0)
            No prior competency assessment history is available for this employee yet.
            @else
            No records match your search or filters.
            @endif
        </p>
        @endforelse
    </div>

    {{-- Desktop / tablet table --}}
    <div class="relative hidden md:block">
        <div class="overflow-x-auto overscroll-x-contain rounded-md border border-slate-500" style="-webkit-overflow-scrolling: touch;">
            <table class="w-full min-w-[42rem] table-fixed border-collapse text-[10px] text-slate-900 lg:text-[11px]">
            <colgroup>
                <col class="w-[10.5rem]">
                <col class="w-[4.25rem]">
                <col class="w-[5.5rem]">
                <col class="w-[3.75rem]">
                <col class="w-[3.25rem]">
                <col class="w-[3.25rem]">
                <col class="w-[4.5rem]">
                <col class="w-[4.75rem]">
                <col class="w-[2.75rem]">
            </colgroup>
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'competency', 'label' => 'COMPETENCY', 'class' => 'px-1.5'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'assessment_date', 'label' => 'REVIEW<br>DATE', 'align' => 'center'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'reviewer', 'label' => 'REVIEWER', 'class' => 'px-1.5'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'rated', 'label' => 'RATED<br>ITEMS', 'align' => 'center'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'total_score', 'label' => 'TOTAL<br>POINTS', 'align' => 'center'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'average_score', 'label' => 'AVE.<br>PTS', 'align' => 'center'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'overall_rating', 'label' => 'OVERALL<br>RATING', 'align' => 'center'])
                    @include('livewire.admin.facilities.checklist.partials.competency-history-sortable-th', ['column' => 'status', 'label' => 'STATUS', 'align' => 'center'])
                    <th class="sticky right-0 z-10 border border-slate-500 bg-slate-200 px-1 py-1 text-center font-semibold leading-tight shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.12)]">PDF</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paginatedHistory as $historyRow)
                <tr class="group bg-white odd:bg-white even:bg-slate-50" wire:key="competency-history-{{ $historyRow['assessment_period_id'] }}-{{ $historyRow['competency_section'] ?? $historyRow['competency_name'] }}">
                    <td class="border border-slate-500 px-1.5 py-1 align-top">
                        @php
                            $periodLabel = (string) ($historyRow['period_label'] ?? '');
                            $periodParts = explode(' to ', $periodLabel);
                            $formattedPeriodLabel = $periodLabel;
                            if (count($periodParts) === 2 && !empty($periodParts[0]) && !empty($periodParts[1])) {
                                try {
                                    $formattedPeriodLabel = \Illuminate\Support\Carbon::parse($periodParts[0])->format('m-d-y')
                                        .' to '.
                                        \Illuminate\Support\Carbon::parse($periodParts[1])->format('m-d-y');
                                } catch (\Throwable) {
                                    $formattedPeriodLabel = $periodLabel;
                                }
                            }
                            $competencyTitle = trim(($historyRow['competency_name'] ?? '—').' · '.$formattedPeriodLabel);
                        @endphp
                        <div class="min-w-0" title="{{ $competencyTitle }}">
                            <div class="truncate font-semibold leading-tight">{{ $historyRow['competency_name'] ?? '—' }}</div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                <span class="truncate text-[9px] leading-tight text-slate-600">{{ $formattedPeriodLabel }}</span>
                                @if((int) ($historyRow['assessment_period_id'] ?? 0) === (int) ($selectedAssessmentPeriodId ?? 0))
                                <span class="inline-flex shrink-0 rounded-full bg-slate-200 px-1.5 py-0.5 text-[8px] font-semibold uppercase tracking-wide text-slate-700">Current</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="border border-slate-500 px-1 py-1 text-center whitespace-nowrap">
                        {{ !empty($historyRow['assessment_date']) ? \Illuminate\Support\Carbon::parse($historyRow['assessment_date'])->format('m-d-y') : '' }}
                    </td>
                    <td class="border border-slate-500 px-1.5 py-1 align-top">
                        <span class="block truncate" title="{{ $historyRow['reviewer_name'] ?: '—' }}">{{ $historyRow['reviewer_name'] ?: '—' }}</span>
                    </td>
                    <td class="border border-slate-500 px-1 py-1 text-center whitespace-nowrap">{{ $historyRow['items_count'] }}/{{ $historyRow['total_items'] ?? $historyRow['items_count'] }}</td>
                    <td class="border border-slate-500 px-1 py-1 text-center font-semibold">{{ $historyRow['total_score'] }}</td>
                    <td class="border border-slate-500 px-1 py-1 text-center font-semibold">{{ $historyRow['average_score'] }}</td>
                    <td class="border border-slate-500 px-1 py-1 text-center align-top">
                        <span class="block break-words font-semibold leading-tight">{{ $historyRow['overall_rating'] }}</span>
                    </td>
                    <td class="border border-slate-500 px-1 py-1 text-center align-top">
                        <span class="block break-words font-semibold leading-tight">{{ $historyRow['status'] ?? 'Draft' }}</span>
                    </td>
                    <td class="sticky right-0 z-10 border border-slate-500 bg-white px-1 py-1 text-center shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.12)] group-even:bg-slate-50">
                        @if(!empty($historyRow['can_view_pdf']) && !empty($historyRow['competency_assessment_id']) && !empty($historyRow['competency_section']))
                        <a
                            href="{{ route('admin.employees.competency-section.pdf', ['assessment' => $historyRow['competency_assessment_id'], 'section' => $historyRow['competency_section']]) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-slate-400 bg-white text-red-700 hover:bg-red-50"
                            title="View compact PDF"
                            aria-label="View compact PDF for {{ $historyRow['competency_name'] ?? 'competency' }}"
                        >
                            <i class="fas fa-file-pdf text-sm" aria-hidden="true"></i>
                        </a>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="border border-slate-500 bg-slate-50 px-4 py-6 text-center text-slate-700">
                        @if(count($historyRows) === 0)
                        No prior competency assessment history is available for this employee yet.
                        @else
                        No records match your search or filters.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <p class="mt-1 hidden text-[10px] text-slate-500 lg:block">Scroll horizontally if needed; PDF column stays pinned on the right.</p>
    </div>

    @if($paginatedHistory->hasPages())
    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[11px] text-slate-600">
            Page {{ $paginatedHistory->currentPage() }} of {{ $paginatedHistory->lastPage() }}
        </p>
        <nav class="flex flex-wrap items-center gap-1" aria-label="Competency history pagination">
            <button
                type="button"
                wire:click="gotoPage(1, 'historyPage')"
                @disabled($paginatedHistory->onFirstPage())
                class="rounded border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            >
                First
            </button>
            <button
                type="button"
                wire:click="previousPage('historyPage')"
                @disabled($paginatedHistory->onFirstPage())
                class="rounded border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Previous
            </button>
            @foreach($paginatedHistory->getUrlRange(max(1, $paginatedHistory->currentPage() - 2), min($paginatedHistory->lastPage(), $paginatedHistory->currentPage() + 2)) as $page => $url)
            <button
                type="button"
                wire:click="gotoPage({{ $page }}, 'historyPage')"
                class="rounded border px-2 py-1 text-[11px] font-semibold {{ $page === $paginatedHistory->currentPage() ? 'border-slate-600 bg-slate-600 text-white' : 'border-slate-400 bg-white text-slate-800 hover:bg-slate-100' }}"
            >
                {{ $page }}
            </button>
            @endforeach
            <button
                type="button"
                wire:click="nextPage('historyPage')"
                @disabled(!$paginatedHistory->hasMorePages())
                class="rounded border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Next
            </button>
            <button
                type="button"
                wire:click="gotoPage({{ $paginatedHistory->lastPage() }}, 'historyPage')"
                @disabled(!$paginatedHistory->hasMorePages())
                class="rounded border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Last
            </button>
        </nav>
    </div>
    @endif
</div>
