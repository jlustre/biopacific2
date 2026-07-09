<?php

namespace Tests\Unit;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Services\EmployeeAssessmentPeriodService;
use App\Support\EmployeeAssessmentPeriodCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class EmployeeAssessmentPeriodServiceTest extends TestCase
{
    public function test_recommended_period_advances_after_hire_anniversary(): void
    {
        $employee = new BPEmployee([
            'employee_num' => 'EMP-900',
            'original_hire_dt' => '2020-05-18',
        ]);

        $beforeAnniversary = EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn(
            $employee,
            Carbon::parse('2026-05-17'),
        );
        $afterAnniversary = EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn(
            $employee,
            Carbon::parse('2026-05-18'),
        );

        $this->assertSame('2024-05-18', $beforeAnniversary['date_from']);
        $this->assertSame('2025-05-17', $beforeAnniversary['date_to']);
        $this->assertSame('2025-05-18', $afterAnniversary['date_from']);
        $this->assertSame('2026-05-17', $afterAnniversary['date_to']);
    }

    public function test_should_advance_when_prior_period_is_completed_and_newer_period_is_due(): void
    {
        $employee = new BPEmployee(['employee_num' => 'EMP-901']);

        $priorPeriod = EmployeeAssessmentPeriod::make([
            'employee_num' => 'EMP-901',
            'date_from' => '2024-05-18',
            'date_to' => '2025-05-17',
            'period_year' => 2024,
        ]);
        $priorPeriod->id = 10;

        $duePeriod = EmployeeAssessmentPeriod::make([
            'employee_num' => 'EMP-901',
            'date_from' => '2025-05-18',
            'date_to' => '2026-05-17',
            'period_year' => 2025,
        ]);
        $duePeriod->id = 11;

        $service = new class extends EmployeeAssessmentPeriodService
        {
            protected function competencyCompleted(
                string $employeeNum,
                int $periodId,
                ?EmployeeCompetencyAssessment $assessment = null,
            ): bool {
                return $periodId === 10;
            }
        };

        $this->assertTrue($service->shouldAdvanceToRecommendedPeriod($employee, $priorPeriod, $duePeriod));
    }

    public function test_resolve_active_period_keeps_requested_period_when_viewing_history(): void
    {
        $employee = new BPEmployee(['employee_num' => 'EMP-902']);

        $priorPeriod = EmployeeAssessmentPeriod::make([
            'employee_num' => 'EMP-902',
            'date_from' => '2024-05-18',
            'date_to' => '2025-05-17',
            'period_year' => 2024,
        ]);
        $priorPeriod->id = 20;

        $duePeriod = EmployeeAssessmentPeriod::make([
            'employee_num' => 'EMP-902',
            'date_from' => '2025-05-18',
            'date_to' => '2026-05-17',
            'period_year' => 2025,
        ]);
        $duePeriod->id = 21;

        $service = new class extends EmployeeAssessmentPeriodService
        {
            public function resolveDefaultPeriodId(BPEmployee $employee, ?Carbon $on = null): ?int
            {
                return 21;
            }
        };

        $resolved = $service->resolveActivePeriodIdForReview(
            $employee,
            collect([$duePeriod, $priorPeriod]),
            20,
            Carbon::parse('2026-06-01'),
            true,
        );

        $this->assertSame(20, $resolved);
    }
}
