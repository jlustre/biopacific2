@php
    $assessmentSummaryMode = $assessmentSummaryMode ?? 'competency';
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

    @if($assessmentSummaryMode === 'competency')
    <div class="flex flex-col gap-2 lg:flex-row lg:items-stretch">
        <div class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Total Points</div>
            <input id="partGTotalScore" type="text" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
        </div>
        <div class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Average</div>
            <input id="partGAverageScore" type="text" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
        </div>
        <div id="partGOverallRatingCard" class="rounded-md border border-slate-400 bg-white px-3 py-2 shadow-sm transition-colors">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">Overall Rating</div>
            <input id="partGOverallRating" type="text" class="mt-1 w-full border-0 bg-transparent p-0 text-xl font-bold text-slate-900 focus:outline-none focus:ring-0" readonly>
        </div>
    </div>

    <div id="partGUnsatisfactoryDetailsWrapper" class="mt-2 hidden rounded-md border border-dashed border-slate-400 bg-slate-100 px-2 py-2 transition-opacity">
        <textarea id="partGUnsatisfactoryDetails" class="min-h-[56px] w-full resize-y rounded-md border border-slate-300 bg-white px-2 py-2 text-sm text-slate-900 placeholder:text-slate-500 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200 disabled:cursor-not-allowed disabled:bg-slate-50" placeholder="Describe the further action required..." disabled>{{ $selectedCompetencyAssessment?->further_action_required ?? '' }}</textarea>
    </div>

    <div class="mt-3 grid gap-3 lg:grid-cols-[1.2fr,0.8fr]">
        <div class="rounded-md border border-slate-400 bg-white p-3 shadow-sm">
            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Comments</label>
            <textarea id="partGComments" class="min-h-[96px] w-full resize-y rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 placeholder:text-slate-500 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200" placeholder="Enter comments here...">{{ $selectedCompetencyAssessment?->comments ?? '' }}</textarea>
        </div>

        @php
            $partGCurrentSubmission = $selectedCompetencyAssessment ?? null;
            $partGCurrentSubmissionData = optional($partGCurrentSubmission);
            $partGCurrentStatus = $partGCurrentSubmissionData->status ?? 'draft';
            $partGAssignment = $employee->currentAssignment;
            $partGDefaultEmployeeDisplayName = trim($employee->last_name . ', ' . $employee->first_name . ($employee->middle_name ? ' ' . $employee->middle_name : ''));
            $partGEmployeeDisplayName = $partGCurrentSubmissionData->employee_name ?: $partGDefaultEmployeeDisplayName;
            $partGEmployeeTitle = $partGCurrentSubmissionData->employee_title ?: ($partGAssignment?->position?->title ?? '');
            $partGReviewerDisplayName = $partGCurrentSubmissionData->reviewer_name ?: (!empty($reviewDate)
                ? ($reviewerName ?? (auth()->user()->name ?? ''))
                : (auth()->user()->name ?? ($reviewerName ?? '')));
            $partGReviewerTitle = $partGCurrentSubmissionData->reviewer_title ?: '';
            $partGReviewDateValue = optional($partGCurrentSubmissionData->review_date)->toDateString() ?: ($reviewDate ?? '');
            $partGEmployeeDateValue = optional($partGCurrentSubmissionData->employee_signed_at)->toDateString() ?? '';

            if ($partGReviewerTitle === '') {
                $partGReviewerTitle = $partGAssignment?->reportsToPositionTitle() ?? '';
            }
        @endphp
        <div class="rounded-md border border-slate-400 bg-white p-3 shadow-sm">
            <div class="grid gap-2.5 lg:grid-cols-3">
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Reviewer Name/Signature</label>
                    <input id="partGReviewerName" type="text" value="{{ $partGReviewerDisplayName }}" class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Reviewer Title</label>
                    <input id="partGReviewerTitle" type="text" value="{{ $partGReviewerTitle }}" class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Review Date</label>
                    <input id="partGReviewDate" type="date" value="{{ $partGReviewDateValue }}" class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div class="lg:col-span-1">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Employee Name/Signature</label>
                    <input id="partGEmployeeName" type="text" value="{{ $partGEmployeeDisplayName }}" class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div class="lg:col-span-1">
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Employee Title</label>
                    <input id="partGEmployeeTitle" type="text" value="{{ $partGEmployeeTitle }}" class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-600">Employee Date</label>
                    <input id="partGEmployeeDate" type="date" value="{{ $partGEmployeeDateValue }}" class="w-full rounded-md border border-slate-300 bg-slate-50 px-2 py-2 text-sm text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
            </div>
            <input type="hidden" id="partGWorkflowStatus" value="{{ $partGCurrentStatus }}">
            <div class="mt-3 flex flex-wrap justify-end gap-2">
                @if($partGCurrentStatus === 'completed' && $partGCurrentSubmissionData->id)
                <a href="{{ route('admin.employees.competency-assessment.pdf', $partGCurrentSubmissionData->id) }}" target="_blank" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    View Completed PDF
                </a>
                @else
                @php
                    $partGBlockEvaluatorActions = !empty($evaluatorActionsDisabled) && $partGCurrentStatus !== 'for_employee_signature';
                @endphp
                @if(($partGCurrentStatus === 'draft' || !$partGCurrentSubmission) && ! $partGBlockEvaluatorActions)
                <button type="button" id="partGSaveDraftBtn" class="rounded-md border border-slate-400 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm transition-colors hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    Save as Draft
                </button>
                @endif
                @if(! $partGBlockEvaluatorActions || $partGCurrentStatus === 'for_employee_signature')
                <button type="button" id="partGSubmitAssessmentBtn" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    @if($partGCurrentStatus === 'for_employee_signature')
                    Employee Sign Assessment
                    @elseif($partGCurrentStatus === 'for_reviewer_signature')
                    Reviewer Sign Assessment
                    @else
                    Submit Assessment
                    @endif
                </button>
                @endif
                @endif
            </div>
            <div id="partGSubmitAssessmentMessage" class="mt-2 hidden rounded-md border px-3 py-2 text-sm shadow-sm"></div>
        </div>
    </div>
    @else
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
    @endif
</div>