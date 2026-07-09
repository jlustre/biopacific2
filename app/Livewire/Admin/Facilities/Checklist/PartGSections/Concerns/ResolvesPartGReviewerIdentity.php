<?php

namespace App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns;

use App\Models\EmployeeCompetencyAssessment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait ResolvesPartGReviewerIdentity
{
    protected function resolveAuthenticatedReviewerTitle(?User $user = null): string
    {
        $user ??= Auth::user();
        if (! $user instanceof User) {
            return '';
        }

        $reviewerEmployee = $user->resolvedBpEmployee(['currentAssignment.position']);

        return trim((string) (
            $reviewerEmployee?->currentAssignment?->position?->title
            ?? $reviewerEmployee?->position
            ?? ''
        ));
    }

    protected function hydrateReviewerIdentityFromAssessment(?EmployeeCompetencyAssessment $assessment): void
    {
        if (! $assessment) {
            return;
        }

        if (filled($assessment->reviewer_name)) {
            $this->reviewerName = (string) $assessment->reviewer_name;
        }

        $storedTitle = trim((string) ($assessment->reviewer_title ?? ''));
        if ($storedTitle !== '') {
            $this->reviewerTitle = $storedTitle;

            return;
        }

        if ($this->reviewerTitle !== '') {
            return;
        }

        if (! $assessment->submitted_by) {
            return;
        }

        $reviewer = User::query()->find($assessment->submitted_by);
        if (! $reviewer) {
            return;
        }

        $resolvedTitle = $this->resolveAuthenticatedReviewerTitle($reviewer);
        if ($resolvedTitle !== '') {
            $this->reviewerTitle = $resolvedTitle;
        }
    }

    protected function loadReviewerIdentityFromStorage(): void
    {
        if (! $this->assessmentPeriodId) {
            return;
        }

        $assessment = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $this->employeeNum)
            ->where('assessment_period_id', $this->assessmentPeriodId)
            ->first();

        $this->hydrateReviewerIdentityFromAssessment($assessment);
    }

    protected function refreshReviewerIdentityForPersist(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        if ($this->reviewerName === '') {
            $this->reviewerName = (string) ($user->name ?? '');
        }

        $resolvedTitle = $this->resolveAuthenticatedReviewerTitle($user);
        if ($resolvedTitle !== '') {
            $this->reviewerTitle = $resolvedTitle;
        }
    }
}
