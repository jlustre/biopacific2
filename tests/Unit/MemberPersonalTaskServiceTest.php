<?php

namespace Tests\Unit;

use App\Services\MemberPersonalTaskService;
use ReflectionMethod;
use Tests\TestCase;

class MemberPersonalTaskServiceTest extends TestCase
{
    public function test_signature_and_confirmation_tasks_sort_to_the_top(): void
    {
        $service = app(MemberPersonalTaskService::class);
        $method = new ReflectionMethod(MemberPersonalTaskService::class, 'prioritizeTasks');
        $method->setAccessible(true);

        $tasks = $method->invoke($service, collect([
            [
                'id' => 'todo-upload-license',
                'title' => 'Upload: Driver License',
                'description' => 'Required document',
                'category' => 'upload',
                'priority' => 'high',
                'route' => '/documents',
                'done' => false,
            ],
            [
                'id' => 'sig-performance-1',
                'title' => 'Sign: Performance appraisal',
                'description' => 'Acknowledge your performance appraisal.',
                'category' => 'signature',
                'priority' => 'medium',
                'route' => '/my-employment?tab=checklist',
                'done' => false,
            ],
            [
                'id' => 'todo-complete-profile',
                'title' => 'Complete your profile information',
                'description' => 'Profile details needed.',
                'category' => 'profile',
                'priority' => 'high',
                'route' => '/profile',
                'done' => false,
            ],
            [
                'id' => 'todo-training-1',
                'title' => 'Annual safety training',
                'description' => 'Complete this training requirement.',
                'category' => 'training',
                'priority' => 'high',
                'route' => '/trainings',
                'done' => false,
            ],
        ]));

        $this->assertSame('sig-performance-1', $tasks[0]['id']);
        $this->assertContains($tasks[1]['id'], ['todo-upload-license', 'todo-complete-profile']);
        $this->assertContains($tasks[2]['id'], ['todo-upload-license', 'todo-complete-profile']);
        $this->assertSame('todo-training-1', $tasks[3]['id']);
    }

    public function test_competency_confirmation_tasks_sort_before_profile_and_upload_tasks(): void
    {
        $service = app(MemberPersonalTaskService::class);
        $method = new ReflectionMethod(MemberPersonalTaskService::class, 'prioritizeTasks');
        $method->setAccessible(true);

        $tasks = $method->invoke($service, collect([
            [
                'id' => 'todo-upload-license',
                'title' => 'Upload: Driver License',
                'description' => 'Required document',
                'category' => 'upload',
                'priority' => 'high',
                'route' => '/documents',
                'done' => false,
            ],
            [
                'id' => 'confirm-competency-42',
                'title' => 'Sign: Review updated LICENSED NURSE eMAR COMPETENCY',
                'description' => 'Your reviewer updated your competency assessment.',
                'category' => 'competency-confirmation',
                'priority' => 'high',
                'action' => 'sign',
                'route' => '/employment?tab=checklist',
                'done' => false,
            ],
            [
                'id' => 'todo-complete-profile',
                'title' => 'Complete your profile information',
                'description' => 'Profile details needed.',
                'category' => 'profile',
                'priority' => 'high',
                'route' => '/profile',
                'done' => false,
            ],
        ]));

        $this->assertSame('confirm-competency-42', $tasks[0]['id']);
    }

    public function test_signature_tasks_are_never_trimmed_by_dashboard_limit(): void
    {
        $service = app(MemberPersonalTaskService::class);
        $method = new ReflectionMethod(MemberPersonalTaskService::class, 'prioritizeTasks');
        $method->setAccessible(true);

        $tasks = $method->invoke($service, collect([
            [
                'id' => 'confirm-competency-1',
                'title' => 'Sign: Confirm competency assessment',
                'category' => 'competency-confirmation',
                'priority' => 'high',
                'action' => 'sign',
                'route' => '/employment',
                'done' => false,
            ],
            [
                'id' => 'confirm-competency-2',
                'title' => 'Sign: Review updated competency assessment',
                'category' => 'competency-confirmation',
                'priority' => 'high',
                'action' => 'sign',
                'route' => '/employment',
                'done' => false,
            ],
            [
                'id' => 'sig-orientation-1',
                'title' => 'Sign: Orientation checklist',
                'category' => 'signature',
                'priority' => 'high',
                'route' => '/employment',
                'done' => false,
            ],
            ...collect(range(1, 10))->map(fn (int $index) => [
                'id' => 'todo-upload-' . $index,
                'title' => 'Upload: Document ' . $index,
                'category' => 'upload',
                'priority' => 'high',
                'route' => '/documents',
                'done' => false,
            ])->all(),
        ]), 8);

        $this->assertCount(8, $tasks);
        $firstThree = array_slice(array_column($tasks, 'id'), 0, 3);
        $this->assertEqualsCanonicalizing(
            ['confirm-competency-1', 'confirm-competency-2', 'sig-orientation-1'],
            $firstThree
        );
    }
}
