<div id="partF" class="tab-content">
    @php
        $hasAssessmentPeriod = !empty($selectedAssessmentPeriodId);
        $partFSelectedAssessment = $selectedPerformanceAssessment ?? ($assessment ?? null);
        $partFWorkflowStatus = $partFSelectedAssessment?->workflowStatus() ?? \App\Support\AssessmentWorkflowStatus::DRAFT;
        $partFAssessmentLocked = \App\Support\AssessmentWorkflowStatus::isLocked($partFWorkflowStatus);
        $partFStatusLabel = \App\Support\AssessmentWorkflowStatus::label($partFWorkflowStatus);
        $partFEmployeeCanConfirm = \App\Support\AssessmentWorkflowStatus::employeeCanConfirm($partFWorkflowStatus);
        $partFReviewerCanApprove = \App\Support\AssessmentWorkflowStatus::reviewerCanApprove($partFWorkflowStatus);
        $partFWaitingEmployeeConfirmation = $partFWorkflowStatus === \App\Support\AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;
        $partFPerformanceDue = \App\Support\EmployeeAssessmentPeriodCalculator::isAssessmentDue($employee);
        $partFFirstDueDate = \App\Support\EmployeeAssessmentPeriodCalculator::firstAssessmentDueDate($employee);
        $partFConfirmationService = app(\App\Services\PerformanceAssessmentConfirmationService::class);
        $partFContentChangedSinceEmployeeConfirmation = $partFSelectedAssessment
            && $partFReviewerCanApprove
            && $partFConfirmationService->hasChangedSinceEmployeeConfirmation($partFSelectedAssessment);
        $partFCanApprove = $partFReviewerCanApprove
            && empty($evaluatorActionsDisabled)
            && ! $partFContentChangedSinceEmployeeConfirmation
            && filled($partFSelectedAssessment?->employee_signature_path);
        $partFSummaryReadOnly = ! $partFPerformanceDue
            || $partFAssessmentLocked
            || ($partFWaitingEmployeeConfirmation && empty($evaluatorActionsDisabled))
            || ($partFReviewerCanApprove && !empty($evaluatorActionsDisabled));
        $partFCurrentReviewerId = auth()->id();
        $partFSelectedReviewerId = optional($partFSelectedAssessment)->assessed_by;
        $partFShowStatusWarning = $partFSelectedAssessment && (
            $partFAssessmentLocked
            || $partFEmployeeCanConfirm
            || $partFReviewerCanApprove
            || ((string) $partFSelectedReviewerId !== (string) $partFCurrentReviewerId && !empty($evaluatorActionsDisabled))
        );
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var partF = document.getElementById('partF');
            var hasMessages = false;
            if (partF && partF.querySelector('#partF-messages > *')) {
                hasMessages = true;
            }
            if (hasMessages) {
                // Show checklist tab and PART F
                localStorage.setItem('employeeTab', 'checklist');
                if (partF) partF.classList.remove('hidden');
                // Optionally scroll to the message
                var msg = partF.querySelector('#partF-messages > *');
                if (msg) msg.scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        });
    </script>
    <div class="overflow-x-auto">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-bold">PART F - EMPLOYEE PERFORMANCE APPRAISAL</h2>
            @if($partFStatusLabel)
            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ ($partFAssessmentLocked || ! $partFPerformanceDue) ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-900' }}">{{ ! $partFPerformanceDue ? 'Not Due' : ($partFAssessmentLocked ? 'Read Only' : $partFStatusLabel) }}</span>
            @endif
        </div>
        @if(! $partFPerformanceDue && $partFFirstDueDate)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-950 shadow-sm">
            <strong>Performance appraisal not yet due.</strong>
            This employee completes one year of service on {{ $partFFirstDueDate->format('F j, Y') }}.
            Continue with the competency evaluation in Part G during the first year.
        </div>
        @endif

        <div class="mb-4 grid gap-3 xl:grid-cols-2 xl:items-stretch">
            <div>
                @include('admin.facilities.checklist.employee-assessment-subject-summary', [
                    'managerId' => 'partF',
                ])
            </div>
            <div>
                @include('admin.facilities.checklist.employee-assessment-period-manager', [
                    'managerId' => 'partF',
                    'contextLabel' => 'Performance Appraisal',
                ])
            </div>
        </div>

        @if($partFStatusLabel && $partFShowStatusWarning)
        <div class="mb-4 rounded-md border {{ $partFAssessmentLocked ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-sky-300 bg-sky-50 text-sky-900' }} px-3 py-2 text-[11px] shadow-sm">
            <strong>Warning:</strong>
            @if($partFAssessmentLocked)
            A performance assessment already exists for this employee in the selected period with status <strong>{{ $partFStatusLabel }}</strong>. This loaded assessment is read-only.
            @elseif($partFEmployeeCanConfirm)
            This performance assessment is waiting for <strong>employee confirmation</strong>. The employee may enter comments and save acknowledgement.
            @elseif($partFReviewerCanApprove)
            The employee has acknowledged this assessment. It is waiting for <strong>reviewer approval</strong>.
            @else
            A performance assessment already exists for this employee in the selected period with status <strong>{{ $partFStatusLabel }}</strong>. The reviewer can still update it until it is completed.
            @endif
        </div>
        @endif

        <div class="mb-4">
            @include('admin.facilities.checklist.partials.part-f-performance-appraisal-instructions')

            <h3 class="mb-2 font-bold">PERFORMANCE AREAS</h3>
            <p class="mb-2 text-xs italic text-slate-600">
                Assess each item below. Select <strong>E</strong>, <strong>M</strong>, or <strong>B</strong> for every applicable line; ratings save when selected.
                Use <strong>Save</strong> under each section to store that section&rsquo;s comments and sync all ratings for the period.
            </p>

            @include('admin.facilities.checklist.partials.part-f-rating-legend')
        </div>

        @php
            $partFPositionTitle = $employee->currentAssignment?->position?->title;
            $partFAppraisalTemplateLabel = \App\Support\PerformanceAppraisalTemplate::displayLabelForPositionTitle($partFPositionTitle);
            $partFPositionId = $employee->currentAssignment?->position_id;
        @endphp

        @if(! $partFPositionTitle)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900 shadow-sm">
            <strong>No position assigned.</strong> Assign a job position to this employee to load the correct performance appraisal items.
        </div>
        @elseif(! $partFAppraisalTemplateLabel)
        <div class="mb-4 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900 shadow-sm">
            <strong>No appraisal template mapped.</strong> The position <strong>{{ $partFPositionTitle }}</strong> is not mapped to a performance appraisal template.
        </div>
        @else
        <div class="mb-4 rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800 shadow-sm">
            <strong>{{ $partFAppraisalTemplateLabel }}</strong>
            <span class="text-slate-600">— items for <strong>{{ $partFPositionTitle }}</strong></span>
        </div>
        @endif

        @livewire('admin.facilities.checklist.part-f-sections.performance-appraisal-areas', [
            'employeeNum' => $employee->employee_num,
            'assessmentPeriodId' => $selectedAssessmentPeriodId,
            'assessmentLocked' => ! $partFPerformanceDue
                || $partFAssessmentLocked
                || $partFEmployeeCanConfirm
                || ($partFReviewerCanApprove && !empty($evaluatorActionsDisabled))
                || (!empty($evaluatorActionsDisabled) && ! $partFEmployeeCanConfirm),
            'positionId' => $partFPositionId,
            'positionTitle' => $partFPositionTitle,
        ], key('part-f-performance-'.$employee->employee_num.'-'.($selectedAssessmentPeriodId ?? 'none').'-'.($partFPositionId ?? 'none')))

        <div id="partF-messages">
            @if(session('success'))
            <div class="mb-4 rounded-md border border-slate-400 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-800 shadow-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-800 shadow-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 shadow-sm">
                <ul class="mb-0 list-disc pl-5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if(!empty($employee->employee_num))
        <form id="areasDevelopmentForm" method="POST" action="{{ route('admin.employees.areas_development.save', ['employee' => $employee->id]) }}" enctype="multipart/form-data" @if($partFReviewerCanApprove && empty($evaluatorActionsDisabled)) data-partf-confirmation-snapshot='@json($partFSelectedAssessment?->employee_confirmation_snapshot)' @endif>
            <input type="hidden" name="assessment_period_id" value="{{ $selectedAssessmentPeriodId }}">
            <input type="hidden" name="workflow_action" id="partFWorkflowAction" value="">
            @csrf

            @include('admin.facilities.checklist.employee-assessment-summary-form', [
                'assessmentSummaryMode' => 'performance',
                'assessmentWord' => 'Performance',
                'assessmentSummaryTitle' => 'Performance Evaluation Summary',
                'assessmentSummaryDescription' => 'Capture the development notes and signatures for this performance assessment period.',
            ])

            @include('admin.facilities.checklist.partials.part-f-employee-signature-modal')
        </form>
        @endif

        <div class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">
            <div class="mb-4 flex flex-col gap-1 md:flex-row md:items-end md:justify-between">
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-wide text-slate-900">Performance Assessment History</h3>
                    <p class="text-[11px] text-slate-700">Previous Part F assessments for this employee, grouped by assessment period.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-md border border-slate-400 bg-white shadow-sm">
                <table class="min-w-full table-fixed text-[11px] text-slate-900 md:text-xs">
                    <thead class="bg-slate-200 text-slate-900">
                        <tr>
                            <th class="w-[26%] border border-slate-400 px-2 py-1.5 text-left font-semibold tracking-wide">Assessment Period</th>
                            <th class="w-[14%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Assessed Date</th>
                            <th class="w-[12%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Items Rated</th>
                            <th class="w-[12%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Total Points</th>
                            <th class="w-[12%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Average</th>
                            <th class="w-[12%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Overall</th>
                            <th class="w-[10%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Status</th>
                            <th class="w-[12%] border border-slate-400 px-2 py-1.5 text-center font-semibold tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performanceAssessmentHistory as $history)
                        @php
                            $historyIsCurrent = !empty($history['is_current']);
                            $historyIsFinalized = !empty($history['is_finalized']);
                            $historyLoadQuery = array_filter([
                                'tab' => 'checklist',
                                'checklist_tab' => 'partF',
                                'assessment_period_id' => $history['assessment_period_id'] ?? null,
                                'assessment_year' => $history['period_year'] ?? null,
                                'facility' => request('facility'),
                            ], fn ($value) => $value !== null && $value !== '');
                            $historyLoadUrl = route('admin.employees.edit', $employee->id).'?'.http_build_query($historyLoadQuery);
                            $historyActionLabel = $historyIsFinalized ? 'View' : 'Load';
                        @endphp
                        @php
                            $periodLabel = (string) ($history['period_label'] ?? '');
                            $periodParts = explode(' to ', $periodLabel);
                            $formattedPeriodLabel = $periodLabel;
                            if (count($periodParts) === 2 && !empty($periodParts[0]) && !empty($periodParts[1])) {
                                try {
                                    $formattedPeriodLabel = \Illuminate\Support\Carbon::parse($periodParts[0])->format('m-d-y')
                                        .' to '.
                                        \Illuminate\Support\Carbon::parse($periodParts[1])->format('m-d-y');
                                } catch (\Throwable) {
                                    $formattedPeriodLabel = $periodLabel;
                                }
                            }
                            $formattedAssessmentDate = !empty($history['assessment_date'])
                                ? \Illuminate\Support\Carbon::parse($history['assessment_date'])->format('m-d-y')
                                : 'N/A';
                        @endphp
                        <tr class="{{ $loop->odd ? 'bg-white' : 'bg-slate-50' }}">
                            <td class="border border-slate-400 px-2 py-1.5 whitespace-nowrap">
                                <div>{{ $formattedPeriodLabel }}</div>
                                @if($historyIsCurrent)
                                <div class="mt-1 inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700">Current</div>
                                @endif
                            </td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">{{ $formattedAssessmentDate }}</td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">{{ $history['items_count'] }}/{{ $history['total_items'] ?? $history['items_count'] }}</td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">{{ $history['total_score'] }}</td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">{{ $history['average_score'] }}</td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">{{ $history['overall_rating'] }}</td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">
                                @php
                                    $historyStatusKey = $history['status_key'] ?? 'draft';
                                    $historyStatusClass = match ($historyStatusKey) {
                                        'completed' => 'bg-emerald-100 text-emerald-900',
                                        'for_employee_confirmation' => 'bg-sky-100 text-sky-900',
                                        'for_reviewer_approval' => 'bg-violet-100 text-violet-900',
                                        default => 'bg-slate-200 text-slate-800',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $historyStatusClass }}">{{ $history['status'] }}</span>
                            </td>
                            <td class="border border-slate-400 px-2 py-1.5 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1">
                                    @if(!empty($history['can_view_pdf']) && !empty($history['performance_assessment_id']))
                                    @include('admin.facilities.checklist.partials.assessment-pdf-link', [
                                        'href' => route('admin.employees.performance-assessment.pdf', ['assessment' => $history['performance_assessment_id']]),
                                        'title' => 'View performance assessment PDF',
                                        'ariaLabel' => 'View performance assessment PDF',
                                    ])
                                    @endif
                                    @if($historyIsCurrent)
                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Loaded</span>
                                    @else
                                    <a href="{{ $historyLoadUrl }}"
                                        class="load-employee-btn inline-flex rounded-md bg-slate-700 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-white hover:bg-slate-800"
                                        title="{{ $historyIsFinalized ? 'Open this completed assessment in read-only mode' : 'Load this assessment for editing' }}">{{ $historyActionLabel }}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="border border-slate-400 bg-slate-50 px-4 py-6 text-center text-slate-700">No performance assessment history is available for this employee yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('areasDevelopmentForm');
            if (!form) return;

            var actionInput = document.getElementById('partFWorkflowAction');
            if (!actionInput) return;

            form.addEventListener('click', function(event) {
                var button = event.target.closest('button[type="submit"][data-workflow-action]');
                if (!button || !form.contains(button)) {
                    return;
                }

                actionInput.value = button.getAttribute('data-workflow-action') || '';
            });

            form.addEventListener('submit', function(event) {
                var submitter = event.submitter;
                if (submitter && submitter.getAttribute('data-workflow-action')) {
                    actionInput.value = submitter.getAttribute('data-workflow-action') || '';
                }

                if (!actionInput.value) {
                    actionInput.value = 'save';
                }

                if (submitter && submitter.hasAttribute('data-workflow-locked')) {
                    event.preventDefault();
                }
            });

            var approveBtn = document.getElementById('partFApproveAssessmentBtn');
            var resubmitBtn = document.getElementById('partFResubmitForEmployeeBtn');
            if (!approveBtn || !resubmitBtn || !form.hasAttribute('data-partf-confirmation-snapshot')) {
                return;
            }

            function setReviewerApprovalButtons(changed) {
                if (changed) {
                    approveBtn.classList.add('hidden');
                    resubmitBtn.classList.remove('hidden');
                } else {
                    resubmitBtn.classList.add('hidden');
                    approveBtn.classList.remove('hidden');
                }
            }

            function markReviewerContentChanged() {
                setReviewerApprovalButtons(true);
            }

            form.addEventListener('input', markReviewerContentChanged);
            form.addEventListener('change', markReviewerContentChanged);

            window.addEventListener('partf-summary-updated', markReviewerContentChanged);
            document.addEventListener('livewire:initialized', function() {
                if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.on('partf-summary-updated', markReviewerContentChanged);
                }
            });
        });
    </script>
</div>