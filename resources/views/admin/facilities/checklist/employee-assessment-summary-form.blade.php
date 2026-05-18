@php
    $assessmentSummaryMode = $assessmentSummaryMode ?? 'performance';
    $assessmentWord = $assessmentWord ?? ucfirst($assessmentSummaryMode);
    $assessmentSummaryTitle = $assessmentSummaryTitle ?? ($assessmentWord . ' Evaluation Summary');
    $assessmentSummaryDescription = $assessmentSummaryDescription ?? 'Review the calculated result, add notes, and complete the signatures.';
    $assessmentLegendText = $assessmentLegendText ?? 'Average Legend: Below 1.5 = Unsatisfactory   1.5 to 2.49 = Satisfactory   2.5 and above = Excellent';
@endphp

<div class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
    <div class="mb-4 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">{{ $assessmentSummaryTitle }}</h3>
            <p class="text-[11px] text-slate-700">{{ $assessmentSummaryDescription }}</p>
        </div>
    </div>

    <div class="mb-3 rounded-md border border-slate-400 bg-white px-3 py-2 text-[11px] font-semibold text-slate-800 shadow-sm">
        {{ $assessmentLegendText }}
    </div>

    @if($assessmentSummaryMode === 'performance')
    @php
        $partFSummaryTotal = old('partf_total_score', $selectedPerformanceAssessment?->total_score ?? '');
        $partFSummaryAverage = old('partf_average_score', $selectedPerformanceAssessment?->average_score !== null
            ? number_format((float) $selectedPerformanceAssessment->average_score, 2, '.', '')
            : '');
        $partFSummaryOverall = old('overall_rating', $selectedPerformanceAssessment?->overall_rating ?? '');
    @endphp
    <div class="mb-3 grid gap-2 md:grid-cols-3 xl:grid-cols-3">
        <div class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Total</div>
            <input id="partFTotalScore" type="text" value="{{ $partFSummaryTotal !== '' ? $partFSummaryTotal : '' }}" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
        </div>
        <div class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Average</div>
            <input id="partFAverageScore" type="text" value="{{ $partFSummaryAverage }}" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
        </div>
        <div id="partFOverallRatingCard" class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm transition-colors">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Overall Rating</div>
            <input id="partFOverallRating" type="text" value="{{ $partFSummaryOverall }}" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.95fr)]">
        <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Development Notes</h4>
                <p class="mt-1 text-[11px] text-slate-700">Use the same assessment period narrative fields here that were previously shown beneath the old Part F form.</p>
            </div>

            <input type="hidden" name="overall_rating" id="partFOverallRatingValue" value="{{ old('overall_rating', $partFSummaryOverall) }}">

            <div id="partFUnsatisfactoryReasonWrapper" class="hidden rounded-md border border-dashed border-amber-300 bg-amber-50 px-3 py-3 shadow-sm">
                <label for="partFUnsatisfactoryReason" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-amber-900">Unsatisfactory Reason</label>
                <p class="mb-2 text-[11px] text-amber-800">Explain why the overall performance rating is unsatisfactory.</p>
                <textarea name="overall_unsatisfactory_reason" id="partFUnsatisfactoryReason" class="min-h-[88px] w-full resize-y rounded-md border border-amber-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Required when the overall rating is unsatisfactory." @readonly($partFAssessmentLocked)>{{ old('overall_unsatisfactory_reason', $selectedPerformanceAssessment?->comments ?? '') }}</textarea>
            </div>

            @include('admin.facilities.checklist.employee-areas-development')
        </div>

        <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Review Signatures</h4>
                <p class="mt-1 text-[11px] text-slate-700">The current Part F backend still uses save and final submit. This card mirrors the Part G presentation while preserving the existing submit flow.</p>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Supervisor
                    <input type="text" name="supervisor_name" class="mt-1 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900" value="{{ old('supervisor_name', $supervisorName ?? '') }}" @readonly($partFAssessmentLocked)>
                </label>

                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Review Date
                    <input type="date" name="review_dt" class="mt-1 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900" value="{{ old('review_dt', $reviewDt ?? '') }}" @required(!$partFAssessmentLocked) @readonly($partFAssessmentLocked || (auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id))>
                </label>

                <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2 md:items-end md:gap-x-6 md:gap-y-3">
                    <div class="min-w-0">
                        <label for="partFReviewSignaturesEmployeeName" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee</label>
                        <input id="partFReviewSignaturesEmployeeName" type="text" name="employee_name" class="w-full max-w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 md:max-w-md" value="{{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{ $employee->middle_name }}@endif" readonly>
                    </div>
                    <div class="min-w-0">
                        <label for="partFReviewSignaturesEmployeeAckDt" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee Acknowledge Date</label>
                        <input id="partFReviewSignaturesEmployeeAckDt" type="date" name="employee_acknowledge_dt" class="w-full min-w-0 rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900" value="{{ old('employee_acknowledge_dt', $employeeAcknowledgeDt ?? '') }}" @readonly($partFAssessmentLocked || !(auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id))>
                    </div>
                </div>

                <div class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-[11px] text-slate-700 md:col-span-2">
                    <div class="font-semibold uppercase tracking-wide text-slate-900">Current Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ $partFStatusLabel ?? 'Draft' }}</div>
                    <div class="mt-1">Save as Draft keeps the assessment editable. Submit Assessment marks the period complete in the current Part F flow.</div>
                </div>
            </div>

            @if($partFAssessmentLocked)
            <div class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-[11px] text-amber-900">
                This assessment has already been completed for the selected period. The notes and signature fields are shown for reference only.
            </div>
            @elseif(!empty($evaluatorActionsDisabled))
            <div class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-[11px] text-amber-950">
                You may update your employee acknowledgment date below. A supervisor must complete and submit this performance evaluation.
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <button type="submit" name="action" value="save" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save acknowledgment</button>
            </div>
            @else
            <div class="flex flex-wrap justify-end gap-2">
                <button type="submit" name="action" value="save" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save as Draft</button>
                <button type="submit" name="action" value="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Submit Assessment</button>
            </div>
            @endif
        </div>
    </div>
    @elseif($assessmentSummaryMode === 'competency')
    @php
        $partGSummaryTotal = $selectedCompetencyAssessment?->total_score ?? '';
        $partGSummaryAverage = $selectedCompetencyAssessment?->average_score !== null
            ? number_format((float) $selectedCompetencyAssessment->average_score, 2, '.', '')
            : '';
        $partGSummaryOverall = $selectedCompetencyAssessment?->overall_rating ?? '';
    @endphp
    <div
        @partg-summary-updated.window="window.updatePartGSummaryScores && window.updatePartGSummaryScores($event.detail)"
    >
        <div class="mb-3 grid gap-2 md:grid-cols-3 xl:grid-cols-3">
            <div class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm">
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Total</div>
                <input id="partGTotalScore" type="text" value="{{ $partGSummaryTotal !== '' ? $partGSummaryTotal : '' }}" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
            </div>
            <div class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm">
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Average</div>
                <input id="partGAverageScore" type="text" value="{{ $partGSummaryAverage }}" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
            </div>
            <div id="partGOverallRatingCard" class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm transition-colors">
                <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Overall Rating</div>
                <input id="partGOverallRating" type="text" value="{{ $partGSummaryOverall }}" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
            </div>
        </div>
        <input type="hidden" id="partGOverallRatingValue" value="{{ $partGSummaryOverall }}">
        <p class="text-[11px] text-slate-700">Scores update automatically as competency ratings are saved. Use each section&rsquo;s <strong>Save as Draft</strong> button to persist comments and signatures.</p>
    </div>
    @endif
</div>
