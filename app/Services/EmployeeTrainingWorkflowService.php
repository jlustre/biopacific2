<?php

namespace App\Services;

use App\Mail\TrainingCompletionApprovedMail;
use App\Models\BPEmployee;
use App\Models\EmployeeTrainingCompletion;
use App\Models\EmployeeTrainingItem;
use App\Models\PersonalTask;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class EmployeeTrainingWorkflowService
{
    public function findOrCreateCompletion(
        BPEmployee $employee,
        EmployeeTrainingItem $item,
        ?int $assessmentPeriodId
    ): EmployeeTrainingCompletion {
        if ($item->requiresAssessmentPeriod() && ! $assessmentPeriodId) {
            throw new RuntimeException('An assessment period is required for '.$item->frequencyShortLabel().' trainings.');
        }

        $periodKey = EmployeeTrainingCompletion::periodKeyFor($assessmentPeriodId, $item->isHiring());

        return EmployeeTrainingCompletion::query()->firstOrCreate(
            [
                'employee_num' => $employee->employee_num,
                'employee_training_item_id' => $item->id,
                'period_key' => $periodKey,
            ],
            [
                'assessment_period_id' => $item->isHiring() ? null : $assessmentPeriodId,
                'status' => EmployeeTrainingCompletion::STATUS_NOT_STARTED,
            ]
        );
    }

    public function start(EmployeeTrainingCompletion $completion, User $actor): EmployeeTrainingCompletion
    {
        if (! in_array($completion->status, [
            EmployeeTrainingCompletion::STATUS_NOT_STARTED,
            EmployeeTrainingCompletion::STATUS_REJECTED,
        ], true)) {
            throw new RuntimeException('This training cannot be started in its current status.');
        }

        $completion->fill([
            'status' => EmployeeTrainingCompletion::STATUS_IN_PROGRESS,
            'started_at' => $completion->started_at ?? now(),
            'started_by' => $completion->started_by ?? $actor->id,
            'rejection_reason' => null,
        ])->save();

        return $completion->fresh();
    }

    public function submit(EmployeeTrainingCompletion $completion, User $actor, ?string $notes = null): EmployeeTrainingCompletion
    {
        if (! $completion->employeeCanSubmit()) {
            throw new RuntimeException('This training must be in progress (or rejected) before it can be submitted.');
        }

        $employee = BPEmployee::query()->where('employee_num', $completion->employee_num)->firstOrFail();
        $item = $completion->trainingItem ?? EmployeeTrainingItem::query()->findOrFail($completion->employee_training_item_id);

        return DB::transaction(function () use ($completion, $actor, $notes, $employee, $item) {
            $this->cancelOpenReviewTasks($completion);

            $reviewers = $this->resolveReviewers($employee);
            if ($reviewers->isEmpty()) {
                throw new RuntimeException('No DSD or supervisor is available to review this training. Contact your facility administrator.');
            }

            $primaryReviewer = $reviewers->first();
            $reviewUrl = $this->reviewTaskUrl($employee, $completion);
            $description = $this->reviewTaskDescription($employee, $item, $completion);

            $task = PersonalTask::query()->create([
                'created_by' => $actor->id,
                'assigned_to' => $primaryReviewer->id,
                'title' => 'Review training: '.$item->name,
                'description' => $description,
                'action_url' => $reviewUrl,
                'action_label' => 'Review training',
                'priority' => 'high',
                'status' => PersonalTask::STATUS_PENDING,
                'due_at' => now()->addDays(7),
            ]);

            // Additional reviewers get their own open tasks
            foreach ($reviewers->skip(1) as $reviewer) {
                PersonalTask::query()->create([
                    'created_by' => $actor->id,
                    'assigned_to' => $reviewer->id,
                    'title' => 'Review training: '.$item->name,
                    'description' => $description,
                    'action_url' => $reviewUrl,
                    'action_label' => 'Review training',
                    'priority' => 'high',
                    'status' => PersonalTask::STATUS_PENDING,
                    'due_at' => now()->addDays(7),
                ]);
            }

            $completion->fill([
                'status' => EmployeeTrainingCompletion::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'submitted_by' => $actor->id,
                'notes' => $notes ?? $completion->notes,
                'review_task_id' => $task->id,
                'rejection_reason' => null,
            ])->save();

            return $completion->fresh();
        });
    }

    /**
     * Approve a submitted training. Returns whether the employee email was sent.
     * An in-app Messages notice is always available once status is completed.
     */
    public function approve(EmployeeTrainingCompletion $completion, User $reviewer): bool
    {
        if (! $completion->reviewerCanDecide()) {
            throw new RuntimeException('Only submitted trainings can be approved.');
        }

        $employee = BPEmployee::query()
            ->with('user')
            ->where('employee_num', $completion->employee_num)
            ->firstOrFail();
        $item = $completion->trainingItem ?? EmployeeTrainingItem::query()->findOrFail($completion->employee_training_item_id);

        $completion = DB::transaction(function () use ($completion, $reviewer) {
            $completion->fill([
                'status' => EmployeeTrainingCompletion::STATUS_COMPLETED,
                'completed_at' => now(),
                'completed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'rejection_reason' => null,
            ])->save();

            $this->completeOpenReviewTasks($completion, $reviewer);

            return $completion->fresh(['reviewedByUser', 'completedByUser', 'trainingItem', 'assessmentPeriod']);
        });

        // Notify after commit so a mail failure never rolls back the approval.
        return $this->notifyEmployeeApproved($employee, $item, $completion);
    }

    public function reject(EmployeeTrainingCompletion $completion, User $reviewer, string $reason): EmployeeTrainingCompletion
    {
        if (! $completion->reviewerCanDecide()) {
            throw new RuntimeException('Only submitted trainings can be rejected.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($completion, $reviewer, $reason) {
            $completion->fill([
                'status' => EmployeeTrainingCompletion::STATUS_REJECTED,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'rejection_reason' => $reason,
                'completed_at' => null,
                'completed_by' => null,
            ])->save();

            $this->cancelOpenReviewTasks($completion);

            return $completion->fresh();
        });
    }

    /**
     * DSD users at the facility plus users in the employee's supervisory position.
     *
     * @return Collection<int, User>
     */
    public function resolveReviewers(BPEmployee $employee): Collection
    {
        $employee->loadMissing([
            'currentAssignment.facility',
            'currentAssignment.position.reportsToPosition',
            'user',
        ]);

        $facilityId = $employee->currentAssignment?->facility_id;
        $reviewers = collect();

        if ($facilityId) {
            // Prefer DSDs first — they own Part H training completion reviews.
            $dsdUsers = User::query()
                ->role('facility-dsd')
                ->where('facility_id', $facilityId)
                ->whereNotNull('email')
                ->orderBy('name')
                ->get();
            $reviewers = $reviewers->merge($dsdUsers);

            $fallbackManagers = User::query()
                ->role(['don', 'facility-admin'])
                ->where('facility_id', $facilityId)
                ->whereNotNull('email')
                ->orderBy('name')
                ->get();
            $reviewers = $reviewers->merge($fallbackManagers);
        }

        $supervisorPositionId = $employee->currentAssignment?->reports_to
            ?: $employee->currentAssignment?->position?->reports_to_position_id;

        if ($supervisorPositionId) {
            $supervisorUsers = User::query()
                ->whereHas('bpEmployee.currentAssignment', function ($query) use ($supervisorPositionId, $facilityId) {
                    $query->where('position_id', $supervisorPositionId);
                    if ($facilityId) {
                        $query->where('facility_id', $facilityId);
                    }
                })
                ->whereNotNull('email')
                ->orderBy('name')
                ->get();
            $reviewers = $reviewers->merge($supervisorUsers);
        }

        // Never assign the trainee as their own reviewer
        $traineeUserId = $employee->user?->id;

        return $reviewers
            ->unique('id')
            ->reject(fn (User $user) => $traineeUserId && (int) $user->id === (int) $traineeUserId)
            ->values();
    }

    public function actorIsEmployee(User $actor, BPEmployee $employee): bool
    {
        $actorEmployee = method_exists($actor, 'resolvedBpEmployee')
            ? $actor->resolvedBpEmployee()
            : null;

        return $actorEmployee
            && (string) $actorEmployee->employee_num === (string) $employee->employee_num;
    }

    public function actorCanReview(User $actor, BPEmployee $employee): bool
    {
        if ($this->actorIsEmployee($actor, $employee)) {
            return false;
        }

        if ($actor->hasRole(['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'])) {
            return true;
        }

        return $this->resolveReviewers($employee)->contains(fn (User $user) => (int) $user->id === (int) $actor->id);
    }

    protected function reviewTaskUrl(BPEmployee $employee, EmployeeTrainingCompletion $completion): string
    {
        return route('admin.employees.edit', $employee->id).'?'.http_build_query(array_filter([
            'tab' => 'checklist',
            'checklist_tab' => 'partH',
            'assessment_period_id' => $completion->assessment_period_id,
        ]));
    }

    protected function reviewTaskDescription(BPEmployee $employee, EmployeeTrainingItem $item, EmployeeTrainingCompletion $completion): string
    {
        $name = trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) ?: $employee->employee_num;
        $scope = $item->isHiring()
            ? 'Hiring (one-time)'
            : $item->frequencyShortLabel().' · assessment period #'.($completion->assessment_period_id ?? '—');
        $marker = $this->reviewTaskMarker($completion);

        return "{$marker}\nEmployee {$name} (#{$employee->employee_num}) submitted \"{$item->name}\" for completion review ({$scope}). Open Part H to approve or return it.";
    }

    protected function reviewTaskMarker(EmployeeTrainingCompletion $completion): string
    {
        return '[training_completion_id:'.(int) $completion->id.']';
    }

    protected function openReviewTasksQuery(EmployeeTrainingCompletion $completion)
    {
        $marker = $this->reviewTaskMarker($completion);

        return PersonalTask::query()
            ->where('status', PersonalTask::STATUS_PENDING)
            ->where(function ($query) use ($completion, $marker) {
                if ($completion->review_task_id) {
                    $query->whereKey($completion->review_task_id);
                }
                $query->orWhere('description', 'like', '%'.$marker.'%');
            });
    }

    protected function cancelOpenReviewTasks(EmployeeTrainingCompletion $completion): void
    {
        $this->openReviewTasksQuery($completion)->update([
            'status' => PersonalTask::STATUS_CANCELLED,
        ]);
    }

    protected function completeOpenReviewTasks(EmployeeTrainingCompletion $completion, User $reviewer): void
    {
        $tasks = $this->openReviewTasksQuery($completion)->get();

        foreach ($tasks as $task) {
            if ((int) $task->assigned_to === (int) $reviewer->id) {
                $task->update([
                    'status' => PersonalTask::STATUS_CONFIRMED,
                    'completed_at' => now(),
                    'completed_by' => $reviewer->id,
                    'confirmed_at' => now(),
                    'confirmed_by' => $reviewer->id,
                ]);
            } else {
                $task->update(['status' => PersonalTask::STATUS_CANCELLED]);
            }
        }
    }

    /**
     * Email the employee that their training was approved, and mirror that in Messages.
     */
    public function notifyEmployeeApproved(
        BPEmployee $employee,
        EmployeeTrainingItem $item,
        EmployeeTrainingCompletion $completion
    ): bool {
        $employee->loadMissing('user');

        $email = trim((string) ($employee->email ?: $employee->user?->email ?: ''));
        if ($email === '') {
            Log::warning('Training approval email skipped: employee has no email.', [
                'employee_num' => $employee->employee_num,
                'training_item_id' => $item->id,
                'completion_id' => $completion->id,
            ]);

            return false;
        }

        $periodLabel = $item->isHiring()
            ? 'one-time (hiring) requirement — completed permanently'
            : ($item->frequencyShortLabel().' — '.(optional($completion->assessmentPeriod)->displayDateRange() ?: 'the selected assessment period'));

        try {
            Mail::to($email)->send(new TrainingCompletionApprovedMail(
                $employee,
                $item,
                $completion,
                $periodLabel
            ));

            return true;
        } catch (\Throwable $e) {
            report($e);
            Log::error('Training approval email failed.', [
                'employee_num' => $employee->employee_num,
                'email' => $email,
                'training_item_id' => $item->id,
                'completion_id' => $completion->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
