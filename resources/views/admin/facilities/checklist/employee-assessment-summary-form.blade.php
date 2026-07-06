@php
    $assessmentSummaryMode = $assessmentSummaryMode ?? 'performance';
    $assessmentWord = $assessmentWord ?? ucfirst($assessmentSummaryMode);
    $assessmentSummaryTitle = $assessmentSummaryTitle ?? ($assessmentWord . ' Evaluation Summary');
    $assessmentSummaryDescription = $assessmentSummaryDescription ?? 'Review the calculated result, add notes, and complete the signatures.';
    $partFSummaryOverallForLegend = ($assessmentSummaryMode ?? 'performance') === 'performance'
        ? old('overall_rating', $selectedPerformanceAssessment?->overall_rating ?? '')
        : '';
    $partFSummaryAverageForLegend = ($assessmentSummaryMode ?? 'performance') === 'performance'
        ? ($selectedPerformanceAssessment?->average_score !== null
            ? (float) $selectedPerformanceAssessment->average_score
            : null)
        : null;
@endphp

<div class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
    <div class="mb-4 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">{{ $assessmentSummaryTitle }}</h3>
            <p class="text-[11px] text-slate-700">{{ $assessmentSummaryDescription }}</p>
        </div>
    </div>

    @if($assessmentSummaryMode === 'performance')
    <div class="mb-3">
        @include('admin.facilities.checklist.partials.part-f-rating-legend', [
            'showOverallFooter' => true,
            'overallRatingLabel' => $partFSummaryOverallForLegend,
            'overallAverage' => $partFSummaryAverageForLegend,
        ])
    </div>
    @else
    <div class="mb-3">
        @include('admin.facilities.checklist.partials.part-g-average-legend')
    </div>
    @endif

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
                <label for="partFUnsatisfactoryReason" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-amber-900">Below Expectations Reason</label>
                <p class="mb-2 text-[11px] text-amber-800">Explain why the overall performance rating is below expectations.</p>
                <textarea name="overall_unsatisfactory_reason" id="partFUnsatisfactoryReason" class="min-h-[88px] w-full resize-y rounded-md border border-amber-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Required when the overall rating is below expectations." @readonly(!empty($partFSummaryReadOnly))>{{ old('overall_unsatisfactory_reason', $selectedPerformanceAssessment?->comments ?? '') }}</textarea>
            </div>

            @include('admin.facilities.checklist.employee-areas-development')
        </div>

        <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Review Signatures</h4>
                <p class="mt-1 text-[11px] text-slate-700">The current Part F backend still uses save and final submit. This card mirrors the Part G presentation while preserving the existing submit flow.</p>
            </div>

            @php
                $partFReviewDateValue = old('review_dt', isset($reviewDt) && $reviewDt !== '' ? \Illuminate\Support\Carbon::parse($reviewDt)->format('Y-m-d') : '');
                $partFEmployeeAckDateValue = old('employee_acknowledge_dt', isset($employeeAcknowledgeDt) && $employeeAcknowledgeDt !== '' ? \Illuminate\Support\Carbon::parse($employeeAcknowledgeDt)->format('Y-m-d') : '');
                $partFReviewDateReadOnly = !empty($partFSummaryReadOnly) || (auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id);
                $partFEmployeeAckDateReadOnly = !empty($partFSummaryReadOnly) || !(auth()->check() && isset($employee->user_id) && auth()->id() == $employee->user_id && !empty($partFEmployeeCanConfirm));
            @endphp

            <div class="grid gap-3 md:grid-cols-2">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Reviewer
                    <input type="text" name="supervisor_name" class="mt-1 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900" value="{{ old('supervisor_name', $supervisorName ?? '') }}" @readonly(!empty($partFSummaryReadOnly))>
                </label>

                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Review Date
                    @if($partFReviewDateReadOnly && $partFReviewDateValue !== '')
                    <input type="hidden" name="review_dt" value="{{ $partFReviewDateValue }}">
                    @endif
                    <input type="date" @if(! $partFReviewDateReadOnly) name="review_dt" @endif class="mt-1 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900" value="{{ $partFReviewDateValue }}" @required(empty($partFSummaryReadOnly)) @readonly($partFReviewDateReadOnly)>
                </label>

                <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2 md:items-end md:gap-x-6 md:gap-y-3">
                    <div class="min-w-0">
                        <label for="partFReviewSignaturesEmployeeName" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee</label>
                        <input id="partFReviewSignaturesEmployeeName" type="text" name="employee_name" class="w-full max-w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900 md:max-w-md" value="{{ $employee->last_name }}, {{ $employee->first_name }}@if($employee->middle_name), {{ $employee->middle_name }}@endif" readonly>
                    </div>
                    <div class="min-w-0">
                        <label for="partFReviewSignaturesEmployeeAckDt" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee Acknowledge Date</label>
                        @if($partFEmployeeAckDateReadOnly && $partFEmployeeAckDateValue !== '')
                        <input type="hidden" name="employee_acknowledge_dt" value="{{ $partFEmployeeAckDateValue }}">
                        @endif
                        <input id="partFReviewSignaturesEmployeeAckDt" type="date" @if(! $partFEmployeeAckDateReadOnly) name="employee_acknowledge_dt" @endif class="w-full min-w-0 rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900" value="{{ $partFEmployeeAckDateValue }}" @readonly($partFEmployeeAckDateReadOnly)>
                    </div>
                </div>

                <div class="rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-[11px] text-slate-700 md:col-span-2">
                    <div class="font-semibold uppercase tracking-wide text-slate-900">Current Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ $partFStatusLabel ?? 'In Progress' }}</div>
                    <div class="mt-1">
                        @if(!empty($partFEmployeeCanConfirm))
                        Enter your employee comments, then click <strong>Sign &amp; Acknowledge</strong> to confirm this assessment.
                        @elseif(!empty($partFWaitingEmployeeConfirmation) && empty($evaluatorActionsDisabled))
                        This assessment has been submitted for <strong>employee confirmation</strong>. The submit button is disabled until the employee acknowledges it or sends it back for corrections.
                        @elseif(!empty($partFReviewerCanApprove))
                        The employee has acknowledged this assessment. If you change ratings or notes, save to send it back for employee confirmation. Approve only when no further changes are needed.
                        @elseif(!empty($partFAssessmentLocked))
                        This assessment is completed and read-only.
                        @else
                        Save as Draft keeps the assessment editable. Submit for Employee Confirmation sends it to the employee for signature.
                        @endif
                    </div>
                </div>
            </div>

            @if($partFAssessmentLocked)
            <div class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-[11px] text-amber-900">
                This assessment has been completed for the selected period. The notes and signature fields are shown for reference only.
            </div>
            @if(empty($evaluatorActionsDisabled))
            <div class="flex flex-wrap justify-end gap-2">
                <button type="submit" data-workflow-action="reopen" class="rounded-md border border-slate-400 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Reopen for Editing</button>
            </div>
            @endif
            @elseif(!empty($partFWaitingEmployeeConfirmation) && empty($evaluatorActionsDisabled))
            <div class="rounded-md border border-sky-300 bg-sky-50 px-3 py-2 text-[11px] text-sky-950">
                This performance assessment was submitted for <strong>employee signature / confirmation</strong> on
                <strong>{{ optional($selectedPerformanceAssessment?->updated_at)->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? 'the selected review date' }}</strong>.
                The employee will see a dashboard task and email notification. You can submit again only if the employee sends it back for corrections.
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <button type="submit" data-workflow-action="submit" class="rounded-md bg-slate-400 px-4 py-2 text-sm font-medium text-white" disabled data-workflow-locked="1" title="Already submitted for employee confirmation">Submit for Employee Confirmation</button>
            </div>
            @elseif(!empty($partFEmployeeCanConfirm) && !empty($evaluatorActionsDisabled))
            <div class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-[11px] text-amber-950">
                You may enter employee comments, then sign to acknowledge this assessment. Ratings and reviewer fields cannot be changed on your own record.
            </div>
            @if($errors->has('employee_signature'))
            <div class="rounded-md border border-red-300 bg-red-50 px-3 py-2 text-[11px] text-red-800">{{ $errors->first('employee_signature') }}</div>
            @endif
            <div class="flex flex-wrap justify-end gap-2">
                <button type="submit" data-workflow-action="send_back" class="rounded-md border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">Send Back to Reviewer</button>
                <button type="button" id="partFOpenEmployeeSignatureModal" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Sign &amp; Acknowledge</button>
            </div>
            @elseif(!empty($partFReviewerCanApprove) && empty($evaluatorActionsDisabled))
            <div class="rounded-md border border-violet-300 bg-violet-50 px-3 py-2 text-[11px] text-violet-950">
                @if(!empty($partFContentChangedSinceEmployeeConfirmation))
                This assessment was changed after the employee confirmed it. Save or resubmit to send it back to the employee. Approval is disabled until they confirm again.
                @else
                The employee has acknowledged and signed this assessment. Review it, then approve when no further changes are needed.
                @endif
            </div>
            <div id="partFReviewerApprovalActions" class="flex flex-wrap justify-end gap-2">
                <button type="submit" data-workflow-action="save" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save Changes</button>
                <button type="submit" data-workflow-action="submit" id="partFResubmitForEmployeeBtn" class="rounded-md border border-sky-400 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-950 hover:bg-sky-100 {{ empty($partFContentChangedSinceEmployeeConfirmation) ? 'hidden' : '' }}">Resubmit for Employee Confirmation</button>
                <button type="submit" data-workflow-action="approve" id="partFApproveAssessmentBtn" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black {{ !empty($partFCanApprove) ? '' : 'hidden' }}">Approve Assessment</button>
            </div>
            @elseif(!empty($evaluatorActionsDisabled))
            <div class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-[11px] text-amber-950">
                This assessment is not yet ready for your confirmation, or a supervisor must complete the evaluation first.
            </div>
            @else
            <div class="flex flex-wrap justify-end gap-2">
                <button type="submit" data-workflow-action="save" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save as Draft</button>
                <button type="submit" data-workflow-action="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Submit for Employee Confirmation</button>
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
