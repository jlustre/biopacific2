<?php

namespace Tests\Unit;

use App\Support\AssessmentWorkflowStatus;
use App\Support\CompetencyAssessmentHistoryBuilder;
use ReflectionMethod;
use Tests\TestCase;

class CompetencyAssessmentWorkflowReadinessTest extends TestCase
{
    public function test_draft_section_submit_status_label_is_section_submitted(): void
    {
        $method = new ReflectionMethod(CompetencyAssessmentHistoryBuilder::class, 'resolveSectionStatus');
        $method->setAccessible(true);

        $label = $method->invoke(null, AssessmentWorkflowStatus::DRAFT, true);

        $this->assertSame('Section submitted', $label);
    }
}
