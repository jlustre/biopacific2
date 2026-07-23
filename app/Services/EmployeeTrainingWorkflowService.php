<?php

namespace App\Services;

use App\Mail\TrainingTaskAssignedMail;
use App\Mail\TrainingCompletionApprovedMail;
use App\Models\BPEmployee;
use App\Models\EmployeeTrainingCompletion;
use App\Models\EmployeeTrainingItem;
use App\Models\PersonalTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
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
        $employee = BPEmployee::query()->where('employee_num', $completion->employee_num)->firstOrFail();
        $this->assertEmployeeSelfService($actor, $employee);

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
        $this->assertEmployeeSelfService($actor, $employee);
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

        $completion = DB::transaction(function () use ($completion, $reviewer, $employee, $item) {
            $completion->fill([
                'status' => EmployeeTrainingCompletion::STATUS_COMPLETED,
                'completed_at' => now(),
                'completed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'rejection_reason' => null,
            ])->save();

            $this->completeOpenReviewTasks($completion, $reviewer);
            $this->completeEmployeeAssignmentTasks($employee, $item, $completion, $reviewer);

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

    private function assertEmployeeSelfService(User $actor, BPEmployee $employee): void
    {
        if (! $this->actorIsEmployee($actor, $employee)) {
            throw new AuthorizationException('Only the employee can start or submit their own training.');
        }
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

    /**
     * @return array{task: PersonalTask, email_sent: bool}
     */
    public function assignTaskToEmployee(
        BPEmployee $employee,
        EmployeeTrainingItem $item,
        User $reviewer,
        string $title,
        string $message,
        string $priority,
        ?Carbon $dueAt,
        ?int $assessmentPeriodId
    ): array {
        $employee->loadMissing('user');
        if (! $employee->user) {
            throw new RuntimeException('This employee does not have a linked portal account.');
        }
        if (! $this->actorCanReview($reviewer, $employee)) {
            throw new AuthorizationException('You are not authorized to assign training tasks to this employee.');
        }

        $periodKey = EmployeeTrainingCompletion::periodKeyFor($assessmentPeriodId, $item->isHiring());
        $marker = $this->assignmentTaskMarker($item->id, $periodKey);
        $actionUrl = route('admin.employees.edit', $employee->id).'?'.http_build_query(array_filter([
            'tab' => 'checklist',
            'checklist_tab' => 'partH',
            'assessment_period_id' => $assessmentPeriodId,
            'training_item_id' => $item->id,
        ]));

        $task = PersonalTask::query()
            ->where('assigned_to', $employee->user->id)
            ->where('status', PersonalTask::STATUS_PENDING)
            ->where(function ($query) use ($title, $actionUrl, $marker) {
                $query->where(function ($exact) use ($title, $actionUrl) {
                    $exact->where('title', $title)->where('action_url', $actionUrl);
                })->orWhere('description', 'like', '%'.$marker.'%');
            })
            ->first() ?? new PersonalTask;

        $task->fill([
            'created_by' => $reviewer->id,
            'assigned_to' => $employee->user->id,
            'title' => $title,
            'description' => trim($marker."\n".$message),
            'action_url' => $actionUrl,
            'action_label' => 'Open training',
            'priority' => $priority,
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => $dueAt,
        ])->save();

        $email = trim((string) ($employee->email ?: $employee->user->email ?: ''));
        $emailSent = false;
        if ($email !== '') {
            try {
                Mail::to($email)->send(new TrainingTaskAssignedMail(
                    $employee,
                    $item,
                    $reviewer,
                    $title,
                    $message,
                    $actionUrl,
                    $dueAt?->format('F j, Y'),
                ));
                $emailSent = true;
            } catch (\Throwable $exception) {
                Log::warning('Training task email could not be sent.', [
                    'employee_num' => $employee->employee_num,
                    'training_item_id' => $item->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return ['task' => $task->fresh(), 'email_sent' => $emailSent];
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

    protected function assignmentTaskMarker(int $trainingItemId, string $periodKey): string
    {
        return '[training_assignment:'.$trainingItemId.':'.$periodKey.']';
    }

    protected function completeEmployeeAssignmentTasks(
        BPEmployee $employee,
        EmployeeTrainingItem $item,
        EmployeeTrainingCompletion $completion,
        User $reviewer
    ): void {
        $employee->loadMissing('user');
        if (! $employee->user) {
            return;
        }

        $periodKey = EmployeeTrainingCompletion::periodKeyFor(
            $completion->assessment_period_id,
            $item->isHiring()
        );
        $marker = $this->assignmentTaskMarker((int) $item->id, $periodKey);
        $trainingQuery = 'training_item_id='.(int) $item->id;

        $tasks = PersonalTask::query()
            ->where('assigned_to', $employee->user->id)
            ->where('status', PersonalTask::STATUS_PENDING)
            ->where(function ($query) use ($marker, $trainingQuery, $completion, $item) {
                $query->where('description', 'like', '%'.$marker.'%')
                    ->orWhere(function ($urlQuery) use ($trainingQuery, $completion, $item) {
                        $urlQuery->where('action_url', 'like', '%'.$trainingQuery.'%');
                        if ($item->isHiring()) {
                            $urlQuery->where(function ($hireQuery) {
                                $hireQuery->where('action_url', 'not like', '%assessment_period_id=%')
                                    ->orWhere('action_url', 'like', '%assessment_period_id=&%');
                            });
                        } elseif ($completion->assessment_period_id) {
                            $urlQuery->where('action_url', 'like', '%assessment_period_id='.(int) $completion->assessment_period_id.'%');
                        }
                    });
            })
            ->get();

        foreach ($tasks as $task) {
            $task->update([
                'status' => PersonalTask::STATUS_CONFIRMED,
                'completed_at' => $task->completed_at ?? now(),
                'completed_by' => $task->completed_by ?? $employee->user->id,
                'confirmed_at' => now(),
                'confirmed_by' => $reviewer->id,
            ]);
        }
    }

    /**
     * Close orphaned employee training-assignment tasks whose training is already completed.
     */
    public function syncCompletedAssignmentTasksForUser(User $user): int
    {
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee()
            : null;
        if (! $employee?->employee_num) {
            return 0;
        }

        $pendingTasks = PersonalTask::query()
            ->where('assigned_to', $user->id)
            ->where('status', PersonalTask::STATUS_PENDING)
            ->where(function ($query) {
                $query->where('description', 'like', '%[training_assignment:%')
                    ->orWhere('action_url', 'like', '%training_item_id=%');
            })
            ->get();

        $closed = 0;
        foreach ($pendingTasks as $task) {
            $trainingItemId = null;
            $periodKey = null;

            if (preg_match('/\[training_assignment:(\d+):([^\]]+)\]/', (string) $task->description, $matches)) {
                $trainingItemId = (int) $matches[1];
                $periodKey = (string) $matches[2];
            } elseif (preg_match('/[?&]training_item_id=(\d+)/', (string) $task->action_url, $matches)) {
                $trainingItemId = (int) $matches[1];
                if (preg_match('/[?&]assessment_period_id=(\d+)/', (string) $task->action_url, $periodMatches)) {
                    $periodKey = (string) (int) $periodMatches[1];
                } else {
                    $periodKey = EmployeeTrainingCompletion::PERIOD_KEY_HIRE;
                }
            }

            if (! $trainingItemId || ! $periodKey) {
                continue;
            }

            $isCompleted = EmployeeTrainingCompletion::query()
                ->where('employee_num', $employee->employee_num)
                ->where('employee_training_item_id', $trainingItemId)
                ->where('period_key', $periodKey)
                ->where('status', EmployeeTrainingCompletion::STATUS_COMPLETED)
                ->exists();

            if (! $isCompleted) {
                continue;
            }

            $task->update([
                'status' => PersonalTask::STATUS_CONFIRMED,
                'completed_at' => $task->completed_at ?? now(),
                'completed_by' => $task->completed_by ?? $user->id,
                'confirmed_at' => now(),
                'confirmed_by' => $task->confirmed_by ?? $user->id,
            ]);
            $closed++;
        }

        return $closed;
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
