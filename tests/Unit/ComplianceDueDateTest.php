<?php

namespace Tests\Unit;

use App\Models\EmployeeTrainingItem;
use App\Support\ComplianceDueDate;
use App\Support\EmployeeAssessmentPeriodCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class ComplianceDueDateTest extends TestCase
{
    public function test_due_date_is_thirty_days_before_event(): void
    {
        $this->assertSame(
            '2026-04-18',
            ComplianceDueDate::before('2026-05-18')->format('Y-m-d')
        );
    }

    public function test_period_due_is_thirty_days_before_hire_anniversary(): void
    {
        // Period ends the day before anniversary (2026-05-17 → anniversary 2026-05-18).
        $this->assertSame(
            '2026-04-18',
            ComplianceDueDate::forPeriodEnd('2026-05-17')->format('Y-m-d')
        );
        $this->assertSame(
            '2026-04-18',
            EmployeeAssessmentPeriodCalculator::dueDateForPeriodEnd('2026-05-17')->format('Y-m-d')
        );
    }

    public function test_document_due_is_thirty_days_before_expiration(): void
    {
        $this->assertSame(
            '2026-11-01',
            ComplianceDueDate::forExpiration('2026-12-01')->format('Y-m-d')
        );
    }

    public function test_recurring_training_becomes_due_thirty_days_before_renewal(): void
    {
        $item = new EmployeeTrainingItem([
            'name' => 'Annual Safety',
            'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
        ]);

        $result = $item->evaluateDue(
            Carbon::parse('2025-05-18'),
            Carbon::parse('2026-04-18')
        );

        $this->assertTrue($result['due']);
        $this->assertSame('2026-04-18', $result['next_due_at']?->format('Y-m-d'));
        $this->assertSame('2026-05-18', $result['satisfied_until']?->format('Y-m-d'));

        $beforeDue = $item->evaluateDue(
            Carbon::parse('2025-05-18'),
            Carbon::parse('2026-04-17')
        );

        $this->assertFalse($beforeDue['due']);
        $this->assertSame('2026-04-18', $beforeDue['next_due_at']?->format('Y-m-d'));
    }
}
