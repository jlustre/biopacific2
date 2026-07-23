<?php

namespace Tests\Feature;

use App\Models\BPEmployee;
use App\Models\EmployeeTrainingCompletion;
use App\Models\EmployeeTrainingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrainingSelfServiceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'facility-dsd', 'guard_name' => 'web']);
    }

    public function test_only_employee_can_start_their_own_training(): void
    {
        $employeeUser = User::factory()->create();
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-TRAIN-001',
            'user_id' => $employeeUser->id,
            'first_name' => 'Training',
            'last_name' => 'Employee',
            'email' => $employeeUser->email,
        ]);
        $training = EmployeeTrainingItem::query()->create([
            'name' => 'Required hiring module',
            'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
        ]);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('facility-dsd');

        $this->actingAs($reviewer)
            ->post(route('admin.employees.training-completions.start', [
                'employee' => $employee,
                'trainingItem' => $training,
            ]))
            ->assertForbidden();
        $this->assertDatabaseCount('employee_training_completions', 0);

        $this->actingAs($employeeUser)
            ->post(route('admin.employees.training-completions.start', [
                'employee' => $employee,
                'trainingItem' => $training,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('employee_training_completions', [
            'employee_num' => $employee->employee_num,
            'employee_training_item_id' => $training->id,
            'status' => EmployeeTrainingCompletion::STATUS_IN_PROGRESS,
            'started_by' => $employeeUser->id,
        ]);
    }

    public function test_reviewer_cannot_submit_training_for_employee(): void
    {
        $employeeUser = User::factory()->create();
        $employee = BPEmployee::query()->create([
            'employee_num' => 'EMP-TRAIN-002',
            'user_id' => $employeeUser->id,
            'first_name' => 'Training',
            'last_name' => 'Employee',
            'email' => $employeeUser->email,
        ]);
        $training = EmployeeTrainingItem::query()->create([
            'name' => 'Required hiring module',
            'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
        ]);
        EmployeeTrainingCompletion::query()->create([
            'employee_num' => $employee->employee_num,
            'employee_training_item_id' => $training->id,
            'period_key' => EmployeeTrainingCompletion::PERIOD_KEY_HIRE,
            'status' => EmployeeTrainingCompletion::STATUS_IN_PROGRESS,
            'started_by' => $employeeUser->id,
            'started_at' => now(),
        ]);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('facility-dsd');

        $this->actingAs($reviewer)
            ->post(route('admin.employees.training-completions.submit', [
                'employee' => $employee,
                'trainingItem' => $training,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('employee_training_completions', [
            'employee_num' => $employee->employee_num,
            'status' => EmployeeTrainingCompletion::STATUS_IN_PROGRESS,
            'submitted_by' => null,
        ]);
    }
}
