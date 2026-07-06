<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\User;
use App\Services\CompetencyAssessmentConfirmationService;
use Illuminate\Support\Carbon;

class PartGAcknowledgementViewData
{
    /**
     * @return array<string, mixed>|null
     */
    public static function build(
        string $employeeNum,
        ?int $assessmentPeriodId,
        ?User $user = null,
    ): ?array {
        if (! $assessmentPeriodId) {
            return null;
        }

        $employee = BPEmployee::query()
            ->where('employee_num', $employeeNum)
            ->first();

        if (! $employee) {
            return null;
        }

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $assessmentPeriodId)
            ->first();

        $user ??= auth()->user();
        $evaluatorActionsDisabled = PreventsSelfAssessment::isSelfAssessment($user, $employeeNum);

        $workflowStatus = $assessment?->workflowStatus() ?? AssessmentWorkflowStatus::DRAFT;
        $assessmentLocked = AssessmentWorkflowStatus::isLocked($workflowStatus);
        $employeeCanConfirm = AssessmentWorkflowStatus::employeeCanConfirm($workflowStatus);
        $reviewerCanApprove = AssessmentWorkflowStatus::reviewerCanApprove($workflowStatus);

        $confirmationService = app(CompetencyAssessmentConfirmationService::class);
        $contentChangedSinceEmployeeConfirmation = $assessment
            && $reviewerCanApprove
            && $confirmationService->hasChangedSinceEmployeeConfirmation($assessment);

        $canApprove = $reviewerCanApprove
            && ! $evaluatorActionsDisabled
            && ! $contentChangedSinceEmployeeConfirmation
            && filled($assessment?->employee_signature_path);

        $readyForEmployeeConfirmation = $assessment instanceof EmployeeCompetencyAssessment
            && self::isReadyForEmployeeConfirmation($assessment, $employee);

        $canSubmitForEmployeeConfirmation = $workflowStatus === AssessmentWorkflowStatus::DRAFT
            && ! $evaluatorActionsDisabled
            && $readyForEmployeeConfirmation;

        $employeeSignedAt = $assessment?->employee_signed_at;
        $reviewerSignedAt = $assessment?->reviewer_signed_at ?? $assessment?->review_date;

        return [
            'employee' => $employee,
            'assessment' => $assessment,
            'evaluatorActionsDisabled' => $evaluatorActionsDisabled,
            'workflowStatus' => $workflowStatus,
            'assessmentLocked' => $assessmentLocked,
            'employeeCanConfirm' => $employeeCanConfirm,
            'reviewerCanApprove' => $reviewerCanApprove,
            'contentChangedSinceEmployeeConfirmation' => $contentChangedSinceEmployeeConfirmation,
            'canApprove' => $canApprove,
            'readyForEmployeeConfirmation' => $readyForEmployeeConfirmation,
            'canSubmitForEmployeeConfirmation' => $canSubmitForEmployeeConfirmation,
            'statusLabel' => AssessmentWorkflowStatus::label($workflowStatus),
            'employeeAckDateValue' => $employeeSignedAt
                ? Carbon::parse($employeeSignedAt)->format('M j, Y g:i A')
                : '',
            'reviewerSignDateValue' => filled($reviewerSignedAt)
                ? Carbon::parse($reviewerSignedAt)->format('M j, Y g:i A')
                : '',
            'employeeCommentsValue' => (string) ($assessment?->employee_comments ?? ''),
            'employeeFullName' => $employee->formattedFullName(),
            'confirmationSnapshot' => $assessment?->employee_confirmation_snapshot,
        ];
    }

    public static function isReadyForEmployeeConfirmation(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        return CompetencyAssessmentWorkflowReadiness::isReadyForEmployeeConfirmation($assessment, $employee);
    }
}
