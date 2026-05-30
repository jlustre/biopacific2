@php
    use App\Support\AssessmentWorkflowStatus;
@endphp

@if($hasAssessmentPeriod && $partGHasCompetenciesForPosition)
<div class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
    <div class="mb-4">
        <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment Acknowledgement</h3>
        <p class="text-[11px] text-slate-700">
            Current status: <strong>{{ $partGSubmissionStatusLabel }}</strong>
        </p>
    </div>

    <form method="POST" action="{{ route('admin.employees.competency-workflow.save', ['employee' => $employee->id]) }}">
        @csrf
        <input type="hidden" name="assessment_period_id" value="{{ $selectedAssessmentPeriodId }}">

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.95fr)]">
            <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
                <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee Comments</label>
                <textarea
                    name="employee_comments"
                    rows="4"
                    class="min-h-[88px] w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900"
                    @readonly(! $partGEmployeeCanConfirm || empty($evaluatorActionsDisabled))
                >{{ old('employee_comments', $partGCompetencyEmployeeComments) }}</textarea>
            </div>

            <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Employee Acknowledge Date
                    <input
                        type="date"
                        name="employee_acknowledge_dt"
                        class="mt-1 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900"
                        value="{{ old('employee_acknowledge_dt', $partGCompetencyEmployeeAckDate) }}"
                        @readonly(! $partGEmployeeCanConfirm || empty($evaluatorActionsDisabled))
                    >
                </label>

                @if($partGAssessmentLocked)
                <p class="text-[11px] text-amber-900">This competency assessment is completed and read-only.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    @if(!empty($evaluatorActionsDisabled))
                    <button type="submit" name="action" value="send_back" class="rounded-md border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">Send Back to Reviewer</button>
                    @else
                    <button type="submit" name="action" value="reopen" class="rounded-md border border-slate-400 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Reopen for Editing</button>
                    @endif
                </div>
                @elseif($partGEmployeeCanConfirm && !empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-amber-950">Enter your comments and save acknowledgement to send this assessment for reviewer approval.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" name="action" value="send_back" class="rounded-md border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">Send Back to Reviewer</button>
                    <button type="submit" name="action" value="acknowledge" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save Acknowledgement</button>
                </div>
                @elseif($partGReviewerCanApprove && empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-slate-700">The employee has acknowledged this competency assessment. Approve it to mark the period complete.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" name="action" value="approve" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Approve Assessment</button>
                </div>
                @elseif($partGWorkflowStatus === \App\Support\AssessmentWorkflowStatus::DRAFT && empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-slate-700">When competency ratings are complete, submit the assessment for employee confirmation.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" name="action" value="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Submit for Employee Confirmation</button>
                </div>
                @elseif($partGEmployeeCanConfirm || $partGReviewerCanApprove)
                <p class="text-[11px] text-slate-600">This step is waiting for the employee or reviewer to complete their action.</p>
                @endif
            </div>
        </div>
    </form>
</div>
@endif
