@php
    use App\Support\AssessmentWorkflowStatus;
    use App\Support\PartGAcknowledgementViewData;

    $partGAck = PartGAcknowledgementViewData::build(
        $employeeNum,
        $assessmentPeriodId,
    );
@endphp

@if($partGAck)
<div class="mt-6 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
    <div class="mb-4">
        <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment Acknowledgement</h3>
        <p class="text-[11px] text-slate-700">
            Current status: <strong>{{ $partGAck['statusLabel'] }}</strong>
        </p>
    </div>

    @if($errors->has('employee_signature'))
    <div class="mb-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-[11px] text-red-800">{{ $errors->first('employee_signature') }}</div>
    @endif

    @if($errors->has('reviewer_signature'))
    <div class="mb-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-[11px] text-red-800">{{ $errors->first('reviewer_signature') }}</div>
    @endif

    <form
        id="partGCompetencyWorkflowForm-{{ $acknowledgementKey }}"
        data-partg-workflow-form="{{ $acknowledgementKey }}"
        method="POST"
        action="{{ route('admin.employees.competency-workflow.save', ['employee' => $partGAck['employee']->id]) }}"
        enctype="multipart/form-data"
        @if($partGAck['reviewerCanApprove'] && ! $partGAck['evaluatorActionsDisabled'])
            data-partg-confirmation-snapshot='@json($partGAck['confirmationSnapshot'])'
        @endif
    >
        @csrf
        <input type="hidden" name="assessment_period_id" value="{{ $assessmentPeriodId }}">
        <input type="hidden" name="action" id="partGWorkflowAction-{{ $acknowledgementKey }}" value="">
        <input type="hidden" name="reviewer_signature_data" id="partGReviewerSignatureData-{{ $acknowledgementKey }}" value="">
        <input type="hidden" name="employee_signature_data" id="partGEmployeeSignatureData-{{ $acknowledgementKey }}" value="">

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.95fr)]">
            <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
                <label class="text-[11px] font-semibold uppercase tracking-wide text-slate-700">Employee Comments</label>
                <textarea
                    name="employee_comments"
                    rows="4"
                    class="min-h-[88px] w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-900"
                    @readonly(! $partGAck['employeeCanConfirm'] || ! $partGAck['evaluatorActionsDisabled'])
                >{{ old('employee_comments', $partGAck['employeeCommentsValue']) }}</textarea>
            </div>

            <div class="space-y-3 rounded-md border border-slate-400 bg-white p-3 shadow-sm">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Employee Acknowledge Date
                    <input
                        type="text"
                        readonly
                        class="mt-1 w-full rounded-md border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-900"
                        value="{{ $partGAck['employeeAckDateValue'] }}"
                        placeholder="Recorded automatically when the employee signs"
                    >
                </label>

                @if($partGAck['reviewerSignDateValue'] !== '')
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Reviewer Sign Date
                    <input
                        type="text"
                        readonly
                        class="mt-1 w-full rounded-md border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-900"
                        value="{{ $partGAck['reviewerSignDateValue'] }}"
                    >
                </label>
                @endif

                @if($partGAck['assessmentLocked'])
                <p class="text-[11px] text-amber-900">This competency assessment is completed and read-only.</p>
                @if(! $partGAck['evaluatorActionsDisabled'])
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="reopen" class="rounded-md border border-slate-400 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Reopen for Editing</button>
                </div>
                @endif
                @elseif($partGAck['employeeCanConfirm'] && $partGAck['evaluatorActionsDisabled'])
                <p class="text-[11px] text-amber-950">Enter your comments, then sign to acknowledge this competency assessment.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="send_back" class="rounded-md border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">Send Back to Reviewer</button>
                    <button
                        type="button"
                        class="partg-open-employee-signature-modal rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                        data-partg-form="{{ $acknowledgementKey }}"
                    >Sign &amp; Acknowledge</button>
                </div>
                @elseif($partGAck['reviewerCanApprove'] && ! $partGAck['evaluatorActionsDisabled'])
                <p class="text-[11px] text-violet-950">
                    @if($partGAck['contentChangedSinceEmployeeConfirmation'])
                    This competency assessment was changed after the employee confirmed it. Use <strong>Resubmit for Employee Confirmation</strong> to send it back to the employee.
                    @else
                    The employee has signed this competency assessment. Use <strong>Complete Assessment</strong> to add your signature and mark it completed.
                    @endif
                </p>
                <div class="partg-reviewer-approval-actions flex flex-wrap justify-end gap-2" data-partg-form="{{ $acknowledgementKey }}">
                    <button
                        type="submit"
                        data-partg-action="submit"
                        class="partg-resubmit-for-employee-btn rounded-md border border-sky-400 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-950 hover:bg-sky-100 {{ $partGAck['contentChangedSinceEmployeeConfirmation'] ? '' : 'hidden' }}"
                    >Resubmit for Employee Confirmation</button>
                    <button
                        type="button"
                        class="partg-open-reviewer-signature-modal rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black {{ $partGAck['canApprove'] ? '' : 'hidden' }}"
                        data-partg-form="{{ $acknowledgementKey }}"
                    >Complete Assessment</button>
                </div>
                @elseif($partGAck['workflowStatus'] === AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION && ! $partGAck['evaluatorActionsDisabled'])
                <p class="text-[11px] text-sky-950">This competency assessment was submitted for <strong>employee signature / confirmation</strong>. The employee will see a dashboard task and email notification.</p>
                @elseif($partGAck['workflowStatus'] === AssessmentWorkflowStatus::DRAFT && ! $partGAck['evaluatorActionsDisabled'])
                    @if($partGAck['canSubmitForEmployeeConfirmation'])
                <p class="text-[11px] text-slate-700">All competency sections are complete. Send the full assessment to the employee for confirmation.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Submit for Employee Confirmation</button>
                </div>
                    @else
                <p class="text-[11px] text-slate-700">Use <strong>Submit Section</strong> in each competency section above. When every required section is submitted or excluded, <strong>Submit for Employee Confirmation</strong> will appear here.</p>
                    @endif
                @elseif($partGAck['employeeCanConfirm'] || $partGAck['reviewerCanApprove'])
                <p class="text-[11px] text-slate-600">This step is waiting for the employee or reviewer to complete their action.</p>
                @endif
            </div>
        </div>
    </form>
</div>
@endif
