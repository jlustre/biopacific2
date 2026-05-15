@php
    $managerId = $managerId ?? 'default';
    $contextLabel = $contextLabel ?? 'Performance Appraisal';
    $showReviewedEmployees = $showReviewedEmployees ?? true;
    $facilityName = $employee->currentAssignment && $employee->currentAssignment->facility
        ? $employee->currentAssignment->facility->name
        : '';
    $facilityId = $employee->currentAssignment && $employee->currentAssignment->facility
        ? $employee->currentAssignment->facility->id
        : '';
    $years = collect($assessmentPeriods)->pluck('period_year')->filter()->unique()->sort()->values();
    $currentYear = date('Y');
    $selectedYear = request('assessment_year') ?? ($years->contains($currentYear) ? $currentYear : $years->last());
@endphp

<div class="assessment-period-manager h-full rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm" data-manager-id="{{ $managerId }}" data-context-label="{{ $contextLabel }}">
    <div class="flex flex-col gap-3">
        <div class="min-w-0">
            <div class="flex items-center justify-between gap-2 mb-2">
                <div class="flex flex-col">
                <label for="assessmentPeriodSelect-{{ $managerId }}" class="mr-2 text-[11px] font-semibold uppercase tracking-wide text-slate-700">Assessment Period:</label>
                <div class="flex flex-wrap items-center gap-2 relative">
                    <select id="assessmentYearSelect-{{ $managerId }}"
                        class="js-assessment-year-select rounded-md border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-900 md:text-xs">
                        @foreach($years as $year)
                        <option value="{{ $year }}" @if((string) $selectedYear === (string) $year) selected @endif>{{ $year }}</option>
                        @endforeach
                    </select>
                    <form id="assessmentPeriodForm-{{ $managerId }}" class="js-assessment-period-form" method="GET" action="">
                        <select id="assessmentPeriodSelect-{{ $managerId }}" name="assessment_period_id"
                            class="js-assessment-period-select rounded-md border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-900 md:text-xs"
                            onchange="this.form.submit()">
                            @foreach($assessmentPeriods as $period)
                            <option value="{{ $period->id }}" data-year="{{ $period->period_year }}"
                                @if((int) $selectedAssessmentPeriodId === (int) $period->id) selected @endif>
                                {{ $period->date_from }} to {{ $period->date_to }}
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden" class="js-assessment-year-hidden" name="assessment_year" value="{{ $selectedYear }}">
                        @foreach(request()->except(['assessment_period_id', 'assessment_year']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>
                    <button type="button"
                        class="js-assessment-period-action my-1 rounded-md bg-slate-700 px-2 py-1 text-[11px] text-white cursor-pointer"
                        data-action="all-periods"
                        title="Show all created assessment periods for this employee">All Periods</button>
                    <button type="button"
                        class="js-assessment-period-action my-1 rounded-md bg-slate-900 px-2 py-1 text-[11px] text-white cursor-pointer"
                        data-action="new-period"
                        title="Create new assessment period">New Period</button>
                    <button type="button"
                        class="js-assessment-period-action my-1 rounded-md bg-slate-600 px-2 py-1 text-[11px] text-white cursor-pointer"
                        data-action="edit-period"
                        title="Edit selected assessment period">Edit Period</button>
                    <button type="button"
                        class="js-assessment-period-action my-1 rounded-md bg-red-700 px-2 py-1 text-[11px] text-white cursor-pointer"
                        data-action="delete-period"
                        title="Delete selected assessment period">Delete</button>
                    @if($showReviewedEmployees)
                    <button type="button"
                        class="js-assessment-period-action my-1 rounded-md bg-slate-500 px-2 py-1 text-[11px] text-white cursor-pointer"
                        data-action="reviewed-employees"
                        title="List all employees reviewed for this period and facility">Reviewed Employees</button>
                    @endif
                </div>
                </div>
                <div style="position:absolute; right:0; top:0; height:100%; display:flex; align-items:flex-start; gap:4px; background:inherit;">
                    <label for="assessmentReviewDate-{{ $managerId }}" class="text-[11px] font-semibold text-slate-700 mr-1 mt-1">REVIEW DATE:</label>
                    <input type="date" id="assessmentReviewDate-{{ $managerId }}" name="assessment_review_date" class="rounded-md border border-slate-400 bg-white px-2 py-1 text-[11px] font-semibold text-slate-900 md:text-xs mt-1" style="min-width:120px;" />
                </div>
            </div>
        </div>
    </div>
</div>

