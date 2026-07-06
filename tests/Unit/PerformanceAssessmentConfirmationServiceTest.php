<?php

namespace Tests\Unit;

use App\Models\EmployeePerformanceAssessment;
use App\Services\PerformanceAssessmentConfirmationService;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerformanceAssessmentConfirmationServiceTest extends TestCase
{
    public function test_format_snapshot_date_accepts_string_review_date(): void
    {
        $service = app(PerformanceAssessmentConfirmationService::class);
        $method = new \ReflectionMethod($service, 'formatSnapshotDate');
        $method->setAccessible(true);

        $this->assertSame('2026-07-01', $method->invoke($service, '2026-07-01'));
        $this->assertNull($method->invoke($service, null));
    }

    public function test_store_employee_signature_from_data_url(): void
    {
        Storage::fake('public');

        $assessment = new EmployeePerformanceAssessment([
            'id' => 99,
            'employee_num' => 'EMP-001',
        ]);

        $service = app(PerformanceAssessmentConfirmationService::class);
        $path = $service->storeEmployeeSignature(
            $assessment,
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            null,
        );

        $this->assertNotSame('', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_reset_for_employee_reconfirmation_clears_signature_fields(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('performance-assessments/123/signatures/test.png', 'signature');

        $assessment = new EmployeePerformanceAssessment([
            'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            'acknowledge_dt' => now()->toDateString(),
            'employee_signature_path' => 'performance-assessments/123/signatures/test.png',
            'employee_confirmation_snapshot' => ['ratings' => []],
        ]);

        $service = app(PerformanceAssessmentConfirmationService::class);
        $service->resetForEmployeeReconfirmation($assessment);

        $this->assertSame(AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION, $assessment->workflowStatus());
        $this->assertNull($assessment->acknowledge_dt);
        $this->assertNull($assessment->employee_signature_path);
        $this->assertNull($assessment->employee_confirmation_snapshot);
        Storage::disk('public')->assertMissing('performance-assessments/123/signatures/test.png');
    }
}
