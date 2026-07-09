<?php

namespace App\Services;

use App\Mail\PerformanceAssessmentConfirmationMail;
use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Models\EmployeePerformanceAssessment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AssessmentConfirmationNotificationService
{
    public function buildEmployeeChecklistUrl(
        BPEmployee $employee,
        string $checklistTab,
        ?int $assessmentPeriodId = null
    ): string {
        $facilityId = $employee->relationLoaded('currentAssignment')
            ? $employee->currentAssignment?->facility_id
            : null;

        $query = array_filter([
            'tab' => 'checklist',
            'checklist_tab' => $checklistTab,
            'assessment_period_id' => $assessmentPeriodId,
            'facility' => $facilityId,
        ], fn ($value) => $value !== null && $value !== '');

        $baseUrl = route('employment.portal');

        return $query === [] ? $baseUrl : $baseUrl.'?'.http_build_query($query);
    }

    public function resolveEmployeeEmail(BPEmployee $employee): ?string
    {
        $email = trim((string) ($employee->email ?? ''));

        if ($email !== '') {
            return $email;
        }

        if ($employee->relationLoaded('user')) {
            $userEmail = trim((string) ($employee->user?->email ?? ''));

            return $userEmail !== '' ? $userEmail : null;
        }

        $userEmail = trim((string) ($employee->user()->value('email') ?? ''));

        return $userEmail !== '' ? $userEmail : null;
    }

    public function notifyPerformanceAssessmentSubmitted(
        EmployeePerformanceAssessment $assessment,
        BPEmployee $employee
    ): bool {
        $email = $this->resolveEmployeeEmail($employee);

        if ($email === null) {
            return false;
        }

        if ($assessment->relationLoaded('period')) {
            $period = $assessment->period;
        } else {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = config('app.name');
        if ($employee->relationLoaded('currentAssignment')
            && $employee->currentAssignment?->relationLoaded('facility')) {
            $facilityName = $employee->currentAssignment->facility?->name ?? $facilityName;
        }

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildEmployeeChecklistUrl(
                $employee,
                'partF',
                (int) $assessment->assessment_period_id
            ),
            $this->formatPeriodLabel($period),
            facilityName: $facilityName,
            notificationPurpose: 'employee_confirmation',
        ));

        return true;
    }

    public function notifyCompetencyAssessmentSubmitted(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee
    ): bool {
        $email = $this->resolveEmployeeEmail($employee);

        if ($email === null) {
            return false;
        }

        if ($assessment->relationLoaded('period')) {
            $period = $assessment->period;
        } else {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = config('app.name');
        if ($employee->relationLoaded('currentAssignment')
            && $employee->currentAssignment?->relationLoaded('facility')) {
            $facilityName = $employee->currentAssignment->facility?->name ?? $facilityName;
        }

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildEmployeeChecklistUrl(
                $employee,
                'partG',
                (int) $assessment->assessment_period_id
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $facilityName,
            notificationPurpose: 'employee_confirmation',
        ));

        return true;
    }

    public function buildReviewerChecklistUrl(
        BPEmployee $employee,
        string $checklistTab,
        ?int $assessmentPeriodId = null,
    ): string {
        return app(MemberDashboardService::class)->buildAdminEmployeeEditUrl(
            $employee->id,
            'checklist',
            $checklistTab,
            $assessmentPeriodId,
        );
    }

    public function resolveReviewerEmail(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $email = trim((string) (User::query()->whereKey($userId)->value('email') ?? ''));

        return $email !== '' ? $email : null;
    }

    public function notifyCompetencyAssessmentReturnedToReviewer(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        $email = $this->resolveReviewerEmail($assessment->submitted_by);

        if ($email === null) {
            return false;
        }

        $period = $assessment->relationLoaded('period') ? $assessment->period : null;
        if ($period === null && ! $assessment->relationLoaded('period')) {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = $this->resolveEmployeeFacilityName($employee);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildReviewerChecklistUrl(
                $employee,
                'partG',
                (int) $assessment->assessment_period_id,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $facilityName,
            notificationPurpose: 'reviewer_returned',
        ));

        return true;
    }

    public function notifyCompetencyAssessmentResubmittedToEmployee(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        $email = $this->resolveEmployeeEmail($employee);

        if ($email === null) {
            return false;
        }

        if ($assessment->relationLoaded('period')) {
            $period = $assessment->period;
        } else {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = $this->resolveEmployeeFacilityName($employee);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildEmployeeChecklistUrl(
                $employee,
                'partG',
                (int) $assessment->assessment_period_id
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $facilityName,
            notificationPurpose: 'employee_resubmitted',
        ));

        return true;
    }

    public function notifyPerformanceAssessmentResubmittedToEmployee(
        EmployeePerformanceAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        $email = $this->resolveEmployeeEmail($employee);

        if ($email === null) {
            return false;
        }

        if ($assessment->relationLoaded('period')) {
            $period = $assessment->period;
        } else {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = $this->resolveEmployeeFacilityName($employee);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildEmployeeChecklistUrl(
                $employee,
                'partF',
                (int) $assessment->assessment_period_id
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'performance',
            facilityName: $facilityName,
            notificationPurpose: 'employee_resubmitted',
        ));

        return true;
    }

    public function notifyCompetencyAssessmentReadyForReviewerApproval(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        $email = $this->resolveReviewerEmail($assessment->submitted_by);

        if ($email === null) {
            return false;
        }

        $period = $assessment->relationLoaded('period') ? $assessment->period : null;
        if ($period === null && ! $assessment->relationLoaded('period')) {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = $this->resolveEmployeeFacilityName($employee);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildReviewerChecklistUrl(
                $employee,
                'partG',
                (int) $assessment->assessment_period_id,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $facilityName,
            notificationPurpose: 'reviewer_approval',
        ));

        return true;
    }

    public function notifyPerformanceAssessmentReadyForReviewerApproval(
        EmployeePerformanceAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        $email = $this->resolveReviewerEmail($assessment->assessed_by);

        if ($email === null) {
            return false;
        }

        $period = $assessment->relationLoaded('period') ? $assessment->period : null;
        if ($period === null && ! $assessment->relationLoaded('period')) {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = $this->resolveEmployeeFacilityName($employee);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildReviewerChecklistUrl(
                $employee,
                'partF',
                (int) $assessment->assessment_period_id,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'performance',
            facilityName: $facilityName,
            notificationPurpose: 'reviewer_approval',
        ));

        return true;
    }

    public function notifyPerformanceAssessmentReturnedToReviewer(
        EmployeePerformanceAssessment $assessment,
        BPEmployee $employee,
    ): bool {
        $email = $this->resolveReviewerEmail($assessment->assessed_by);

        if ($email === null) {
            return false;
        }

        $period = $assessment->relationLoaded('period') ? $assessment->period : null;
        if ($period === null && ! $assessment->relationLoaded('period')) {
            $assessment->loadMissing('period');
            $period = $assessment->period;
        }

        $facilityName = $this->resolveEmployeeFacilityName($employee);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $this->buildReviewerChecklistUrl(
                $employee,
                'partF',
                (int) $assessment->assessment_period_id,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'performance',
            facilityName: $facilityName,
            notificationPurpose: 'reviewer_returned',
        ));

        return true;
    }

    public function notifyCompetencySectionSubmittedToEmployee(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
        string $sectionLabel,
    ): bool {
        return $this->sendCompetencySectionEmployeeMail(
            $assessment,
            $employee,
            $sectionLabel,
            'employee_confirmation',
        );
    }

    public function notifyCompetencySectionResubmittedToEmployee(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
        string $sectionLabel,
    ): bool {
        return $this->sendCompetencySectionEmployeeMail(
            $assessment,
            $employee,
            $sectionLabel,
            'employee_resubmitted',
        );
    }

    public function notifyCompetencySectionReadyForReviewerApproval(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
        string $sectionLabel,
    ): bool {
        $email = $this->resolveReviewerEmail($assessment->submitted_by);

        if ($email === null) {
            return false;
        }

        $period = $this->resolveAssessmentPeriod($assessment);
        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $sectionWorkflow->buildReviewerSectionChecklistUrl(
                $employee,
                (int) $assessment->assessment_period_id,
                $sectionLabel,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $this->resolveEmployeeFacilityName($employee),
            notificationPurpose: 'reviewer_approval',
            sectionLabel: $sectionLabel,
        ));

        return true;
    }

    public function notifyCompetencySectionReturnedToReviewer(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
        string $sectionLabel,
    ): bool {
        $email = $this->resolveReviewerEmail($assessment->submitted_by);

        if ($email === null) {
            return false;
        }

        $period = $this->resolveAssessmentPeriod($assessment);
        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $sectionWorkflow->buildReviewerSectionChecklistUrl(
                $employee,
                (int) $assessment->assessment_period_id,
                $sectionLabel,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $this->resolveEmployeeFacilityName($employee),
            notificationPurpose: 'reviewer_returned',
            sectionLabel: $sectionLabel,
        ));

        return true;
    }

    protected function sendCompetencySectionEmployeeMail(
        EmployeeCompetencyAssessment $assessment,
        BPEmployee $employee,
        string $sectionLabel,
        string $notificationPurpose,
    ): bool {
        $email = $this->resolveEmployeeEmail($employee);

        if ($email === null) {
            return false;
        }

        $period = $this->resolveAssessmentPeriod($assessment);
        $sectionWorkflow = app(\App\Services\CompetencySectionWorkflowService::class);

        Mail::to($email)->send(new PerformanceAssessmentConfirmationMail(
            $assessment,
            $employee,
            $sectionWorkflow->buildSectionChecklistUrl(
                $employee,
                (int) $assessment->assessment_period_id,
                $sectionLabel,
            ),
            $this->formatPeriodLabel($period),
            assessmentKind: 'competency',
            facilityName: $this->resolveEmployeeFacilityName($employee),
            notificationPurpose: $notificationPurpose,
            sectionLabel: $sectionLabel,
        ));

        return true;
    }

    protected function resolveAssessmentPeriod(EmployeeCompetencyAssessment $assessment): mixed
    {
        if ($assessment->relationLoaded('period')) {
            return $assessment->period;
        }

        $assessment->loadMissing('period');

        return $assessment->period;
    }

    protected function resolveEmployeeFacilityName(BPEmployee $employee): string
    {
        $facilityName = config('app.name');

        if ($employee->relationLoaded('currentAssignment')) {
            $assignment = $employee->getRelation('currentAssignment');

            return $assignment?->facility?->name ?? $facilityName;
        }

        $employee->loadMissing('currentAssignment.facility');

        return $employee->currentAssignment?->facility?->name ?? $facilityName;
    }

    protected function formatPeriodLabel(?EmployeeAssessmentPeriod $period): ?string
    {
        if (! $period) {
            return null;
        }

        $from = $period->date_from ? $period->date_from->format('M j, Y') : null;
        $to = $period->date_to ? $period->date_to->format('M j, Y') : null;

        if ($from && $to) {
            return "{$from} – {$to}";
        }

        return $from ?? $to ?? $period->name ?? null;
    }
}
