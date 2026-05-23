@php
    use App\Support\EmployeeAssessmentPeriodCalculator;

    $loadableYearRange = EmployeeAssessmentPeriodCalculator::loadableYearRange();
    $assessmentPeriodsForJs = collect($assessmentPeriods ?? [])->map(fn ($period) => array_merge($period->toArray(), [
        'can_delete' => $period->canBeDeleted(),
        'can_load' => EmployeeAssessmentPeriodCalculator::isPeriodLoadable($period),
        'date_from' => $period->date_from?->format('Y-m-d'),
        'date_to' => $period->date_to?->format('Y-m-d'),
    ]))->values();
@endphp
<script>
    window.assessmentPeriods = @json($assessmentPeriodsForJs);
    window.performanceAssessmentStatuses = @json($performanceAssessmentStatuses ?? []);
    window.competencyAssessmentStatuses = @json($competencyAssessmentStatuses ?? []);
    window.currentFacilityId = '{{ $employee->currentAssignment && $employee->currentAssignment->facility ? $employee->currentAssignment->facility->id : '' }}';
    window.currentEmployeeNum = @json($employee->employee_num ?? null);
    window.currentEmployeeRecordId = @json($employee->id ?? null);
    window.suggestedAssessmentPeriod = @json($suggestedAssessmentPeriod ?? null);
    window.assessmentPeriodLoadYearWindow = {{ EmployeeAssessmentPeriodCalculator::LOADABLE_YEAR_OFFSET }};
    window.assessmentPeriodLoadableYearRange = @json($loadableYearRange);
</script>

<div id="deleteAffectedModal"
    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeDeleteAffectedModal()">&times;</button>
        <h3 class="text-lg font-bold mb-4">Assessments Affected by Deletion</h3>
        <div id="deleteAffectedList" class="mb-4 text-sm max-h-64 overflow-y-auto"></div>
        <div class="flex justify-end space-x-2">
            <button type="button" class="px-4 py-2 bg-gray-300 rounded"
                onclick="closeDeleteAffectedModal()">Cancel</button>
            <button type="button" id="confirmDeletePeriodBtn"
                class="px-4 py-2 bg-red-600 text-white rounded">Delete Anyway</button>
        </div>
    </div>
</div>

<div id="reviewedEmployeesModal"
    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeReviewedEmployeesModal()">&times;</button>
        <h3 class="text-lg font-bold mb-2">Employees Reviewed</h3>
        <div class="mb-2 text-sm text-gray-700">
            <span id="reviewedEmployeesFacility"></span> | <span id="reviewedEmployeesPeriod"></span>
        </div>
        <div id="reviewedEmployeesList" class="mb-4 text-sm max-h-96 overflow-y-auto"></div>
        <div class="flex justify-end">
            <button class="px-4 py-2 bg-gray-300 rounded"
                onclick="closeReviewedEmployeesModal()">Close</button>
        </div>
    </div>
</div>

<div id="allAssessmentPeriodsModal"
    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl relative">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            onclick="closeAllAssessmentPeriodsModal()">&times;</button>
        <h3 class="text-lg font-bold mb-2">Assessment Periods</h3>
        <p id="allAssessmentPeriodsDescription" class="mb-4 text-sm text-gray-600">Annual periods follow this employee&rsquo;s hire or rehire anniversary. Assessments default to the prior-year window for the review date (e.g. review in 2026 uses the cycle ending the year before).</p>
        <div id="allAssessmentPeriodsList" class="mb-4 text-sm max-h-96 overflow-y-auto"></div>
        <div class="flex justify-end">
            <button class="px-4 py-2 bg-gray-300 rounded"
                onclick="closeAllAssessmentPeriodsModal()">Close</button>
        </div>
    </div>
</div>

<div id="newPeriodModal" class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 hidden p-3">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[85vh] overflow-y-auto relative">
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl leading-none"
            onclick="closeNewPeriodModal()" aria-label="Close">&times;</button>

        <div class="px-4 py-3 border-b border-slate-200 pr-8">
            <h3 id="periodModalTitle" class="text-base font-bold text-slate-900">View Periods</h3>
            <p id="periodModalSubtitle" class="mt-1 hidden text-xs text-slate-600"></p>
            <p id="periodModalHelp" class="mt-1.5 text-[11px] leading-relaxed text-slate-600">
                Periods are unique to this employee. Annual windows follow the
                <strong>Original Hire Date</strong> on the Personal tab, or <strong>Rehire Date</strong> when Action is Rehire
                (each year runs from that anniversary through the day before the next anniversary&mdash;e.g. May 18 to May 17).
                For the <strong>review date</strong>, the system selects the prior completed year by default, not the employment year still in progress.
                Use the dropdown to change periods, or <strong>View Periods</strong> to browse history and load a period.
            </p>
        </div>

        {{-- Browse / select mode --}}
        <div id="periodModalSelectMode" class="px-4 py-3 space-y-3">
            <div class="flex flex-wrap items-end gap-2 rounded border border-slate-200 bg-slate-50 p-2">
                <div class="shrink-0">
                    <label for="periodModalReviewDate" class="block text-[10px] font-semibold uppercase text-slate-600">Review date</label>
                    <input type="date" id="periodModalReviewDate" class="mt-0.5 rounded border border-slate-300 px-2 py-1 text-xs">
                </div>
                <button type="button" id="periodModalRefreshBtn"
                    class="rounded bg-slate-700 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800">
                    Refresh
                </button>
                <p id="periodModalAnchorInfo" class="text-[11px] text-slate-600 flex-1 min-w-0"></p>
            </div>

            <div id="periodModalLoading" class="hidden text-xs text-slate-500">Loading…</div>
            <div id="periodModalError" class="hidden rounded border border-red-200 bg-red-50 px-2 py-1.5 text-xs text-red-700"></div>

            <div id="periodModalBrowseContent" class="space-y-3">
            <section id="periodModalRecommendedSection" class="hidden rounded border border-teal-200 bg-teal-50 p-2.5">
                <p class="text-[11px] font-semibold text-teal-900">Recommended (prior-year)</p>
                <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2">
                    <p id="periodModalRecommendedRange" class="text-xs font-semibold text-slate-900"></p>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" id="periodModalLoadRecommendedBtn"
                            class="hidden rounded bg-teal-700 px-2 py-1 text-[11px] font-semibold text-white hover:bg-teal-800">
                            Load
                        </button>
                        <button type="button" id="periodModalCreateRecommendedBtn"
                            class="hidden rounded bg-emerald-600 px-2 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700">
                            Create &amp; load
                        </button>
                    </div>
                </div>
                <p id="periodModalRecommendedMissing" class="mt-1.5 hidden text-[11px] text-amber-800"></p>
                <p id="periodModalContainingNote" class="mt-1 hidden text-[11px] text-slate-600"></p>
            </section>

            <section>
                <div class="mb-1 flex items-center justify-between gap-2">
                    <h4 class="text-xs font-bold text-slate-800">History</h4>
                    <button type="button" id="periodModalShowCustomBtn"
                        class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                        Create custom period
                    </button>
                </div>
                <div id="periodModalHistoryEmpty" class="hidden text-xs text-slate-500 py-2">No periods yet.</div>
                <div id="periodModalHistoryWrap" class="hidden overflow-x-auto max-h-44 border border-slate-200 rounded">
                    <table class="min-w-full text-[11px]">
                        <thead class="bg-slate-100 sticky top-0">
                            <tr>
                                <th class="border-b border-slate-200 px-2 py-1.5 text-left">Period</th>
                                <th class="border-b border-slate-200 px-2 py-1.5 text-left">Yr</th>
                                <th class="border-b border-slate-200 px-2 py-1.5 text-left">Type</th>
                                <th class="border-b border-slate-200 px-2 py-1.5 text-center">Perf</th>
                                <th class="border-b border-slate-200 px-2 py-1.5 text-center">Comp</th>
                                <th class="border-b border-slate-200 px-2 py-1.5 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="periodModalHistoryBody"></tbody>
                    </table>
                </div>
            </section>
            </div>

            <section id="periodModalCustomSection" class="hidden rounded border border-slate-300 bg-slate-50 p-2.5">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h4 class="text-xs font-bold text-slate-800">Custom period</h4>
                    <button type="button" id="periodModalHideCustomBtn"
                        class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 underline">
                        Back to list
                    </button>
                </div>
                <p class="mb-2 text-[11px] text-slate-600">Quarterly or other date ranges outside the annual hire/rehire rule.</p>
                <form id="newPeriodForm" class="space-y-2">
                    <input type="hidden" id="newPeriodIdInput" name="id" value="">
                    <div class="grid gap-2 grid-cols-2">
                        <div>
                            <label class="block text-[11px] font-semibold mb-0.5">From <span class="text-red-600">*</span></label>
                            <input type="date" name="date_from" id="newPeriodDateFromInput" class="border rounded px-2 py-1 w-full text-xs">
                            <span id="newPeriodDateFromError" class="text-red-600 text-[10px] hidden">Required</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold mb-0.5">To <span class="text-red-600">*</span></label>
                            <input type="date" name="date_to" id="newPeriodDateToInput" class="border rounded px-2 py-1 w-full text-xs">
                            <span id="newPeriodDateToError" class="text-red-600 text-[10px] hidden">Required</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-0.5">Review type <span class="text-red-600">*</span></label>
                        <select name="review_type" id="newPeriodReviewTypeInput" class="border rounded px-2 py-1 w-full text-xs">
                            <option value="A">Annual</option>
                            <option value="Q">Quarterly</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" id="periodModalCancelCustomBtn"
                            class="px-3 py-1.5 bg-gray-200 rounded text-xs">Cancel</button>
                        <button id="periodModalSubmitBtn" type="submit"
                            class="px-3 py-1.5 bg-green-600 text-white rounded text-xs font-semibold">Create &amp; load</button>
                    </div>
                </form>
            </section>

            <div class="flex justify-end pt-1">
                <button type="button" onclick="closeNewPeriodModal()"
                    class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900">Close</button>
            </div>
        </div>

        {{-- Edit mode (single period) --}}
        <div id="periodModalEditMode" class="hidden px-4 py-3">
            <form id="editPeriodForm" class="space-y-2 max-w-md">
                <input type="hidden" id="editPeriodIdInput" value="">
                <div>
                    <label class="block text-xs font-semibold mb-1">From <span class="text-red-600">*</span></label>
                    <input type="date" id="editPeriodDateFromInput" class="border rounded px-2 py-1 w-full text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">To <span class="text-red-600">*</span></label>
                    <input type="date" id="editPeriodDateToInput" class="border rounded px-2 py-1 w-full text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Type <span class="text-red-600">*</span></label>
                    <select id="editPeriodReviewTypeInput" class="border rounded px-2 py-1 w-full text-sm">
                        <option value="A">Annual</option>
                        <option value="Q">Quarterly</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" onclick="closeNewPeriodModal()" class="px-3 py-1.5 bg-gray-200 rounded text-xs">Cancel</button>
                    <button type="button" id="editPeriodSubmitBtn" class="px-3 py-1.5 bg-green-600 text-white rounded text-xs font-semibold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
