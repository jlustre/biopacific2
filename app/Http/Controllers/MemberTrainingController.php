<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTrainingItem;
use App\Services\EmployeeTrainingWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberTrainingController extends Controller
{
    public function __construct(
        protected EmployeeTrainingWorkflowService $workflow
    ) {}

    public function start(Request $request, EmployeeTrainingItem $trainingItem)
    {
        $user = Auth::user();
        $employee = $user?->resolvedBpEmployee();
        if (! $employee) {
            return redirect()->route('member.checklists')->with('error', 'No employee record is linked to your account.');
        }

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        $completion = $this->workflow->findOrCreateCompletion($employee, $trainingItem, $periodId);
        $this->workflow->start($completion, $user);

        return $this->back($request, $periodId, 'Training marked In Progress. Finish the module, then submit it for review.');
    }

    public function submit(Request $request, EmployeeTrainingItem $trainingItem)
    {
        $user = Auth::user();
        $employee = $user?->resolvedBpEmployee();
        if (! $employee) {
            return redirect()->route('member.checklists')->with('error', 'No employee record is linked to your account.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'assessment_period_id' => ['nullable', 'integer', 'exists:employee_assessment_periods,id'],
        ]);

        $periodId = $this->periodIdFromRequest($request, $trainingItem);
        $completion = $this->workflow->findOrCreateCompletion($employee, $trainingItem, $periodId);

        if ($completion->status === \App\Models\EmployeeTrainingCompletion::STATUS_NOT_STARTED) {
            $this->workflow->start($completion, $user);
            $completion->refresh();
        }

        try {
            $this->workflow->submit($completion, $user, $validated['notes'] ?? null);
        } catch (\RuntimeException $e) {
            return $this->back($request, $periodId, null, $e->getMessage());
        }

        return $this->back(
            $request,
            $periodId,
            'Submitted for completion. Your DSD or supervisor will approve or return it.'
        );
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

    protected function back(Request $request, ?int $periodId, ?string $success = null, ?string $error = null)
    {
        $query = array_filter(['assessment_period_id' => $periodId]);
        $redirect = redirect()->to(route('member.checklists', $query));

        if ($error) {
            return $redirect->with('error', $error);
        }

        return $redirect->with('success', $success ?? 'Saved.');
    }
}
