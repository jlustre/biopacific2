<?php

namespace Tests\Unit;

use App\Models\EmployeeCompetencyAssessment;
use App\Services\CompetencySectionWorkflowService;
use App\Support\AssessmentWorkflowStatus;
use Mockery;
use Tests\TestCase;

class CompetencySectionWorkflowServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_submitting_one_section_queues_employee_confirmation_independently(): void
    {
        $service = app(CompetencySectionWorkflowService::class);

        $assessment = Mockery::mock(EmployeeCompetencyAssessment::class)->makePartial();
        $assessment->employee_num = 'E12873657';
        $assessment->assessment_period_id = 1;
        $assessment->status = AssessmentWorkflowStatus::DRAFT;
        $assessment->snapshot_json = [
            'submitted_section_labels' => ['LICENSED NURSE eMAR COMPETENCY'],
        ];
        $assessment->shouldReceive('save')->once()->andReturnTrue();
        $assessment->shouldReceive('refresh')->andReturnSelf();

        $service->submitSectionForEmployeeConfirmation(
            $assessment,
            'LICENSED NURSE eMAR COMPETENCY',
            99,
        );

        $this->assertSame(
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            $service->sectionStatus($assessment, 'LICENSED NURSE eMAR COMPETENCY'),
        );
        $this->assertSame(
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            $assessment->status,
        );

        $pending = $service->pendingEmployeeConfirmationItems(collect([$assessment]));
        $this->assertCount(1, $pending);
        $this->assertSame('LICENSED NURSE eMAR COMPETENCY', $pending[0]['section']);
    }

    public function test_display_status_is_not_started_when_section_has_no_ratings(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'LICENSED NURSE eMAR COMPETENCY';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'snapshot_json' => [],
        ]);

        $this->assertSame(
            'Not Started',
            $service->sectionDisplayStatusLabel($assessment, $sectionLabel, false, false),
        );
    }

    public function test_display_status_is_in_progress_when_section_has_ratings_but_not_submitted(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'LICENSED NURSE eMAR COMPETENCY';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'snapshot_json' => [],
        ]);

        $this->assertSame(
            'In Progress',
            $service->sectionDisplayStatusLabel($assessment, $sectionLabel, false, true),
        );
    }

    public function test_resolve_section_comments_does_not_fall_back_to_aggregate_assessment_comments(): void
    {
        $service = app(CompetencySectionWorkflowService::class);

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'comments' => 'Reviewer note from another section',
            'employee_comments' => 'This is good.',
            'snapshot_json' => [
                'section_comments' => [
                    'LICENSED NURSE eMAR COMPETENCY' => [
                        'employee_comments' => 'This is good.',
                    ],
                ],
                'section_workflow' => [
                    'LICENSED NURSE eMAR COMPETENCY' => [
                        'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
                        'employee_comments' => 'This is good.',
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'reviewer_comments' => '',
            'employee_comments' => '',
        ], $service->resolveSectionComments($assessment, 'HAND HYGIENE SKILLS'));

        $this->assertSame([
            'reviewer_comments' => '',
            'employee_comments' => 'This is good.',
        ], $service->resolveSectionComments($assessment, 'LICENSED NURSE eMAR COMPETENCY'));
    }

    public function test_resolve_section_comments_falls_back_to_workflow_employee_comments(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'snapshot_json' => [
                'section_comments' => [
                    $sectionLabel => [
                        'reviewer_comments' => 'Initial reviewer note',
                        'employee_comments' => '',
                    ],
                ],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::DRAFT,
                        'returned_at' => '2026-07-09 09:00:00',
                        'employee_comments' => 'Please revise item 3.',
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'reviewer_comments' => 'Initial reviewer note',
            'employee_comments' => 'Please revise item 3.',
        ], $service->resolveSectionComments($assessment, $sectionLabel));
    }

    public function test_section_can_be_cleared_only_when_display_status_is_in_progress(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $notStarted = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'snapshot_json' => [],
        ]);

        $this->assertFalse($service->sectionCanBeCleared($notStarted, $sectionLabel, false));

        $inProgress = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'responses' => [101 => ['response' => 'E']],
            'snapshot_json' => [],
        ]);

        $this->assertTrue($service->sectionCanBeCleared($inProgress, $sectionLabel, true));

        $submitted = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                    ],
                ],
            ],
        ]);

        $this->assertFalse($service->sectionCanBeCleared($submitted, $sectionLabel, true));
    }

    public function test_clear_section_progress_is_skipped_when_section_is_not_in_progress(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'responses' => [
                101 => ['response' => 'E'],
            ],
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                    ],
                ],
            ],
        ]);

        $service->clearSectionProgress($assessment, $sectionLabel, [101], 55);

        $this->assertArrayHasKey(101, $assessment->responses);
        $this->assertSame(
            AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            $service->sectionStatus($assessment, $sectionLabel),
        );
    }

    public function test_clear_section_progress_removes_ratings_workflow_and_comments(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'responses' => [
                101 => ['response' => 'E'],
                202 => ['response' => 'M'],
            ],
            'snapshot_json' => [
                'section_comments' => [
                    $sectionLabel => [
                        'reviewer_comments' => 'Looks good',
                        'employee_comments' => 'Thanks',
                    ],
                ],
                'section_summaries' => [
                    $sectionLabel => ['total_score' => 3],
                ],
            ],
        ]);

        $this->assertTrue($service->sectionCanBeCleared($assessment, $sectionLabel, true));

        $service->clearSectionProgress($assessment, $sectionLabel, [101], 55);

        $snapshot = $assessment->snapshot_json;
        $this->assertSame([], $snapshot['submitted_section_labels'] ?? []);
        $this->assertArrayNotHasKey($sectionLabel, $snapshot['section_workflow'] ?? []);
        $this->assertArrayNotHasKey($sectionLabel, $snapshot['section_comments'] ?? []);
        $this->assertArrayNotHasKey($sectionLabel, $snapshot['section_summaries'] ?? []);
        $this->assertArrayNotHasKey(101, $assessment->responses);
        $this->assertArrayHasKey(202, $assessment->responses);
        $this->assertSame(AssessmentWorkflowStatus::DRAFT, $assessment->status);
    }

    public function test_section_pdf_signature_fields_hide_signatures_during_employee_confirmation(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE COMPETENCY';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'employee_signature_path' => 'competency-assessments/EMP-100/signatures/old-employee.png',
            'reviewer_signature_path' => 'competency-assessments/EMP-100/signatures/old-reviewer.png',
            'employee_signed_at' => now()->subDay(),
            'reviewer_signed_at' => now()->subDay(),
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                        'employee_signature_path' => null,
                        'reviewer_signature_path' => null,
                        'employee_signed_at' => null,
                        'reviewer_signed_at' => null,
                    ],
                ],
            ],
        ]);

        $fields = $service->sectionPdfSignatureFields($assessment, $sectionLabel);

        $this->assertNull($fields['employee_signature_path']);
        $this->assertNull($fields['reviewer_signature_path']);
        $this->assertNull($fields['employee_signed_at']);
        $this->assertNull($fields['reviewer_signed_at']);
    }

    public function test_section_pdf_signature_fields_use_section_signatures_after_employee_acknowledges(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE COMPETENCY';

        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            'employee_signature_path' => 'competency-assessments/EMP-100/signatures/old-employee.png',
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
                        'employee_signature_path' => 'competency-assessments/EMP-100/sections/hand-hygiene-competency/signatures/employee-new.png',
                        'employee_signed_at' => '2026-07-08 10:15:00',
                    ],
                ],
            ],
        ]);

        $fields = $service->sectionPdfSignatureFields($assessment, $sectionLabel);

        $this->assertSame(
            'competency-assessments/EMP-100/sections/hand-hygiene-competency/signatures/employee-new.png',
            $fields['employee_signature_path'],
        );
        $this->assertNull($fields['reviewer_signature_path']);
        $this->assertSame('2026-07-08 10:15:00', $fields['employee_signed_at']);
    }

    public function test_accordion_key_maps_hand_hygiene_section_label(): void
    {
        $service = app(CompetencySectionWorkflowService::class);

        $this->assertSame('hand-hygiene', $service->accordionKeyForSectionLabel('HAND HYGIENE SKILLS'));
    }

    public function test_employee_send_back_persists_comments_and_marks_section_returned(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $assessment = Mockery::mock(EmployeeCompetencyAssessment::class)->makePartial();
        $assessment->employee_num = 'EMP-100';
        $assessment->assessment_period_id = 12;
        $assessment->status = AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION;
        $assessment->snapshot_json = [
            'submitted_section_labels' => [$sectionLabel],
            'section_workflow' => [
                $sectionLabel => [
                    'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                    'submitted_at' => '2026-07-09 08:00:00',
                ],
            ],
        ];
        $assessment->shouldReceive('save')->once()->andReturnTrue();

        $service->employeeSendBackSection($assessment, $sectionLabel, 'Please revise item 3.');

        $this->assertSame(
            AssessmentWorkflowStatus::DRAFT,
            $service->sectionStatus($assessment, $sectionLabel),
        );
        $this->assertTrue($service->sectionWasReturnedToReviewer($assessment, $sectionLabel));
        $this->assertSame(
            'Please revise item 3.',
            $service->resolveSectionComments($assessment, $sectionLabel)['employee_comments'],
        );
        $this->assertSame(
            'Returned to Reviewer',
            $service->sectionDisplayStatusLabel($assessment, $sectionLabel, true),
        );
    }

    public function test_reviewer_must_update_returned_section_before_resubmit(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $assessment = Mockery::mock(EmployeeCompetencyAssessment::class)->makePartial();
        $assessment->employee_num = 'EMP-100';
        $assessment->assessment_period_id = 12;
        $assessment->status = AssessmentWorkflowStatus::DRAFT;
        $assessment->snapshot_json = [
            'submitted_section_labels' => [$sectionLabel],
            'section_workflow' => [
                $sectionLabel => [
                    'status' => AssessmentWorkflowStatus::DRAFT,
                    'returned_at' => '2026-07-09 09:00:00',
                ],
            ],
        ];
        $assessment->shouldReceive('save')->andReturnTrue();

        $this->assertFalse($service->reviewerCanResubmitReturnedSection($assessment, $sectionLabel));

        $service->recordReviewerUpdateAfterReturn($assessment, $sectionLabel);

        $this->assertTrue($service->reviewerCanResubmitReturnedSection($assessment, $sectionLabel));
    }

    public function test_sync_submitted_sections_without_workflow_skips_completed_assessments(): void
    {
        $service = app(CompetencySectionWorkflowService::class);
        $sectionLabel = 'HAND HYGIENE SKILLS';

        $assessment = Mockery::mock(EmployeeCompetencyAssessment::class)->makePartial();
        $assessment->status = AssessmentWorkflowStatus::COMPLETED;
        $assessment->snapshot_json = [
            'submitted_section_labels' => [$sectionLabel],
        ];
        $assessment->shouldNotReceive('save');

        $service->syncSubmittedSectionsWithoutWorkflow($assessment);

        $this->assertSame([], $service->sectionWorkflow($assessment, $sectionLabel));
    }
}
