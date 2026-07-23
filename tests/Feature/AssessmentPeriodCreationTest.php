<?php

namespace Tests\Feature;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\User;
use App\Services\EmployeeAssessmentPeriodService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssessmentPeriodCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_reviewer_can_create_an_idempotent_period_by_assessment_year(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('admin');
        $employee = $this->createEmployee();
        $payload = [
            'employee_num' => $employee->employee_num,
            'assessment_year' => 2026,
            'review_type' => 'A',
        ];

        $first = $this->actingAs($reviewer)
            ->postJson(route('admin.employees.performance-assessment.period'), $payload);
        $second = $this->actingAs($reviewer)
            ->postJson(route('admin.employees.performance-assessment.period'), $payload);

        $first->assertOk()
            ->assertJsonPath('period.date_from', '2025-05-18')
            ->assertJsonPath('period.date_to', '2026-05-17')
            ->assertJsonPath('period.period_year', 2025);
        $second->assertOk()
            ->assertJsonPath('message', 'That assessment period already exists and has been loaded.');
        $this->assertSame(
            1,
            EmployeeAssessmentPeriod::query()->where('employee_num', $employee->employee_num)->count()
        );
    }

    public function test_current_assessment_year_is_generated_and_selected_automatically(): void
    {
        Carbon::setTestNow('2026-07-15');
        try {
            $employee = $this->createEmployee();
            $service = app(EmployeeAssessmentPeriodService::class);

            $periods = $service->periodsForEmployee($employee);
            $selectedId = $service->resolveActivePeriodIdForReview($employee, $periods, null);
            $selected = $periods->firstWhere('id', $selectedId);

            $this->assertNotNull($selected);
            $this->assertSame('2025-05-18', $selected->date_from->format('Y-m-d'));
            $this->assertSame('2026-05-17', $selected->date_to->format('Y-m-d'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_non_reviewer_cannot_create_an_assessment_period(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs(User::factory()->create())
            ->postJson(route('admin.employees.performance-assessment.period'), [
                'employee_num' => $employee->employee_num,
                'assessment_year' => 2026,
                'review_type' => 'A',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('employee_assessment_periods', 0);
    }

    public function test_first_year_period_is_available_for_competency_evaluation(): void
    {
        Carbon::setTestNow('2026-07-15');
        try {
            $reviewer = User::factory()->create();
            $reviewer->assignRole('admin');
            $employee = BPEmployee::query()->create([
                'employee_num' => 'EMP-ASSESS-NEW',
                'first_name' => 'New',
                'last_name' => 'Employee',
                'email' => 'new-assessment-employee@example.com',
                'original_hire_dt' => '2026-01-20',
            ]);
            $service = app(EmployeeAssessmentPeriodService::class);

            $periods = $service->periodsForEmployee($employee);
            $this->assertCount(1, $periods);
            $this->assertSame('2026-01-20', $periods->first()->date_from->format('Y-m-d'));
            $this->assertSame('2027-01-19', $periods->first()->date_to->format('Y-m-d'));
            $this->actingAs($reviewer)
                ->postJson(route('admin.employees.performance-assessment.period'), [
                    'employee_num' => $employee->employee_num,
                    'assessment_year' => 2027,
                    'review_type' => 'A',
                ])
                ->assertOk();
            $this->assertDatabaseCount('employee_assessment_periods', 1);

            $itemPayload = [
                'employee_num' => $employee->employee_num,
                'rating' => 'E',
                'assessment_date' => '2026-07-15',
                'assessment_period_id' => $periods->first()->id,
            ];
            $this->actingAs($reviewer)
                ->postJson(route('admin.employees.performance-assessment.store'), $itemPayload + [
                    'item_key' => 'F_1',
                ])
                ->assertUnprocessable()
                ->assertJsonPath(
                    'message',
                    'Performance appraisal is not due until December 21, 2026. Competency evaluation may be completed during the first year.'
                );
            $this->actingAs($reviewer)
                ->postJson(route('admin.employees.performance-assessment.store'), $itemPayload + [
                    'item_key' => 'G_1',
                ])
                ->assertOk();
            $this->assertDatabaseHas('employee_assessment_item_entries', [
                'employee_num' => $employee->employee_num,
                'assessment_period_id' => $periods->first()->id,
                'assessment_type' => 'competency',
                'item_key' => 'G_1',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_reviewer_cannot_create_a_period_for_their_own_employee_record(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('admin');
        $employee = $this->createEmployee();
        $employee->update(['user_id' => $reviewer->id]);

        $this->actingAs($reviewer)
            ->postJson(route('admin.employees.performance-assessment.period'), [
                'employee_num' => $employee->employee_num,
                'assessment_year' => 2026,
                'review_type' => 'A',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('employee_assessment_periods', 0);
    }

    private function createEmployee(): BPEmployee
    {
        return BPEmployee::query()->create([
            'employee_num' => 'EMP-ASSESS-001',
            'first_name' => 'Assessment',
            'last_name' => 'Employee',
            'email' => 'assessment-employee@example.com',
            'original_hire_dt' => '2020-05-18',
        ]);
    }
}
