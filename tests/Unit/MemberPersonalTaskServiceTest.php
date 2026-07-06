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
        ]));

        $this->assertSame('sig-performance-1', $tasks[0]['id']);
    }
}
