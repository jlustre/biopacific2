<?php



namespace Tests\Unit;



use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGItemReviews;

use App\Models\EmployeeCompetencyAssessment;

use App\Support\AssessmentWorkflowStatus;

use Livewire\Component;

use Tests\TestCase;



class ItemReviewsVisibilityTest extends TestCase

{

    public function test_employee_cannot_view_item_reviews_while_section_is_in_progress(): void

    {

        $component = $this->makeComponent(new EmployeeCompetencyAssessment([

            'employee_num' => 'EMP-100',

            'assessment_period_id' => 12,

            'status' => AssessmentWorkflowStatus::DRAFT,

            'responses' => [101 => ['response' => 'E']],

            'snapshot_json' => [],

        ]), true, [101 => 'E']);



        $this->assertFalse($component->itemReviewsVisibleToCurrentUser());

        $this->assertFalse($component->itemReviewVisibleToUser(101));

        $this->assertSame('', $component->itemReviewDisplayRating(101));

    }



    public function test_employee_can_view_item_reviews_after_section_is_submitted_for_confirmation(): void

    {

        $component = $this->makeComponent(new EmployeeCompetencyAssessment([

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

        ]), true, [101 => 'E']);



        $this->assertTrue($component->itemReviewsVisibleToCurrentUser());

        $this->assertTrue($component->itemReviewVisibleToUser(101));

        $this->assertSame('E', $component->itemReviewDisplayRating(101));

    }



    public function test_evaluator_can_view_item_reviews_during_in_progress(): void

    {

        $component = $this->makeComponent(new EmployeeCompetencyAssessment([

            'employee_num' => 'EMP-100',

            'assessment_period_id' => 12,

            'status' => AssessmentWorkflowStatus::DRAFT,

            'responses' => [101 => ['response' => 'M']],

            'snapshot_json' => [],

        ]), false, [101 => 'M']);



        $this->assertTrue($component->itemReviewsVisibleToCurrentUser());

        $this->assertTrue($component->itemReviewVisibleToUser(101));

        $this->assertSame('M', $component->itemReviewDisplayRating(101));

    }



    private function makeComponent(EmployeeCompetencyAssessment $assessment, bool $evaluatorActionsDisabled, array $responses): Component

    {

        return new class($assessment, $evaluatorActionsDisabled, $responses) extends Component

        {

            use ManagesPartGItemReviews;



            public const SECTION = 'HAND HYGIENE SKILLS';



            public string $employeeNum = 'EMP-100';



            public ?int $assessmentPeriodId = 12;



            public bool $evaluatorActionsDisabled;



            public array $responses;



            public function __construct(

                private EmployeeCompetencyAssessment $assessment,

                bool $evaluatorActionsDisabled,

                array $responses,

            ) {

                $this->evaluatorActionsDisabled = $evaluatorActionsDisabled;

                $this->responses = $responses;

            }



            protected function loadAssessment(): ?EmployeeCompetencyAssessment

            {

                return $this->assessment;

            }

        };

    }

}


