<?php

namespace Tests\Feature;

use App\Mail\TrainingTaskAssignedMail;
use App\Models\BPEmployee;
use App\Models\EmployeeTrainingItem;
use App\Models\PersonalTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrainingTaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_reviewer_assigns_training_task_and_sends_employee_message(): void
    {
        Mail::fake();
        [$employee, $employeeUser, $training] = $this->trainingContext();
        $reviewer = User::factory()->create();
        $reviewer->assignRole('admin');
        $payload = [
            'title' => 'Required training: Infection Control',
            'message' => 'Please start this required training and submit it for review.',
            'priority' => 'high',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ];
        $route = route('admin.employees.training-completions.assign-task', [
            'employee' => $employee,
            'trainingItem' => $training,
        ]);

        $this->actingAs($reviewer)->post($route, $payload)->assertRedirect();
        $this->actingAs($reviewer)->post($route, $payload)->assertRedirect();

        $task = PersonalTask::query()
            ->where('assigned_to', $employeeUser->id)
            ->where('title', $payload['title'])
            ->where('status', PersonalTask::STATUS_PENDING)
            ->sole();

        $this->assertSame($reviewer->id, $task->created_by);
        $this->assertSame('high', $task->priority);
        $this->assertSame('Open training', $task->action_label);
        $this->assertStringContainsString('[training_assignment:', (string) $task->description);
        $this->assertStringContainsString($payload['message'], (string) $task->description);
        $this->assertSame(
            1,
            PersonalTask::query()
                ->where('assigned_to', $employeeUser->id)
                ->where('title', $payload['title'])
                ->where('status', PersonalTask::STATUS_PENDING)
                ->count()
        );
        Mail::assertSent(
            TrainingTaskAssignedMail::class,
            fn (TrainingTaskAssignedMail $mail) => $mail->hasTo($employeeUser->email)
                && $mail->trainingItem->is($training)
        );
    }

    public function test_employee_cannot_assign_training_task_to_themself(): void
    {
        Mail::fake();
        [$employee, $employeeUser, $training] = $this->trainingContext();

        $this->actingAs($employeeUser)
            ->post(route('admin.employees.training-completions.assign-task', [
                'employee' => $employee,
                'trainingItem' => $training,
            ]), [
                'title' => 'Required training',
                'message' => 'Start this training.',
                'priority' => 'medium',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('personal_tasks', 0);
        Mail::assertNothingSent();
    }

    public function test_approving_training_confirms_employee_assignment_task(): void
    {
        Mail::fake();
        [$employee, $employeeUser, $training] = $this->trainingContext();
        $reviewer = User::factory()->create();
        $reviewer->assignRole('admin');
        $workflow = app(\App\Services\EmployeeTrainingWorkflowService::class);

        $workflow->assignTaskToEmployee(
            $employee,
            $training,
            $reviewer,
            'Required training: Infection Control',
            'Please complete this training.',
            'high',
            now()->addDays(7),
            null
        );

        $completion = $workflow->findOrCreateCompletion($employee, $training, null);
        $completion->fill([
            'status' => \App\Models\EmployeeTrainingCompletion::STATUS_SUBMITTED,
            'started_at' => now()->subDay(),
            'started_by' => $employeeUser->id,
            'submitted_at' => now(),
            'submitted_by' => $employeeUser->id,
        ])->save();

        $workflow->approve($completion->fresh(), $reviewer);

        $task = PersonalTask::query()
            ->where('assigned_to', $employeeUser->id)
            ->where('title', 'Required training: Infection Control')
            ->sole();

        $this->assertSame(PersonalTask::STATUS_CONFIRMED, $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertNotNull($task->confirmed_at);
        $this->assertSame(0, PersonalTask::assignedOpenCountForUser($employeeUser));
        $this->assertDatabaseHas('employee_training_completions', [
            'employee_num' => $employee->employee_num,
            'employee_training_item_id' => $training->id,
            'status' => \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED,
        ]);
    }

    public function test_orphan_pending_assignment_task_syncs_when_training_already_completed(): void
    {
        [$employee, $employeeUser, $training] = $this->trainingContext();
        $reviewer = User::factory()->create();
        $reviewer->assignRole('admin');

        \App\Models\EmployeeTrainingCompletion::query()->create([
            'employee_num' => $employee->employee_num,
            'employee_training_item_id' => $training->id,
            'period_key' => \App\Models\EmployeeTrainingCompletion::PERIOD_KEY_HIRE,
            'status' => \App\Models\EmployeeTrainingCompletion::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);

        $marker = '[training_assignment:'.$training->id.':hire]';
        PersonalTask::query()->create([
            'created_by' => $reviewer->id,
            'assigned_to' => $employeeUser->id,
            'title' => 'Required training: Infection Control',
            'description' => $marker."\nPlease complete this training.",
            'action_url' => route('admin.employees.edit', $employee->id).'?tab=checklist&checklist_tab=partH&training_item_id='.$training->id,
            'action_label' => 'Open training',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
        ]);

        $closed = app(\App\Services\EmployeeTrainingWorkflowService::class)
            ->syncCompletedAssignmentTasksForUser($employeeUser);

        $this->assertSame(1, $closed);
        $this->assertSame(
            PersonalTask::STATUS_CONFIRMED,
            PersonalTask::query()->where('assigned_to', $employeeUser->id)->value('status')
        );
        $this->assertSame(0, PersonalTask::assignedOpenCountForUser($employeeUser));
    }

    /**
     * @return array{BPEmployee, User, EmployeeTrainingItem}
     */
    private function trainingContext(): array
    {
        $employeeUser = User::factory()->create();
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-TASK-001',
            'user_id' => $employeeUser->id,
            'first_name' => 'Task',
            'last_name' => 'Employee',
            'email' => $employeeUser->email,
        ]);
        $training = EmployeeTrainingItem::query()->create([
            'name' => 'Infection Control',
            'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
        ]);

        return [$employee, $employeeUser, $training];
    }
}
