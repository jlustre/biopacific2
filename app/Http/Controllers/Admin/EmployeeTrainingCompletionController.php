<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BPEmployee;
use App\Models\EmployeeTrainingCompletion;
use App\Models\EmployeeTrainingItem;
use App\Services\EmployeeAssessmentPeriodService;
use App\Services\EmployeeTrainingWorkflowService;
use App\Support\PreventsSelfAssessment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeTrainingCompletionController extends Controller
{
    public function __construct(
        protected EmployeeTrainingWorkflowService $workflow
    ) {}

    public function start(Request $request, $employee, EmployeeTrainingItem $trainingItem)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $actor = $request->user();
        $this->assertCanActAsEmployee($actor, $employeeModel);

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        $completion = $this->workflow->findOrCreateCompletion($employeeModel, $trainingItem, $periodId);
        $this->workflow->start($completion, $actor);

        return $this->redirectToPartH($employeeModel, $periodId, 'Training marked In Progress. Complete the module, then submit for review.');
    }

    public function submit(Request $request, $employee, EmployeeTrainingItem $trainingItem)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $actor = $request->user();
        $this->assertCanActAsEmployee($actor, $employeeModel);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'assessment_period_id' => ['nullable', 'integer', 'exists:employee_assessment_periods,id'],
        ]);

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        $completion = $this->workflow->findOrCreateCompletion($employeeModel, $trainingItem, $periodId);

        if ($completion->status === \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED) {
            $this->workflow->start($completion, $actor);
            $completion->refresh();
        }

        try {
            $this->workflow->submit($completion, $actor, $validated['notes'] ?? null);
        } catch (\RuntimeException $e) {
            return $this->redirectToPartH($employeeModel, $periodId, null, $e->getMessage());
        }

        return $this->redirectToPartH(
            $employeeModel,
            $periodId,
            'Training submitted for review. DSD / supervisors have been assigned a task.'
        );
    }

    public function approve(Request $request, $employee, EmployeeTrainingItem $trainingItem)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $actor = $request->user();

        if (! $this->workflow->actorCanReview($actor, $employeeModel)) {
            abort(403, 'Only DSD, supervisors, or facility leadership can approve trainings.');
        }

        if (PreventsSelfAssessment::isSelfAssessment($actor, $employeeModel)) {
            return $this->redirectToPartH($employeeModel, $request->integer('assessment_period_id') ?: null, null, PreventsSelfAssessment::DEFAULT_MESSAGE);
        }

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        $completion = $this->workflow->findOrCreateCompletion($employeeModel, $trainingItem, $periodId);

        try {
            $notifiedByEmail = $this->workflow->approve($completion, $actor);
        } catch (\RuntimeException $e) {
            return $this->redirectToPartH($employeeModel, $periodId, null, $e->getMessage());
        }

        $scope = $trainingItem->isHiring()
            ? 'This hiring training is now permanently complete.'
            : 'This '.$trainingItem->frequencyShortLabel().' training is complete for the selected assessment period.';

        $notifyNote = $notifiedByEmail
            ? ' The employee has been emailed a completion notice and can also see it in Messages.'
            : ' A completion notice is available in the employee\'s Messages. Email could not be sent (no address on file or mail delivery failed).';

        return $this->redirectToPartH($employeeModel, $periodId, 'Training approved. '.$scope.$notifyNote);
    }

    public function reject(Request $request, $employee, EmployeeTrainingItem $trainingItem)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $actor = $request->user();

        if (! $this->workflow->actorCanReview($actor, $employeeModel)) {
            abort(403, 'Only DSD, supervisors, or facility leadership can reject trainings.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
            'assessment_period_id' => ['nullable', 'integer', 'exists:employee_assessment_periods,id'],
        ]);

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        $completion = $this->workflow->findOrCreateCompletion($employeeModel, $trainingItem, $periodId);

        try {
            $this->workflow->reject($completion, $actor, $validated['rejection_reason']);
        } catch (\RuntimeException $e) {
            return $this->redirectToPartH($employeeModel, $periodId, null, $e->getMessage());
        }

        return $this->redirectToPartH(
            $employeeModel,
            $periodId,
            'Training returned to the employee for revision.'
        );
    }

    public function assignTask(Request $request, $employee, EmployeeTrainingItem $trainingItem)
    {
        $employeeModel = $this->resolveEmployee($employee);
        $actor = $request->user();
        abort_unless($actor && $this->workflow->actorCanReview($actor, $employeeModel), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assessment_period_id' => ['nullable', 'integer', 'exists:employee_assessment_periods,id'],
        ]);

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        if ($periodId) {
            app(EmployeeAssessmentPeriodService::class)->assertPeriodBelongsToEmployee(
                $periodId,
                $employeeModel->employee_num
            );
        }

        $positionId = $employeeModel->currentAssignment?->position_id;
        $positionIds = $trainingItem->position_ids ?? [];
        abort_unless(
            $trainingItem->is_active
            && ($trainingItem->appliesToEveryone()
                || in_array($positionId, $positionIds, true)
                || in_array((string) $positionId, $positionIds, true)),
            422,
            'This training is not assigned to the employee\'s current position.'
        );

        $periodKey = EmployeeTrainingCompletion::periodKeyFor($periodId, $trainingItem->isHiring());
        $completion = EmployeeTrainingCompletion::query()
            ->where('employee_num', $employeeModel->employee_num)
            ->where('employee_training_item_id', $trainingItem->id)
            ->where('period_key', $periodKey)
            ->first();
        abort_if(
            $completion && $completion->status !== EmployeeTrainingCompletion::STATUS_NOT_STARTED,
            422,
            'A task can be assigned only while this training has not been started.'
        );

        try {
            $result = $this->workflow->assignTaskToEmployee(
                $employeeModel,
                $trainingItem,
                $actor,
                $validated['title'],
                $validated['message'],
                $validated['priority'],
                filled($validated['due_date'] ?? null) ? Carbon::parse($validated['due_date'])->endOfDay() : null,
                $periodId,
            );
        } catch (\RuntimeException $exception) {
            return $this->redirectToPartH($employeeModel, $periodId, null, $exception->getMessage());
        }

        $message = 'Training task assigned. It is now visible in the employee\'s My Tasks and My Messages.';
        $message .= $result['email_sent']
            ? ' An email was also sent.'
            : ' No email was sent, but the portal task was created.';

        return $this->redirectToPartH($employeeModel, $periodId, $message);
    }

    protected function resolveEmployee($employee): BPEmployee
    {
        return BPEmployee::query()
            ->where('id', $employee)
            ->orWhere('employee_num', $employee)
            ->firstOrFail();
    }

    protected function periodIdFromRequest(Request $request, EmployeeTrainingItem $item): ?int
    {
        if ($item->isHiring()) {
            return null;
        }

        $periodId = $request->filled('assessment_period_id')
            ? (int) $request->input('assessment_period_id')
            : null;

        if (! $periodId) {
            abort(422, 'Select an assessment period before working on recurring trainings.');
        }

        return $periodId;
    }

    protected function assertCanActAsEmployee(?\App\Models\User $actor, BPEmployee $employee): void
    {
        if (! $actor) {
            abort(403);
        }

        if ($this->workflow->actorIsEmployee($actor, $employee)) {
            return;
        }

        abort(403, 'Only the employee can start or submit their own training.');
    }

    protected function redirectToPartH(BPEmployee $employee, ?int $periodId, ?string $success = null, ?string $error = null)
    {
        $query = array_filter([
            'tab' => 'checklist',
            'checklist_tab' => 'partH',
            'assessment_period_id' => $periodId,
        ]);

        $redirect = redirect()->to(route('admin.employees.edit', $employee->id).'?'.http_build_query($query));

        if ($error) {
            return $redirect->with('error', $error);
        }

        return $redirect->with('success', $success ?? 'Saved.');
    }
}
