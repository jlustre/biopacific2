<?php

namespace Tests\Unit;

use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeeCompetencyItem;
use App\Support\AssessmentWorkflowStatus;
use App\Support\CompetencyAssessmentHistoryBuilder;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CompetencyAssessmentHistoryBuilderTest extends TestCase
{
    public function test_history_status_reflects_reviewer_approval_without_section_submit_label(): void
    {
        $sectionLabel = 'LICENSED NURSE eMAR COMPETENCY';
        $item = new EmployeeCompetencyItem([
            'section' => $sectionLabel,
            'item' => 'Demonstrates accurate medication pass',
            'order' => 1,
        ]);
        $item->id = 501;

        $submission = new EmployeeCompetencyAssessment([
            'id' => 91,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            'reviewer_name' => 'Reviewer One',
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_summaries' => [
                    $sectionLabel => [
                        'total_score' => 3,
                        'average_score' => 3,
                        'overall_rating' => 'Exceeds',
                        'review_date' => '2026-07-06',
                    ],
                ],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
                        'employee_signature_path' => 'competency-assessments/EMP-100/sections/emar/employee.png',
                    ],
                ],
            ],
        ]);

        $entry = new EmployeeAssessmentItemEntry([
            'id' => 1,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'assessment_type' => 'competency',
            'source_item_id' => 501,
            'item_key' => 'G_501',
            'rating' => 'E',
            'assessment_date' => now()->toDateString(),
        ]);

        $rows = CompetencyAssessmentHistoryBuilder::build(
            collect([$item]),
            collect([12 => $submission]),
            collect([12 => collect([$entry])]),
            collect(),
            12,
            [],
            new Collection,
        );

        $this->assertCount(1, $rows);
        $this->assertSame('For Reviewer approval', $rows[0]['status']);
    }

    public function test_history_status_reflects_completed_section_workflow(): void
    {
        $sectionLabel = 'LICENSED NURSE eMAR COMPETENCY';
        $item = new EmployeeCompetencyItem([
            'section' => $sectionLabel,
            'item' => 'Demonstrates accurate medication pass',
            'order' => 1,
        ]);
        $item->id = 501;

        $submission = new EmployeeCompetencyAssessment([
            'id' => 92,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'reviewer_name' => 'Reviewer One',
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_summaries' => [
                    $sectionLabel => [
                        'total_score' => 3,
                        'average_score' => 3,
                        'overall_rating' => 'Exceeds',
                        'review_date' => '2026-07-06',
                    ],
                ],
                'section_workflow' => [
                    $sectionLabel => [
                        'status' => AssessmentWorkflowStatus::COMPLETED,
                        'employee_signature_path' => 'competency-assessments/EMP-100/sections/emar/employee.png',
                        'reviewer_signature_path' => 'competency-assessments/EMP-100/sections/emar/reviewer.png',
                    ],
                ],
            ],
        ]);

        $entry = new EmployeeAssessmentItemEntry([
            'id' => 1,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'assessment_type' => 'competency',
            'source_item_id' => 501,
            'item_key' => 'G_501',
            'rating' => 'E',
            'assessment_date' => now()->toDateString(),
        ]);

        $rows = CompetencyAssessmentHistoryBuilder::build(
            collect([$item]),
            collect([12 => $submission]),
            collect([12 => collect([$entry])]),
            collect(),
            12,
            [],
            new Collection,
        );

        $this->assertCount(1, $rows);
        $this->assertSame('Completed', $rows[0]['status']);
    }

    public function test_history_recalculates_overall_rating_when_snapshot_summary_is_blank(): void
    {
        $sectionLabel = 'HAND HYGIENE COMPETENCY';
        $item = new EmployeeCompetencyItem([
            'section' => $sectionLabel,
            'item' => 'Performs hand hygiene before resident contact',
            'order' => 1,
        ]);
        $item->id = 601;

        $submission = new EmployeeCompetencyAssessment([
            'id' => 93,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'reviewer_name' => 'Reviewer One',
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_summaries' => [
                    $sectionLabel => [
                        'total_score' => 3,
                        'average_score' => 3,
                        'overall_rating' => '—',
                        'review_date' => '2026-07-08',
                    ],
                ],
            ],
        ]);

        $entry = new EmployeeAssessmentItemEntry([
            'id' => 2,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'assessment_type' => 'competency',
            'source_item_id' => 601,
            'item_key' => 'G_601',
            'rating' => 'E',
            'assessment_date' => now()->toDateString(),
        ]);

        $rows = CompetencyAssessmentHistoryBuilder::build(
            collect([$item]),
            collect([12 => $submission]),
            collect([12 => collect([$entry])]),
            collect(),
            12,
            [],
            new Collection,
        );

        $this->assertCount(1, $rows);
        $this->assertSame('Exceeds Expectations', $rows[0]['overall_rating']);
    }

    public function test_history_recalculates_total_and_average_when_snapshot_scores_are_zero(): void
    {
        $sectionLabel = 'HAND HYGIENE COMPETENCY';
        $item = new EmployeeCompetencyItem([
            'section' => $sectionLabel,
            'item' => 'Performs hand hygiene before resident contact',
            'order' => 1,
        ]);
        $item->id = 601;

        $submission = new EmployeeCompetencyAssessment([
            'id' => 94,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::DRAFT,
            'reviewer_name' => 'Reviewer One',
            'snapshot_json' => [
                'submitted_section_labels' => [$sectionLabel],
                'section_summaries' => [
                    $sectionLabel => [
                        'total_score' => 0,
                        'average_score' => 0,
                        'overall_rating' => '—',
                        'review_date' => '2026-07-08',
                    ],
                ],
            ],
        ]);

        $entry = new EmployeeAssessmentItemEntry([
            'id' => 3,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'assessment_type' => 'competency',
            'source_item_id' => 601,
            'item_key' => 'G_601',
            'rating' => 'E',
            'assessment_date' => now()->toDateString(),
        ]);

        $rows = CompetencyAssessmentHistoryBuilder::build(
            collect([$item]),
            collect([12 => $submission]),
            collect([12 => collect([$entry])]),
            collect(),
            12,
            [],
            new Collection,
        );

        $this->assertCount(1, $rows);
        $this->assertSame(3, $rows[0]['total_score']);
        $this->assertSame(3, $rows[0]['total_max_points']);
        $this->assertSame('3.00', $rows[0]['average_score']);
    }
}
