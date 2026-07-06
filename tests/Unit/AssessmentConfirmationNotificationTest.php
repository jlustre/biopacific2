<?php

namespace Tests\Unit;

use App\Mail\PerformanceAssessmentConfirmationMail;
use App\Models\BPEmployee;
use App\Models\EmployeePerformanceAssessment;
use App\Services\AssessmentConfirmationNotificationService;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AssessmentConfirmationNotificationTest extends TestCase
{
    public function test_it_builds_employee_part_f_confirmation_url(): void
    {
        $employee = new BPEmployee([
            'employee_num' => 'EMP-TEST-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        $url = app(AssessmentConfirmationNotificationService::class)->buildEmployeeChecklistUrl(
            $employee,
            'partF',
            42
        );

        $this->assertStringContainsString('/my-employment', $url);
        $this->assertStringContainsString('tab=checklist', $url);
        $this->assertStringContainsString('checklist_tab=partF', $url);
        $this->assertStringContainsString('assessment_period_id=42', $url);
    }

    public function test_it_sends_performance_confirmation_email_on_submit(): void
    {
        Mail::fake();

        $employee = new BPEmployee([
            'employee_num' => 'EMP-TEST-002',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
        ]);

        $assessment = new EmployeePerformanceAssessment([
            'employee_num' => $employee->employee_num,
            'assessment_period_id' => 7,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'items' => [],
            'assessed_by' => 1,
        ]);
        $assessment->setRelation('period', null);

        $sent = app(AssessmentConfirmationNotificationService::class)
            ->notifyPerformanceAssessmentSubmitted($assessment, $employee);

        $this->assertTrue($sent);

        Mail::assertSent(PerformanceAssessmentConfirmationMail::class, function (PerformanceAssessmentConfirmationMail $mail) {
            return $mail->hasTo('john@example.com')
                && str_contains($mail->confirmationUrl, 'checklist_tab=partF');
        });
    }
}
