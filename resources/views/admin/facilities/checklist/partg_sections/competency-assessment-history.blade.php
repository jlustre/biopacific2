<div class="mt-4 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
    <div class="mb-3 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment History</h3>
            <p class="text-[11px] text-slate-700">Review submitted competency sections by period. Use the PDF icon to view or generate a compact section report.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 md:text-xs">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th class="w-[16rem] border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">ASSESSMENT PERIOD</th>
                    <th class="min-w-[14rem] border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">COMPETENCY</th>
                    <th class="w-24 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ASSESSED DATE</th>
                    <th class="min-w-[8rem] border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">REVIEWER</th>
                    <th class="w-20 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ITEMS RATED</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">TOTAL POINTS</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">AVERAGE POINTS</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">OVERALL</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">STATUS</th>
                    <th class="w-16 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($competencyAssessmentHistory as $historyRow)
                <tr class="bg-white odd:bg-white even:bg-slate-50">
                    <td class="border border-slate-500 px-2 py-1.5 whitespace-nowrap">
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
                        <div class="font-semibold">{{ $formattedPeriodLabel }}</div>
                        @if((int) ($historyRow['assessment_period_id'] ?? 0) === (int) ($selectedAssessmentPeriodId ?? 0))
                        <div class="mt-1 inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700">Current Period</div>
                        @endif
                    </td>
                    <td class="border border-slate-500 px-2 py-1.5">
                        <div class="font-semibold leading-snug">{{ $historyRow['competency_name'] ?? '—' }}</div>
                    </td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center">
                        {{ !empty($historyRow['assessment_date']) ? \Illuminate\Support\Carbon::parse($historyRow['assessment_date'])->format('m-d-y') : '' }}
                    </td>
                    <td class="border border-slate-500 px-2 py-1.5">{{ $historyRow['reviewer_name'] ?: '—' }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center">{{ $historyRow['items_count'] }}/{{ $historyRow['total_items'] ?? $historyRow['items_count'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['total_score'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['average_score'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['overall_rating'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['status'] ?? 'Draft' }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center">
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
                    <td colspan="10" class="border border-slate-500 bg-slate-50 px-4 py-6 text-center text-slate-700">
                        No prior competency assessment history is available for this employee yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
