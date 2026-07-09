<?php

namespace Tests\Unit;

use App\Models\PersonalTask;
use App\Models\User;
use App\Policies\PersonalTaskPolicy;
use Tests\TestCase;

class PersonalTaskWorkflowTest extends TestCase
{
    public function test_assignee_can_complete_open_task_assigned_by_someone_else(): void
    {
        $creator = new User(['name' => 'Creator']);
        $creator->id = 10;
        $assignee = new User(['name' => 'Assignee']);
        $assignee->id = 20;
        $task = new PersonalTask([
            'created_by' => 10,
            'assigned_to' => 20,
            'status' => PersonalTask::STATUS_PENDING,
        ]);

        $policy = new PersonalTaskPolicy();
        $this->assertTrue($policy->complete($assignee, $task));
        $this->assertFalse($policy->complete($creator, $task));
    }

    public function test_creator_confirms_after_assignee_marks_complete(): void
    {
        $creator = new User();
        $creator->id = 10;
        $assignee = new User();
        $assignee->id = 20;
        $task = new PersonalTask([
            'created_by' => 10,
            'assigned_to' => 20,
            'status' => PersonalTask::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => 20,
        ]);

        $policy = new PersonalTaskPolicy();
        $this->assertTrue($policy->confirm($creator, $task));
        $this->assertFalse($policy->confirm($assignee, $task));
        $this->assertTrue($task->awaitsCreatorConfirmation());
    }

    public function test_only_creator_can_update_or_delete_pending_task(): void
    {
        $creator = new User();
        $creator->id = 1;
        $other = new User();
        $other->id = 2;
        $task = new PersonalTask([
            'created_by' => 1,
            'assigned_to' => 2,
            'status' => PersonalTask::STATUS_PENDING,
        ]);

        $policy = new PersonalTaskPolicy();
        $this->assertTrue($policy->update($creator, $task));
        $this->assertTrue($policy->delete($creator, $task));
        $this->assertFalse($policy->update($other, $task));
        $this->assertFalse($policy->delete($other, $task));
    }
}
