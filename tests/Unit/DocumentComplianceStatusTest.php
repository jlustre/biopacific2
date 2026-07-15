<?php

namespace Tests\Unit;

use App\Models\Upload;
use App\Services\DocumentComplianceService;
use App\Services\EmployeeDocumentRequirementsService;
use Carbon\Carbon;
use Tests\TestCase;

class DocumentComplianceStatusTest extends TestCase
{
    private DocumentComplianceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DocumentComplianceService(
            $this->createMock(EmployeeDocumentRequirementsService::class)
        );
    }

    public function test_missing_requirement_has_no_approved_upload(): void
    {
        $result = $this->service->evaluateUploads(collect(), Carbon::parse('2026-07-15'));

        $this->assertSame('missing', $result['status']);
        $this->assertNull($result['valid_approved_upload']);
    }

    public function test_valid_approved_upload_completes_requirement(): void
    {
        $approved = new Upload([
            'verification_status' => Upload::VERIFICATION_APPROVED,
            'expires_at' => '2026-12-31',
        ]);

        $result = $this->service->evaluateUploads(collect([$approved]), Carbon::parse('2026-07-15'));

        $this->assertSame('complete', $result['status']);
        $this->assertSame($approved, $result['valid_approved_upload']);
    }

    public function test_new_pending_renewal_stays_outstanding_over_older_approval(): void
    {
        $pending = new Upload([
            'verification_status' => Upload::VERIFICATION_PENDING,
            'expires_at' => '2027-12-31',
        ]);
        $approved = new Upload([
            'verification_status' => Upload::VERIFICATION_APPROVED,
            'expires_at' => '2026-12-31',
        ]);

        $result = $this->service->evaluateUploads(
            collect([$pending, $approved]),
            Carbon::parse('2026-07-15')
        );

        $this->assertSame('pending_review', $result['status']);
    }

    public function test_rejected_upload_requires_resubmission(): void
    {
        $rejected = new Upload([
            'verification_status' => Upload::VERIFICATION_REJECTED,
            'verification_notes' => 'Image is unreadable.',
        ]);

        $result = $this->service->evaluateUploads(collect([$rejected]), Carbon::parse('2026-07-15'));

        $this->assertSame('rejected', $result['status']);
    }

    public function test_expired_approval_remains_outstanding(): void
    {
        $approved = new Upload([
            'verification_status' => Upload::VERIFICATION_APPROVED,
            'expires_at' => '2026-07-14',
        ]);

        $result = $this->service->evaluateUploads(collect([$approved]), Carbon::parse('2026-07-15'));

        $this->assertSame('expired', $result['status']);
    }
}
