<div id="partH" class="tab-content">
@php
    $hasAssessmentPeriod = ! empty($selectedAssessmentPeriodId);
    $hiringTrainings = ($employeeTrainingItems ?? collect())->where('frequency', \App\Models\EmployeeTrainingItem::FREQUENCY_HIRING)->values();
    $recurringTrainings = ($employeeTrainingItems ?? collect())->where('frequency', '!=', \App\Models\EmployeeTrainingItem::FREQUENCY_HIRING)->values();
    $hireCompletions = $employeeTrainingHireCompletions ?? collect();
    $periodCompletions = $employeeTrainingPeriodCompletions ?? collect();
    $latestCompletedAt = $employeeTrainingLatestCompletedAt ?? collect();

    $actor = auth()->user();
    $workflow = app(\App\Services\EmployeeTrainingWorkflowService::class);
    $isSelf = $actor ? $workflow->actorIsEmployee($actor, $employee) : false;
    $canReview = $actor ? $workflow->actorCanReview($actor, $employee) : false;
    $canActAsEmployee = $isSelf;

    $statusBadge = function (?\App\Models\EmployeeTrainingCompletion $completion, ?array $due = null, bool $satisfiedFromPrior = false): array {
        if ($satisfiedFromPrior) {
            return [$due['status_hint'] ?? 'Current', 'bg-emerald-100 text-emerald-900 border-emerald-300'];
        }
        $status = $completion?->status ?? \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED;
        return match ($status) {
            \App\Models\EmployeeTrainingCompletion::STATUS_IN_PROGRESS => ['In progress', 'bg-sky-100 text-sky-900 border-sky-300'],
            \App\Models\EmployeeTrainingCompletion::STATUS_SUBMITTED => ['Submitted for review', 'bg-amber-100 text-amber-950 border-amber-300'],
            \App\Models\EmployeeTrainingCompletion::STATUS_REJECTED => ['Rejected — revise', 'bg-rose-100 text-rose-900 border-rose-300'],
            \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED => ['Completed', 'bg-emerald-100 text-emerald-900 border-emerald-300'],
            \App\Models\EmployeeTrainingCompletion::STATUS_NA => ['N/A', 'bg-slate-100 text-slate-700 border-slate-300'],
            default => ['Not started', 'bg-slate-100 text-slate-700 border-slate-300'],
        };
    };
@endphp

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <h2 class="text-xl font-bold">PART H — TRAINING PROGRESS</h2>
    </div>

    <p class="mb-4 text-sm text-slate-600">
        Default status is <strong>Not started</strong>. The employee starts the module (<strong>In progress</strong>), then
        <strong>submits for completion</strong>. DSD or supervisors approve (complete) or reject (return to employee).
        Hiring trainings complete permanently once approved; recurring trainings (annual, every 2 years, etc.) complete for the
        selected assessment period and remain current until the next due date.
    </p>

    <div class="mb-4 grid gap-3 xl:grid-cols-2 xl:items-stretch">
        <div>
            @include('admin.facilities.checklist.employee-assessment-subject-summary', [
                'managerId' => 'partH',
            ])
        </div>
        <div>
            @include('admin.facilities.checklist.employee-assessment-period-manager', [
                'managerId' => 'partH',
                'contextLabel' => 'Recurring Trainings',
            ])
        </div>
    </div>

    @if(session('success') && request('checklist_tab') === 'partH')
    <div class="mb-4 rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">{{ session('success') }}</div>
    @endif
    @if(session('error') && request('checklist_tab') === 'partH')
    <div class="mb-4 rounded-md border border-rose-300 bg-rose-50 px-3 py-2 text-sm text-rose-900">{{ session('error') }}</div>
    @endif

    @if($isSelf)
    <div class="mb-4 rounded-md border border-sky-300 bg-sky-50 px-3 py-2 text-sm text-sky-950">
        Open each module, mark it In Progress, then submit for review when finished. Your DSD or supervisor will approve or return it.
    </div>
    @elseif($canReview)
    <div class="mb-4 rounded-md border border-teal-300 bg-teal-50 px-3 py-2 text-sm text-teal-950">
        Submitted trainings appear below for your approval or rejection. Approving notifies the employee and marks the item complete.
    </div>
    @endif

    {{-- Hiring (one-time) --}}
    <div class="mb-6 overflow-x-auto rounded-xl border border-teal-200 shadow-sm">
        <div class="bg-teal-700 px-4 py-2 text-sm font-semibold text-teal-50">Upon hiring (one-time)</div>
        <table class="min-w-full divide-y divide-teal-100 text-sm">
            <thead class="bg-teal-800 text-teal-50">
                <tr>
                    <th class="px-3 py-2 text-left">Training</th>
                    <th class="w-24 px-3 py-2 text-left">Module</th>
                    <th class="w-40 px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                    <th class="w-44 px-3 py-2 text-left">History</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hiringTrainings as $item)
                    @php
                        $completion = $hireCompletions->get($item->id);
                        [$label, $badgeClass] = $statusBadge($completion);
                        $moduleUrl = $item->resolvedContentUrl();
                        $periodId = null;
                        $canStart = $canActAsEmployee && (! $completion || $completion->employeeCanStart());
                        $canSubmit = $canActAsEmployee && $completion && $completion->employeeCanSubmit();
                        $canDecide = $canReview && $completion && $completion->reviewerCanDecide();
                        $canAssignTask = $canReview && (! $completion || $completion->status === \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED);
                        $actionsLocked = false;
                    @endphp
                    @include('admin.facilities.checklist.partials.training-workflow-row', [
                        'item' => $item,
                        'employee' => $employee,
                        'completion' => $completion,
                        'label' => $label,
                        'badgeClass' => $badgeClass,
                        'moduleUrl' => $moduleUrl,
                        'periodId' => $periodId,
                        'canStart' => $canStart,
                        'canSubmit' => $canSubmit,
                        'canDecide' => $canDecide,
                        'canAssignTask' => $canAssignTask,
                        'actionsLocked' => $actionsLocked,
                        'rowEven' => $loop->even,
                    ])
                @empty
                    <tr><td colspan="5" class="px-3 py-4 text-center text-slate-500">No hiring trainings assigned to this position.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Recurring (annual / biennial / …) --}}
    <div class="mb-4 overflow-x-auto rounded-xl border border-teal-200 shadow-sm">
        <div class="bg-teal-700 px-4 py-2 text-sm font-semibold text-teal-50">Recurring (assessment period / anniversary)</div>
        @if(! $hasAssessmentPeriod)
        <div class="bg-amber-50 px-4 py-3 text-sm text-amber-900">Select or create an assessment period above to work on recurring trainings.</div>
        @endif
        <table class="min-w-full divide-y divide-teal-100 text-sm">
            <thead class="bg-teal-800 text-teal-50">
                <tr>
                    <th class="px-3 py-2 text-left">Training</th>
                    <th class="w-24 px-3 py-2 text-left">Module</th>
                    <th class="w-40 px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                    <th class="w-44 px-3 py-2 text-left">History</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recurringTrainings as $item)
                    @php
                        $completion = $periodCompletions->get($item->id);
                        $lastCompletedAt = $latestCompletedAt->get((int) $item->id)
                            ?? ($completion?->status === \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED ? $completion->completed_at : null);
                        $due = $item->evaluateDue($lastCompletedAt ? \Carbon\Carbon::parse($lastCompletedAt) : null);
                        $satisfiedFromPrior = ! $completion && ! $due['due'];
                        [$label, $badgeClass] = $statusBadge($completion, $due, $satisfiedFromPrior);
                        $moduleUrl = $item->resolvedContentUrl();
                        $periodId = $selectedAssessmentPeriodId;
                        $actionsLocked = ! $hasAssessmentPeriod;
                        $canStart = ! $actionsLocked && ! $satisfiedFromPrior && $canActAsEmployee && (! $completion || $completion->employeeCanStart());
                        $canSubmit = ! $actionsLocked && ! $satisfiedFromPrior && $canActAsEmployee && $completion && $completion->employeeCanSubmit();
                        $canDecide = ! $actionsLocked && $canReview && $completion && $completion->reviewerCanDecide();
                        $canAssignTask = ! $actionsLocked && ! $satisfiedFromPrior && $canReview
                            && (! $completion || $completion->status === \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED);
                    @endphp
                    @include('admin.facilities.checklist.partials.training-workflow-row', [
                        'item' => $item,
                        'employee' => $employee,
                        'completion' => $completion,
                        'label' => $label,
                        'badgeClass' => $badgeClass,
                        'moduleUrl' => $moduleUrl,
                        'periodId' => $periodId,
                        'canStart' => $canStart,
                        'canSubmit' => $canSubmit,
                        'canDecide' => $canDecide,
                        'canAssignTask' => $canAssignTask,
                        'actionsLocked' => $actionsLocked,
                        'satisfiedFromPrior' => $satisfiedFromPrior,
                        'frequencyLabel' => $item->frequencyShortLabel(),
                        'rowEven' => $loop->even,
                        'defaultDueDate' => ($due['next_due_at'] ?? null)?->format('Y-m-d')
                            ?? (! empty($selectedAssessmentPeriod)
                                ? \App\Support\ComplianceDueDate::forPeriod($selectedAssessmentPeriod)?->format('Y-m-d')
                                : null)
                            ?? now()->addDays(\App\Support\ComplianceDueDate::offsetDays())->format('Y-m-d'),
                    ])
                @empty
                    <tr><td colspan="5" class="px-3 py-4 text-center text-slate-500">No recurring trainings assigned to this position.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($canReview)
    <div id="trainingTaskModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="trainingTaskModalTitle">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 id="trainingTaskModalTitle" class="text-lg font-bold text-slate-900">Assign training task</h3>
                    <p class="mt-1 text-xs text-slate-600">
                        Assign to {{ $employee->formalName() }} and send the same message by email.
                    </p>
                </div>
                <button type="button" class="js-close-training-task-modal text-2xl leading-none text-slate-400 hover:text-slate-700" aria-label="Close">&times;</button>
            </div>
            <form id="trainingTaskForm" method="POST" class="space-y-4 px-5 py-4">
                @csrf
                <input type="hidden" name="assessment_period_id" id="trainingTaskPeriodId">
                <div>
                    <label for="trainingTaskTitle" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Task title</label>
                    <input id="trainingTaskTitle" name="title" type="text" maxlength="255" required
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="trainingTaskMessage" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Message</label>
                    <textarea id="trainingTaskMessage" name="message" rows="5" maxlength="2000" required
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="trainingTaskPriority" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Priority</label>
                        <select id="trainingTaskPriority" name="priority" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high" selected>High</option>
                        </select>
                    </div>
                    <div>
                        <label for="trainingTaskDueDate" class="block text-xs font-semibold uppercase tracking-wide text-slate-700">Due date</label>
                        <input id="trainingTaskDueDate" name="due_date" type="date"
                            class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" class="js-close-training-task-modal rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="rounded-md bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800">
                        Assign task &amp; send message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('trainingTaskModal');
        const form = document.getElementById('trainingTaskForm');
        if (!modal || !form) return;

        const closeModal = function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.querySelectorAll('.js-open-training-task-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                form.action = button.dataset.action;
                document.getElementById('trainingTaskTitle').value = button.dataset.defaultTitle || '';
                document.getElementById('trainingTaskMessage').value = button.dataset.defaultMessage || '';
                document.getElementById('trainingTaskDueDate').value = button.dataset.defaultDueDate || '';
                document.getElementById('trainingTaskPeriodId').value = button.dataset.periodId || '';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.getElementById('trainingTaskTitle').focus();
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });
        document.querySelectorAll('.js-close-training-task-modal').forEach(function (button) {
            button.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    });
    </script>
    @endif
</div>
