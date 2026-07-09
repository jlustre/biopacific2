<?php

namespace Tests\Unit;

use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGItemReviews;
use App\Models\EmployeeCompetencyAssessment;
use App\Support\AssessmentWorkflowStatus;
use Livewire\Component;
use Tests\TestCase;

class SectionItemReviewsLockedTest extends TestCase
{
    public function test_section_stays_editable_when_only_other_sections_are_in_workflow(): void
    {
        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'snapshot_json' => [
                'submitted_section_labels' => ['LICENSED NURSE eMAR COMPETENCY'],
                'section_workflow' => [
                    'LICENSED NURSE eMAR COMPETENCY' => [
                        'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                    ],
                ],
            ],
        ]);

        $component = new class($assessment) extends Component
        {
            use ManagesPartGItemReviews;

            public const SECTION = 'HAND HYGIENE SKILLS';

            public string $employeeNum = 'EMP-100';

            public ?int $assessmentPeriodId = 12;

            public bool $evaluatorActionsDisabled = false;

            public bool $sectionExcluded = false;

            public bool $assessmentLocked = true;

            public array $responses = [];

            public function __construct(private EmployeeCompetencyAssessment $assessment) {}

            protected function loadAssessment(): ?EmployeeCompetencyAssessment
            {
                return $this->assessment;
            }
        };

        $this->assertFalse($component->sectionItemReviewsLocked());
    }

    public function test_section_locks_when_its_own_workflow_awaits_employee_confirmation(): void
    {
        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'snapshot_json' => [
                'section_workflow' => [
                    'HAND HYGIENE SKILLS' => [
                        'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                    ],
                ],
            ],
        ]);

        $component = new class($assessment) extends Component
        {
            use ManagesPartGItemReviews;

            public const SECTION = 'HAND HYGIENE SKILLS';

            public string $employeeNum = 'EMP-100';

            public ?int $assessmentPeriodId = 12;

            public bool $evaluatorActionsDisabled = false;

            public bool $sectionExcluded = false;

            public bool $assessmentLocked = false;

            public array $responses = [];

            public function __construct(private EmployeeCompetencyAssessment $assessment) {}

            protected function loadAssessment(): ?EmployeeCompetencyAssessment
            {
                return $this->assessment;
            }
        };

        $this->assertTrue($component->sectionItemReviewsLocked());
    }

    public function test_draft_submit_actions_remain_available_when_global_assessment_is_locked(): void
    {
        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
            'snapshot_json' => [
                'submitted_section_labels' => ['LICENSED NURSE eMAR COMPETENCY'],
                'section_workflow' => [
                    'LICENSED NURSE eMAR COMPETENCY' => [
                        'status' => AssessmentWorkflowStatus::FOR_EMPLOYEE_CONFIRMATION,
                    ],
                ],
            ],
        ]);

        $component = new class($assessment) extends Component
        {
            use ManagesPartGItemReviews;
            use \App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionWorkflowUi;

            public const SECTION = 'HAND HYGIENE SKILLS';

            public string $employeeNum = 'EMP-100';

            public ?int $assessmentPeriodId = 12;

            public bool $evaluatorActionsDisabled = false;

            public bool $sectionExcluded = false;

            public bool $assessmentLocked = true;

            public array $responses = [];

            public function __construct(private EmployeeCompetencyAssessment $assessment) {}

            protected function loadAssessment(): ?EmployeeCompetencyAssessment
            {
                return $this->assessment;
            }
        };

        $this->assertTrue($component->assessmentLocked);
        $this->assertFalse($component->sectionItemReviewsLocked());
        $this->assertTrue($component->showDraftSubmitActions());
    }

    public function test_reviewer_summary_comments_stay_editable_during_reviewer_approval(): void
    {
        $assessment = new EmployeeCompetencyAssessment([
            'employee_num' => 'EMP-100',
            'assessment_period_id' => 12,
            'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
            'snapshot_json' => [
                'section_workflow' => [
                    'HAND HYGIENE SKILLS' => [
                        'status' => AssessmentWorkflowStatus::FOR_REVIEWER_APPROVAL,
                    ],
                ],
            ],
        ]);

        $component = new class($assessment) extends Component
        {
            use ManagesPartGItemReviews;
            use \App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionWorkflowUi;

            public const SECTION = 'HAND HYGIENE SKILLS';

            public string $employeeNum = 'EMP-100';

            public ?int $assessmentPeriodId = 12;

            public bool $evaluatorActionsDisabled = false;

            public bool $sectionExcluded = false;

            public bool $assessmentLocked = false;

            public array $responses = [];

            public function __construct(private EmployeeCompetencyAssessment $assessment) {}

            protected function loadAssessment(): ?EmployeeCompetencyAssessment
            {
                return $this->assessment;
            }
        };

        $this->assertTrue($component->sectionItemReviewsLocked());
        $this->assertFalse($component->reviewerSummaryCommentsLocked());
        $this->assertTrue($component->summaryFieldsLocked());
    }
}
