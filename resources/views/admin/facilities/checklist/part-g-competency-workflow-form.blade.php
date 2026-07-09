@php
    use App\Support\AssessmentWorkflowStatus;
    $partGEmployeeAckDateValue = $selectedCompetencyAssessment?->employee_signed_at
        ? \Illuminate\Support\Carbon::parse($selectedCompetencyAssessment->employee_signed_at)->format('M j, Y g:i A')
        : '';
    $partGReviewerSignDateValue = $selectedCompetencyAssessment?->reviewer_signed_at
        ? \Illuminate\Support\Carbon::parse($selectedCompetencyAssessment->reviewer_signed_at)->format('M j, Y g:i A')
        : '';
@endphp

@if($hasAssessmentPeriod && $partGHasCompetenciesForPosition)
<div class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
    <div class="mb-4">
        <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Competency Assessment Acknowledgement</h3>
        <p class="text-[11px] text-slate-700">
            Current status: <strong>{{ $partGSubmissionStatusLabel }}</strong>
        </p>
    </div>

    @if($errors->has('employee_signature'))
    <div class="mb-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-[11px] text-red-800">{{ $errors->first('employee_signature') }}</div>
    @endif

    @if($errors->has('reviewer_signature'))
    <div class="mb-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-[11px] text-red-800">{{ $errors->first('reviewer_signature') }}</div>
    @endif

    <form id="partGCompetencyWorkflowForm" method="POST" action="{{ route('admin.employees.competency-workflow.save', ['employee' => $employee->id]) }}" enctype="multipart/form-data" @if($partGReviewerCanApprove && empty($evaluatorActionsDisabled)) data-partg-confirmation-snapshot='@json($selectedCompetencyAssessment?->employee_confirmation_snapshot)' @endif>
        @csrf
        <input type="hidden" name="assessment_period_id" value="{{ $selectedAssessmentPeriodId }}">
        <input type="hidden" name="action" id="partGWorkflowAction" value="">

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
                        type="text"
                        readonly
                        class="mt-1 w-full rounded-md border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-900"
                        value="{{ $partGEmployeeAckDateValue }}"
                        placeholder="Recorded automatically when the employee signs"
                    >
                </label>

                @if($partGReviewerSignDateValue !== '')
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-700">
                    Reviewer Sign Date
                    <input
                        type="text"
                        readonly
                        class="mt-1 w-full rounded-md border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-900"
                        value="{{ $partGReviewerSignDateValue }}"
                    >
                </label>
                @endif

                @if($partGAssessmentLocked)
                <p class="text-[11px] text-amber-900">This competency assessment is completed and read-only.</p>
                @if(empty($evaluatorActionsDisabled))
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="reopen" class="rounded-md border border-slate-400 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Reopen for Editing</button>
                </div>
                @endif
                @elseif($partGEmployeeCanConfirm && !empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-amber-950">Enter your comments, then sign to acknowledge this competency assessment.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="send_back" class="rounded-md border border-amber-400 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">Send Back to Reviewer</button>
                    <button type="button" id="partGOpenEmployeeSignatureModal" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Sign &amp; Acknowledge</button>
                </div>
                @elseif($partGReviewerCanApprove && empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-violet-950">
                    @if(!empty($partGContentChangedSinceEmployeeConfirmation))
                    This competency assessment was changed after the employee confirmed it. Resubmit to send it back to the employee. Approval is disabled until they confirm again.
                    @else
                    The employee has acknowledged and signed this competency assessment. Approve when no further changes are needed.
                    @endif
                </p>
                <div id="partGReviewerApprovalActions" class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="submit" id="partGResubmitForEmployeeBtn" class="rounded-md border border-sky-400 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-950 hover:bg-sky-100 {{ empty($partGContentChangedSinceEmployeeConfirmation) ? 'hidden' : '' }}">Resubmit for Employee Confirmation</button>
                    <button type="button" id="partGOpenReviewerSignatureModal" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black {{ !empty($partGCanApprove) ? '' : 'hidden' }}">Sign &amp; Approve Assessment</button>
                </div>
                @elseif($partGWorkflowStatus === AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION && empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-sky-950">This competency assessment was submitted for <strong>employee signature / confirmation</strong>. The employee will see a dashboard task and email notification.</p>
                @elseif($partGWorkflowStatus === AssessmentWorkflowStatus::DRAFT && empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-slate-700">When competency ratings are complete, submit the assessment for employee confirmation.</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button type="submit" data-partg-action="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Submit for Employee Confirmation</button>
                </div>
                @elseif($partGWorkflowStatus === AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL && !empty($evaluatorActionsDisabled))
                <p class="text-[11px] text-slate-600">You have signed this assessment. It is waiting for reviewer approval and completion.</p>
                @endif
            </div>
        </div>

        @include('admin.facilities.checklist.partials.part-g-employee-signature-modal', [
            'partGEmployeeFullName' => $partGEmployeeFullName ?? '',
        ])
        @include('admin.facilities.checklist.partials.part-g-reviewer-signature-modal')
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('partGCompetencyWorkflowForm');
        if (!form) return;

        var actionInput = document.getElementById('partGWorkflowAction');
        if (!actionInput) return;

        form.addEventListener('click', function(event) {
            var button = event.target.closest('[data-partg-action]');
            if (!button || !form.contains(button)) {
                return;
            }

            actionInput.value = button.getAttribute('data-partg-action') || '';
        });

        form.addEventListener('submit', function(event) {
            var submitter = event.submitter;
            if (submitter && submitter.getAttribute('data-partg-action')) {
                actionInput.value = submitter.getAttribute('data-partg-action') || '';
            }

            if (!actionInput.value && submitter && (submitter.id === 'partGOpenEmployeeSignatureModal' || submitter.id === 'partGOpenReviewerSignatureModal')) {
                event.preventDefault();
            }
        });

        document.addEventListener('partg-summary-updated', function() {
            var approveBtn = document.getElementById('partGOpenReviewerSignatureModal');
            var resubmitBtn = document.getElementById('partGResubmitForEmployeeBtn');
            if (!approveBtn || !resubmitBtn) return;

            approveBtn.classList.add('hidden');
            resubmitBtn.classList.remove('hidden');
        });

        document.addEventListener('livewire:init', function() {
            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('partg-summary-updated', function() {
                    document.dispatchEvent(new CustomEvent('partg-summary-updated'));
                });
            }
        });
    });
</script>
@endif
