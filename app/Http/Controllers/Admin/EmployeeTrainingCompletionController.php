<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BPEmployee;
use App\Models\EmployeeTrainingItem;
use App\Services\EmployeeTrainingWorkflowService;
use App\Support\PreventsSelfAssessment;
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

        // Employee self-service, or leadership helping to start/submit on behalf of staff
        if ($this->workflow->actorIsEmployee($actor, $employee)) {
            return;
        }

        if ($actor->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            return;
        }

        abort(403, 'You cannot start or submit trainings for this employee.');
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
