<div id="partG" class="tab-content hidden">
    <div class="overflow-x-auto">
        @php
        $partGSections = $employeeCompetencyItems->groupBy('section');
        @endphp
        <h2 class="text-xl font-bold mb-4">COMPETENCIES CHECKLIST: {{ $employee->currentAssignment?->position?->position_title ?? 'No Position Assigned' }}</h2>
        <div class="mb-5 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-900 shadow-sm">
            Rating Legend: 3 = Excellent &nbsp;&nbsp;&nbsp; 2 = Satisfactory &nbsp;&nbsp;&nbsp; 1 = Unsatisfactory &nbsp;&nbsp;&nbsp; N = Not Applicable
        </div>
        <div id="partGTableContainer">
        <table class="min-w-full overflow-hidden rounded-lg border border-teal-300 text-xs shadow-sm md:text-sm">
            <thead>
                <tr class="bg-teal-700 text-white">
                    <th class="border border-teal-500 px-3 py-2 text-left font-semibold tracking-wide">COMPETENCIES/ITEMS</th>
                    <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide w-1/6">RATING</th>
                    <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide w-1/6">ASSESSED DATE</th>
                    <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide w-1/6">ASSESSED BY</th>
                    <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide w-1/6">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partGSections as $sectionLabel => $items)
                <tr class="bg-teal-200 text-teal-950">
                    <td colspan="5" class="border border-teal-300 px-3 py-2 font-bold uppercase tracking-wide">{{ $sectionLabel }}</td>
                </tr>
                @foreach ($items as $item)
                @php
                $itemKey = 'G_' . $item->id;
                $empChecklist = $empCompetencyAssessments[$itemKey] ?? null;
                $ratingText = $empChecklist['rating'] ?? '';
                $rowClasses = $loop->odd
                    ? 'bg-teal-50 text-teal-950'
                    : 'bg-teal-100/70 text-teal-950';
                @endphp
                <tr class="{{ $rowClasses }} transition-colors hover:bg-teal-100">
                    <td class="border border-teal-200 px-3 py-2">
                        <span class="text-sm inline-block pl-4">{{ $item->item }}</span>
                    </td>
                    <td class="border border-teal-200 px-3 py-2 text-center font-semibold">{{ $ratingText }}</td>
                    <td class="border border-teal-200 px-3 py-2 text-center">{{ $empChecklist['verified_dt'] ?? '' }}</td>
                    <td class="border border-teal-200 px-3 py-2 text-center">
                        @if(!empty($empChecklist['verified_by']))
                        {{ $empChecklist['verified_by_name'] ?? (optional($users->firstWhere('id', $empChecklist['verified_by']))->name ?? $empChecklist['verified_by']) }}
                        @endif
                    </td>
                    <td class="border border-teal-200 px-3 py-2 text-center">
                        @if(!empty($empChecklist['verified_by']))
                        <a href="#" class="text-red-600 underline mr-1 unverify-link cursor-pointer text-sm" title="Revoke Assessment"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}">Revoke</a>
                        <span>|</span>
                        <a href="#" class="text-teal-600 underline ml-1 view-link cursor-pointer text-sm" title="View Assessment Details"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">View</a>
                        @else
                        <a href="#" class="text-teal-600 underline verify-link cursor-pointer text-sm" title="Assess Item"
                            data-item-key="{{ $itemKey }}"
                            data-emp-id="{{ $employee->employee_num }}"
                            data-item-label="{{ $item->item }}"
                            data-source-item-id="{{ $item->id }}"
                            data-rating="{{ $empChecklist['rating'] ?? '' }}"
                            data-assessment-date="{{ $empChecklist['verified_dt'] ?? '' }}"
                            data-comments="{{ $empChecklist['comments'] ?? '' }}"
                            data-assessed-by-id="{{ $empChecklist['verified_by'] ?? '' }}">Assess</a>
                        @endif
                    </td>
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="5" class="border border-teal-200 bg-teal-50 px-4 py-6 text-center text-teal-700">
                        No competency checklist items apply to this employee's current position.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="mt-6 rounded-xl border border-teal-200 bg-gradient-to-br from-teal-50 via-white to-teal-100/60 p-4 shadow-sm">
            <div class="mb-4 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-teal-900">Competency Evaluation Summary</h3>
                    <p class="text-xs text-teal-700">Review the calculated result, add notes, and complete the signatures.</p>
                </div>
            </div>

            <div class="mb-4 rounded-lg border border-teal-200 bg-white px-4 py-2 text-xs font-semibold text-teal-800 shadow-sm">
                Average Legend: Below 1.5 = Unsatisfactory &nbsp;&nbsp; 1.5 to 2.49 = Satisfactory &nbsp;&nbsp; 2.5 and above = Excellent
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-teal-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-teal-600">Total</div>
                    <input id="partGTotalScore" type="text" class="mt-2 w-full border-0 bg-transparent p-0 text-2xl font-bold text-teal-900 focus:outline-none focus:ring-0" readonly>
                </div>
                <div class="rounded-lg border border-teal-200 bg-white px-4 py-3 shadow-sm">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-teal-600">Average</div>
                    <input id="partGAverageScore" type="text" class="mt-2 w-full border-0 bg-transparent p-0 text-2xl font-bold text-teal-900 focus:outline-none focus:ring-0" readonly>
                </div>
                <label id="partGExcellentOption" class="flex items-center gap-3 rounded-lg border border-teal-200 bg-white px-4 py-3 font-semibold text-teal-700 shadow-sm transition-colors">
                    <input id="partGExcellentToggle" type="checkbox" class="h-4 w-4 rounded border-teal-400 text-teal-600 focus:ring-teal-500 pointer-events-none" tabindex="-1">
                    <span>Excellent</span>
                </label>
                <label id="partGSatisfactoryOption" class="flex items-center gap-3 rounded-lg border border-teal-200 bg-white px-4 py-3 font-semibold text-teal-700 shadow-sm transition-colors">
                    <input id="partGSatisfactoryToggle" type="checkbox" class="h-4 w-4 rounded border-teal-400 text-teal-600 focus:ring-teal-500 pointer-events-none" tabindex="-1">
                    <span>Satisfactory</span>
                </label>
            </div>

            <div class="mt-3">
                <label id="partGUnsatisfactoryOption" class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 font-semibold text-amber-800 shadow-sm transition-colors">
                    <input id="partGUnsatisfactoryToggle" type="checkbox" class="mt-1 h-4 w-4 rounded border-amber-400 text-teal-600 focus:ring-teal-500 pointer-events-none" tabindex="-1">
                    <span>Unsatisfactory; Requires Further Action Required</span>
                </label>
                <div id="partGUnsatisfactoryDetailsWrapper" class="mt-2 rounded-lg border border-dashed border-teal-300 bg-teal-50 px-3 py-3 opacity-60 transition-opacity">
                    <textarea id="partGUnsatisfactoryDetails" class="min-h-[64px] w-full resize-y rounded-md border border-teal-200 bg-white px-3 py-2 text-sm text-teal-900 placeholder:text-teal-500 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200 disabled:cursor-not-allowed disabled:bg-teal-50" placeholder="Describe the further action required..." disabled></textarea>
                </div>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-[1.2fr,0.8fr]">
                <div class="rounded-lg border border-teal-200 bg-white p-4 shadow-sm">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-teal-600">Comments</label>
                    <textarea class="min-h-[110px] w-full resize-y rounded-md border border-teal-200 bg-teal-50/60 px-3 py-3 text-sm text-teal-900 placeholder:text-teal-500 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200" placeholder="Enter comments here..."></textarea>
                </div>

                <div class="rounded-lg border border-teal-200 bg-white p-4 shadow-sm">
                    <div class="grid gap-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-teal-600">DSD Signature</label>
                            <input type="text" class="w-full rounded-md border border-teal-200 bg-teal-50/60 px-3 py-2 text-sm text-teal-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-teal-600">DSD Date</label>
                            <input type="date" class="w-full rounded-md border border-teal-200 bg-teal-50/60 px-3 py-2 text-sm text-teal-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-teal-600">Evaluator Signature/Title</label>
                            <input type="text" class="w-full rounded-md border border-teal-200 bg-teal-50/60 px-3 py-2 text-sm text-teal-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-teal-600">Evaluator Date</label>
                            <input type="date" class="w-full rounded-md border border-teal-200 bg-teal-50/60 px-3 py-2 text-sm text-teal-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-teal-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-teal-900">Competency Assessment History</h3>
                    <p class="text-xs text-teal-700">Compare prior overall competency results by assessment period, and use the item View action above to inspect item-level history across periods.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full overflow-hidden rounded-lg border border-teal-200 text-xs text-teal-900 md:text-sm">
                    <thead>
                        <tr class="bg-teal-700 text-white">
                            <th class="border border-teal-500 px-3 py-2 text-left font-semibold tracking-wide">ASSESSMENT PERIOD</th>
                            <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide">ASSESSED DATE</th>
                            <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide">ITEMS RATED</th>
                            <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide">TOTAL</th>
                            <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide">AVERAGE</th>
                            <th class="border border-teal-500 px-3 py-2 text-center font-semibold tracking-wide">OVERALL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($competencyAssessmentHistory as $historyRow)
                        <tr class="bg-white odd:bg-teal-50 even:bg-teal-100/40">
                            <td class="border border-teal-200 px-3 py-2">
                                <div class="font-semibold">{{ $historyRow['period_label'] }}</div>
                                @if((int) ($historyRow['assessment_period_id'] ?? 0) === (int) ($selectedAssessmentPeriodId ?? 0))
                                <div class="mt-1 inline-flex rounded-full bg-teal-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-teal-700">Current Period</div>
                                @endif
                            </td>
                            <td class="border border-teal-200 px-3 py-2 text-center">{{ $historyRow['assessment_date'] ?? '' }}</td>
                            <td class="border border-teal-200 px-3 py-2 text-center">{{ $historyRow['items_count'] }}</td>
                            <td class="border border-teal-200 px-3 py-2 text-center font-semibold">{{ $historyRow['total_score'] }}</td>
                            <td class="border border-teal-200 px-3 py-2 text-center font-semibold">{{ $historyRow['average_score'] }}</td>
                            <td class="border border-teal-200 px-3 py-2 text-center font-semibold">{{ $historyRow['overall_rating'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="border border-teal-200 bg-teal-50 px-4 py-6 text-center text-teal-700">
                                No prior competency assessment history is available for this employee yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var excellentToggle = document.getElementById('partGExcellentToggle');
        var satisfactoryToggle = document.getElementById('partGSatisfactoryToggle');
        var unsatisfactoryToggle = document.getElementById('partGUnsatisfactoryToggle');
        var excellentOption = document.getElementById('partGExcellentOption');
        var satisfactoryOption = document.getElementById('partGSatisfactoryOption');
        var unsatisfactoryOption = document.getElementById('partGUnsatisfactoryOption');
        var unsatisfactoryDetails = document.getElementById('partGUnsatisfactoryDetails');
        var unsatisfactoryWrapper = document.getElementById('partGUnsatisfactoryDetailsWrapper');
        var totalScoreField = document.getElementById('partGTotalScore');
        var averageScoreField = document.getElementById('partGAverageScore');
        var partGTableContainer = document.getElementById('partGTableContainer');

        function parseCompetencyRating(rawRating) {
            var rating = (rawRating || '').trim().toUpperCase();
            if (rating === 'E' || rating === 'EXCELLENT' || rating === '3') return 3;
            if (rating === 'S' || rating === 'SATISFACTORY' || rating === '2') return 2;
            if (rating === 'U' || rating === 'UNSATISFACTORY' || rating === '1') return 1;
            return null;
        }

        function syncOverallEvaluation(average) {
            if (!excellentToggle || !satisfactoryToggle || !unsatisfactoryToggle) {
                return;
            }

            var overall = null;
            if (average >= 2.5) {
                overall = 'excellent';
            } else if (average >= 1.5) {
                overall = 'satisfactory';
            } else if (average > 0) {
                overall = 'unsatisfactory';
            }

            excellentToggle.checked = overall === 'excellent';
            satisfactoryToggle.checked = overall === 'satisfactory';
            unsatisfactoryToggle.checked = overall === 'unsatisfactory';

            if (excellentOption) {
                excellentOption.classList.toggle('ring-2', overall === 'excellent');
                excellentOption.classList.toggle('ring-teal-400', overall === 'excellent');
                excellentOption.classList.toggle('bg-teal-100', overall === 'excellent');
            }
            if (satisfactoryOption) {
                satisfactoryOption.classList.toggle('ring-2', overall === 'satisfactory');
                satisfactoryOption.classList.toggle('ring-teal-400', overall === 'satisfactory');
                satisfactoryOption.classList.toggle('bg-teal-100', overall === 'satisfactory');
            }
            if (unsatisfactoryOption) {
                unsatisfactoryOption.classList.toggle('ring-2', overall === 'unsatisfactory');
                unsatisfactoryOption.classList.toggle('ring-amber-300', overall === 'unsatisfactory');
                unsatisfactoryOption.classList.toggle('bg-amber-100', overall === 'unsatisfactory');
            }

            syncUnsatisfactoryState();
        }

        function updatePartGSummaryScores() {
            if (!totalScoreField || !averageScoreField || !partGTableContainer) {
                return;
            }

            var total = 0;
            var count = 0;

            partGTableContainer.querySelectorAll('tbody tr').forEach(function(row) {
                var cells = row.querySelectorAll('td');
                if (cells.length !== 5) {
                    return;
                }

                var numericRating = parseCompetencyRating(cells[1].textContent);
                if (numericRating === null) {
                    return;
                }

                total += numericRating;
                count += 1;
            });

            totalScoreField.value = String(total);
            var average = count ? (total / count) : 0;
            averageScoreField.value = average.toFixed(2);
            syncOverallEvaluation(average);
        }

        function syncUnsatisfactoryState() {
            if (!unsatisfactoryToggle || !unsatisfactoryDetails || !unsatisfactoryWrapper) {
                return;
            }

            var enabled = unsatisfactoryToggle.checked;
            unsatisfactoryDetails.disabled = !enabled;
            unsatisfactoryWrapper.classList.toggle('opacity-60', !enabled);
            unsatisfactoryWrapper.classList.toggle('opacity-100', enabled);

            if (enabled) {
                unsatisfactoryDetails.focus();
                return;
            }

            unsatisfactoryDetails.value = '';
        }

        window.updatePartGSummaryScores = updatePartGSummaryScores;

        if (partGTableContainer && typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function() {
                updatePartGSummaryScores();
            });

            observer.observe(partGTableContainer, {
                childList: true,
                subtree: true,
                characterData: true,
            });
        }

        syncUnsatisfactoryState();
        updatePartGSummaryScores();
    });
</script>