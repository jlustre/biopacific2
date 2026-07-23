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
    $currentYear = (int) date('Y');
    $assessmentYearFor = static function ($period) {
        if (! $period) {
            return null;
        }

        return $period->review_type === 'A' && $period->date_to
            ? (int) $period->date_to->year
            : (int) $period->period_year;
    };
    $years = collect($assessmentPeriods)
        ->map($assessmentYearFor)
        ->filter()
        ->push($currentYear)
        ->unique()
        ->sort()
        ->values();
    $selectedPeriodYear = $selectedAssessmentPeriodId
        ? $assessmentYearFor(collect($assessmentPeriods)->firstWhere('id', $selectedAssessmentPeriodId))
        : null;
    $selectedYear = request('assessment_year')
        ?? $selectedPeriodYear
        ?? $currentYear;
@endphp

<div class="assessment-period-manager h-full rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm" data-manager-id="{{ $managerId }}" data-context-label="{{ $contextLabel }}">
    @if(session('assessment_period_error'))
        <p class="mb-2 rounded border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] text-amber-900">{{ session('assessment_period_error') }}</p>
    @endif
    @if(session('assessment_period_notice'))
        <p class="mb-2 rounded border border-sky-300 bg-sky-50 px-2 py-1 text-[11px] text-sky-900">{{ session('assessment_period_notice') }}</p>
    @endif
    <div class="flex flex-col gap-2.5">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_9.75rem] md:items-end">
            <div class="min-w-0">
                <label for="assessmentPeriodSelect-{{ $managerId }}" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Assessment Period
                </label>
                <div class="mt-1.5 flex min-w-0 items-center gap-2">
                    <select id="assessmentYearSelect-{{ $managerId }}"
                        class="js-assessment-year-select w-[4.25rem] shrink-0 rounded-md border border-slate-400 bg-white px-2 py-1.5 text-[11px] font-semibold text-slate-900 md:text-xs"
                        aria-label="Assessment year">
                        @foreach($years as $year)
                        <option value="{{ $year }}" @if((string) $selectedYear === (string) $year) selected @endif>{{ $year }}</option>
                        @endforeach
                    </select>

                    <form id="assessmentPeriodForm-{{ $managerId }}" class="js-assessment-period-form min-w-0 flex-1" method="GET" action="">
                        <select id="assessmentPeriodSelect-{{ $managerId }}" name="assessment_period_id"
                            class="js-assessment-period-select w-full rounded-md border border-slate-400 bg-white px-2 py-1.5 text-[11px] font-semibold text-slate-900 md:text-xs"
                            aria-label="Assessment period date range">
                            <option value="" @if(empty($selectedAssessmentPeriodId)) selected @endif>— Select/Create Assessment Period —</option>
                            @foreach($assessmentPeriods as $period)
                            @php
                                $periodLoadable = \App\Support\EmployeeAssessmentPeriodCalculator::isPeriodLoadable($period);
                                $periodDeletable = $period->canBeDeleted();
                                $periodAssessmentYear = $assessmentYearFor($period);
                            @endphp
                            <option value="{{ $period->id }}" data-year="{{ $periodAssessmentYear }}"
                                data-loadable="{{ $periodLoadable ? '1' : '0' }}"
                                data-can-delete="{{ $periodDeletable ? '1' : '0' }}"
                                @if((int) $selectedAssessmentPeriodId === (int) $period->id) selected @endif>
                                {{ $period->displayDateRange() }}@unless($periodLoadable) (not loadable)@endunless
                            </option>
                            @endforeach
                        </select>
                        <input type="hidden" class="js-assessment-year-hidden" name="assessment_year" value="{{ $selectedYear }}">
                        <input type="hidden" name="view_period" value="1">
                        @foreach(request()->except(['assessment_period_id', 'assessment_year', 'view_period']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>
                </div>
            </div>

            <div class="shrink-0 md:w-[9.75rem]">
                <label for="assessmentReviewDate-{{ $managerId }}" class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Review Date
                </label>
                <input type="date" id="assessmentReviewDate-{{ $managerId }}" name="assessment_review_date"
                    value="{{ request('assessment_review_date', date('Y-m-d')) }}"
                    class="mt-1.5 w-full rounded-md border border-slate-400 bg-white px-2 py-1.5 text-[11px] font-semibold text-slate-900 md:text-xs" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            <button type="button"
                class="js-assessment-period-action rounded-md bg-slate-900 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800"
                data-action="view-period"
                title="View assessment periods and load an existing period">View Periods</button>
            <button type="button"
                class="js-assessment-period-action rounded-md bg-slate-600 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-700"
                data-action="edit-period"
                title="Edit selected assessment period">Edit Period</button>
            <button type="button"
                class="js-assessment-period-action rounded-md bg-red-700 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-red-800"
                data-action="delete-period"
                title="Delete selected assessment period">Delete</button>
            @if($showReviewedEmployees)
            <button type="button"
                class="js-assessment-period-action rounded-md bg-slate-500 px-2.5 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-600"
                data-action="reviewed-employees"
                title="List all employees reviewed for this period and facility">Reviewed Employees</button>
            @endif
        </div>
    </div>
</div>
