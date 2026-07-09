<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\User;
use App\Services\CompetencySectionWorkflowService;
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
        ?string $sectionLabel = null,
    ): ?array {
        if (! $assessmentPeriodId || blank($sectionLabel)) {
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
        $evaluatorActionsDisabled = AssessmentEvaluatorAuthorization::isEvaluatorActionBlocked($user, $employeeNum);

        $sectionWorkflow = app(CompetencySectionWorkflowService::class);

        if ($assessment) {
            $sectionWorkflow->syncSubmittedSectionsWithoutWorkflow($assessment);
            $assessment->refresh();
        }

        $workflowStatus = $assessment
            ? $sectionWorkflow->sectionStatus($assessment, $sectionLabel)
            : AssessmentWorkflowStatus::DRAFT;

        $sectionState = $assessment
            ? $sectionWorkflow->sectionWorkflow($assessment, $sectionLabel)
            : [];

        $employeeCanConfirm = AssessmentWorkflowStatus::employeeCanConfirm($workflowStatus);
        $reviewerCanApprove = AssessmentWorkflowStatus::reviewerCanApprove($workflowStatus);

        $assessmentLocked = AssessmentWorkflowStatus::isCompleted($workflowStatus)
            || (
                $assessment
                && AssessmentWorkflowStatus::isCompleted($assessment->workflowStatus())
                && (
                    $sectionWorkflow->sectionIsSubmitted($assessment, $sectionLabel)
                    || $sectionWorkflow->sectionWorkflow($assessment, $sectionLabel) !== []
                )
            );

        $contentChangedSinceEmployeeConfirmation = $assessment
            && $reviewerCanApprove
            && $sectionWorkflow->sectionHasChangedSinceEmployeeConfirmation($assessment, $sectionLabel);

        $returnedToReviewer = $assessment
            && $sectionWorkflow->sectionWasReturnedToReviewer($assessment, $sectionLabel);

        $employeeSignaturePath = (string) ($sectionState['employee_signature_path'] ?? '');

        $canApprove = $reviewerCanApprove
            && ! $evaluatorActionsDisabled
            && ! $contentChangedSinceEmployeeConfirmation
            && filled($employeeSignaturePath);

        $canResubmitForEmployeeConfirmation = ($reviewerCanApprove && ! $evaluatorActionsDisabled && $contentChangedSinceEmployeeConfirmation)
            || (
                $workflowStatus === AssessmentWorkflowStatus::DRAFT
                && ! $evaluatorActionsDisabled
                && $assessment
                && $sectionWorkflow->sectionWasReturnedToReviewer($assessment, $sectionLabel)
            );

        $employeeSignedAt = $sectionState['employee_signed_at'] ?? null;
        $reviewerSignedAt = $sectionState['reviewer_signed_at'] ?? null;

        $sectionComments = $assessment
            ? $sectionWorkflow->resolveSectionComments($assessment, $sectionLabel)
            : ['employee_comments' => '', 'reviewer_comments' => ''];

        return [
            'employee' => $employee,
            'assessment' => $assessment,
            'sectionLabel' => $sectionLabel,
            'evaluatorActionsDisabled' => $evaluatorActionsDisabled,
            'workflowStatus' => $workflowStatus,
            'assessmentLocked' => $assessmentLocked,
            'employeeCanConfirm' => $employeeCanConfirm,
            'reviewerCanApprove' => $reviewerCanApprove,
            'contentChangedSinceEmployeeConfirmation' => $contentChangedSinceEmployeeConfirmation,
            'canApprove' => $canApprove,
            'canResubmitForEmployeeConfirmation' => $canResubmitForEmployeeConfirmation,
            'returnedToReviewer' => $returnedToReviewer,
            'statusLabel' => $assessment
                ? $sectionWorkflow->sectionDisplayStatusLabel(
                    $assessment,
                    $sectionLabel,
                    $sectionWorkflow->sectionIsSubmitted($assessment, $sectionLabel),
                )
                : 'Not Started',
            'employeeAckDateValue' => filled($employeeSignedAt)
                ? Carbon::parse($employeeSignedAt)->format('M j, Y g:i A')
                : '',
            'reviewerSignDateValue' => filled($reviewerSignedAt)
                ? Carbon::parse($reviewerSignedAt)->format('M j, Y g:i A')
                : '',
            'employeeCommentsValue' => (string) ($sectionComments['employee_comments'] ?? ''),
            'employeeFullName' => $employee->formattedFullName(),
            'confirmationSnapshot' => $sectionState['employee_confirmation_snapshot'] ?? null,
        ];
    }
}
