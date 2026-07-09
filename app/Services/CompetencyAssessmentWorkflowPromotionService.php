<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Support\AssessmentWorkflowStatus;
use App\Support\CompetencyAssessmentWorkflowReadiness;

class CompetencyAssessmentWorkflowPromotionService
{
    public function promoteWhenReady(EmployeeCompetencyAssessment $assessment, ?BPEmployee $employee = null): bool
    {
        if ($assessment->workflowStatus() !== AssessmentWorkflowStatus::DRAFT) {
            return false;
        }

        $employee ??= BPEmployee::query()
            ->with('currentAssignment')
            ->where('employee_num', $assessment->employee_num)
            ->first();

        if (! $employee || ! CompetencyAssessmentWorkflowReadiness::isReadyForEmployeeConfirmation($assessment, $employee)) {
            return false;
        }

        $confirmationService = app(CompetencyAssessmentConfirmationService::class);
        $confirmationService->prepareForEmployeeConfirmation($assessment);

        if (! $assessment->submitted_at) {
            $assessment->submitted_at = now();
        }

        $assessment->save();

        app(\App\Http\Controllers\EmployeePerformanceAssessmentController::class)
            ->refreshCompetencyWorkflowState($assessment, regeneratePdf: false);

        $notificationService = app(AssessmentConfirmationNotificationService::class);
        $notificationService->notifyCompetencyAssessmentSubmitted($assessment, $employee);

        return true;
    }
}
