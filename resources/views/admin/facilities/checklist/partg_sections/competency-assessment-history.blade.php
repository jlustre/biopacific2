<div class="mt-4 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
    <div class="mb-3 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment History</h3>
            <p class="text-[11px] text-slate-700">Compare prior overall competency results by assessment period, and use the item View action above to inspect item-level history across periods.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full overflow-hidden rounded-md border border-slate-500 text-[11px] text-slate-900 md:text-xs">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th class="w-[24rem] border border-slate-500 px-2 py-1.5 text-left font-semibold tracking-wide">ASSESSMENT PERIOD</th>
                    <th class="w-24 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ASSESSED DATE</th>
                    <th class="w-20 border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ITEMS RATED</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">TOTAL</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">AVERAGE</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">OVERALL</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">STATUS</th>
                    <th class="border border-slate-500 px-2 py-1.5 text-center font-semibold tracking-wide">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($competencyAssessmentHistory as $historyRow)
                <tr class="bg-white odd:bg-white even:bg-slate-50">
                    <td class="border border-slate-500 px-2 py-1.5 whitespace-nowrap">
                        <div class="font-semibold">{{ $historyRow['period_label'] }}</div>
                        @if((int) ($historyRow['assessment_period_id'] ?? 0) === (int) ($selectedAssessmentPeriodId ?? 0))
                        <div class="mt-1 inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700">Current Period</div>
                        @endif
                    </td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center">{{ $historyRow['assessment_date'] ?? '' }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center">{{ $historyRow['items_count'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['total_score'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['average_score'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['overall_rating'] }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center font-semibold">{{ $historyRow['status'] ?? 'Draft' }}</td>
                    <td class="border border-slate-500 px-2 py-1.5 text-center">
                        @if(!empty($historyRow['pdf_available']) && !empty($historyRow['competency_assessment_id']))
                        <a href="{{ route('admin.employees.competency-assessment.pdf', $historyRow['competency_assessment_id']) }}" target="_blank" class="text-slate-700 underline">View PDF</a>
                        @else
                        <span class="text-slate-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="border border-slate-500 bg-slate-50 px-4 py-6 text-center text-slate-700">
                        No prior competency assessment history is available for this employee yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
