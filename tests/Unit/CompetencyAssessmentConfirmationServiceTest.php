<?php

namespace Tests\Unit;

use App\Models\EmployeeCompetencyAssessment;
use App\Services\CompetencyAssessmentConfirmationService;
use App\Support\AssessmentWorkflowStatus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompetencyAssessmentConfirmationServiceTest extends TestCase
{
    public function test_store_employee_signature_from_data_url(): void
    {
        Storage::fake('public');

        $assessment = new EmployeeCompetencyAssessment([
            'id' => 42,
            'employee_num' => 'EMP-002',
        ]);

        $service = app(CompetencyAssessmentConfirmationService::class);
        $path = $service->storeEmployeeSignature(
            $assessment,
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            null,
        );

        $this->assertNotSame('', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_reviewer_signature_from_data_url(): void
    {
        Storage::fake('public');

        $assessment = new EmployeeCompetencyAssessment([
            'id' => 43,
            'employee_num' => 'EMP-003',
        ]);

        $service = app(CompetencyAssessmentConfirmationService::class);
        $path = $service->storeReviewerSignature(
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

        $assessment = new EmployeeCompetencyAssessment([
            'id' => 123,
            'employee_num' => 'EMP-002',
            'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            'employee_signed_at' => now(),
            'employee_signature_path' => 'competency-assessments/123/signatures/test.png',
            'reviewer_signed_at' => now(),
            'reviewer_signature_path' => 'competency-assessments/123/signatures/reviewer.png',
            'review_date' => now()->toDateString(),
            'pdf_path' => 'competency-assessments/EMP-002/assessment-123.pdf',
            'pdf_generated_at' => now(),
            'employee_confirmation_snapshot' => ['ratings' => []],
            'completed_at' => now(),
        ]);

        Storage::disk('public')->put('competency-assessments/123/signatures/test.png', 'signature');
        Storage::disk('public')->put('competency-assessments/123/signatures/reviewer.png', 'signature');
        Storage::disk('public')->put('competency-assessments/EMP-002/assessment-123.pdf', 'pdf');
        Storage::disk('public')->put(
            app(\App\Services\CompetencyAssessmentPdfStorage::class)
                ->sectionPdfPath($assessment, 'LICENSED NURSE eMAR COMPETENCY'),
            'section'
        );

        $service = app(CompetencyAssessmentConfirmationService::class);
        $service->resetForEmployeeReconfirmation($assessment);

        $this->assertSame(AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION, $assessment->workflowStatus());
        $this->assertNull($assessment->employee_signed_at);
        $this->assertNull($assessment->employee_signature_path);
        $this->assertNull($assessment->reviewer_signed_at);
        $this->assertNull($assessment->reviewer_signature_path);
        $this->assertNull($assessment->review_date);
        $this->assertNull($assessment->pdf_path);
        $this->assertNull($assessment->pdf_generated_at);
        $this->assertNull($assessment->employee_confirmation_snapshot);
        $this->assertNull($assessment->completed_at);
        Storage::disk('public')->assertMissing('competency-assessments/123/signatures/test.png');
        Storage::disk('public')->assertMissing('competency-assessments/123/signatures/reviewer.png');
        Storage::disk('public')->assertMissing('competency-assessments/EMP-002/assessment-123.pdf');
        Storage::disk('public')->assertMissing(
            app(\App\Services\CompetencyAssessmentPdfStorage::class)
                ->sectionPdfPath($assessment, 'LICENSED NURSE eMAR COMPETENCY')
        );
    }
}
