<?php

namespace Tests\Feature;

use App\Models\PersonalTask;
use App\Models\User;
use App\Services\PersonalTaskPageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalTaskTrainingVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_sees_open_training_but_not_review_training_they_submitted(): void
    {
        $employee = User::factory()->create(['name' => 'Employee']);
        $reviewer = User::factory()->create(['name' => 'Reviewer']);

        PersonalTask::query()->create([
            'created_by' => $reviewer->id,
            'assigned_to' => $employee->id,
            'title' => 'Required training: Workplace Violence Prevention in CA',
            'description' => "[training_assignment:1:33]\nPlease start the required training.",
            'action_url' => 'https://example.test/training/open',
            'action_label' => 'Open training',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => now()->addDays(7),
        ]);

        PersonalTask::query()->create([
            'created_by' => $employee->id,
            'assigned_to' => $reviewer->id,
            'title' => 'Review training: Workplace Violence Prevention in CA',
            'description' => "[training_completion_id:99]\nEmployee submitted for completion review.",
            'action_url' => 'https://example.test/training/review',
            'action_label' => 'Review training',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => now()->addDays(7),
        ]);

        $titles = app(PersonalTaskPageService::class)
            ->collectRows($employee, ['status' => 'open', 'source' => 'manual'])
            ->pluck('title')
            ->all();

        $this->assertSame(
            ['Required training: Workplace Violence Prevention in CA'],
            $titles
        );
    }

    public function test_reviewer_sees_review_training_but_not_open_training_they_assigned(): void
    {
        $employee = User::factory()->create(['name' => 'Employee']);
        $reviewer = User::factory()->create(['name' => 'Reviewer']);

        PersonalTask::query()->create([
            'created_by' => $reviewer->id,
            'assigned_to' => $employee->id,
            'title' => 'Required training: Workplace Violence Prevention in CA',
            'description' => "[training_assignment:1:33]\nPlease start the required training.",
            'action_url' => 'https://example.test/training/open',
            'action_label' => 'Open training',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => now()->addDays(7),
        ]);

        PersonalTask::query()->create([
            'created_by' => $employee->id,
            'assigned_to' => $reviewer->id,
            'title' => 'Review training: Workplace Violence Prevention in CA',
            'description' => "[training_completion_id:99]\nEmployee submitted for completion review.",
            'action_url' => 'https://example.test/training/review',
            'action_label' => 'Review training',
            'priority' => 'high',
            'status' => PersonalTask::STATUS_PENDING,
            'due_at' => now()->addDays(7),
        ]);

        $titles = app(PersonalTaskPageService::class)
            ->collectRows($reviewer, ['status' => 'open', 'source' => 'manual'])
            ->pluck('title')
            ->all();

        $this->assertSame(
            ['Review training: Workplace Violence Prevention in CA'],
            $titles
        );
    }
}
