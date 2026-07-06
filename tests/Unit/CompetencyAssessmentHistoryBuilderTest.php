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
            'id' => 501,
            'section' => $sectionLabel,
            'item' => 'Demonstrates accurate medication pass',
            'order' => 1,
        ]);

        $submission = new EmployeeCompetencyAssessment([
            'id' => 91,
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            'reviewer_name' => 'Reviewer One',
            'snapshot_json' => [
                'section_summaries' => [
                    $sectionLabel => [
                        'total_score' => 3,
                        'average_score' => 3,
                        'overall_rating' => 'Exceeds',
                        'review_date' => '2026-07-06',
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
}
