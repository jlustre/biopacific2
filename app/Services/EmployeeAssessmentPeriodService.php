<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use App\Models\EmployeeCompetencyAssessment;
use App\Support\AssessmentWorkflowStatus;
use App\Models\EmployeePerformanceAssessment;
use App\Support\EmployeeAssessmentPeriodCalculator;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmployeeAssessmentPeriodService
{
    public function periodsForEmployee(BPEmployee $employee): Collection
    {
        if (! $employee->employee_num) {
            return collect();
        }

        $this->syncAnnualPeriodsForEmployee($employee);

        return EmployeeAssessmentPeriod::query()
            ->where('employee_num', $employee->employee_num)
            ->orderByDesc('date_from')
            ->get();
    }

    public function syncAnnualPeriodsForEmployee(BPEmployee $employee, ?Carbon $on = null): void
    {
        if (! $employee->employee_num) {
            return;
        }

        foreach (EmployeeAssessmentPeriodCalculator::periodsToSync($employee, $on) as $window) {
            EmployeeAssessmentPeriod::query()->updateOrCreate(
                [
                    'employee_num' => $employee->employee_num,
                    'date_from' => $window['date_from'],
                    'date_to' => $window['date_to'],
                ],
                [
                    'period_year' => $window['period_year'],
                    'period_sequence' => 0,
                    'review_type' => 'A',
                ]
            );
        }
    }

    /**
     * @return array{date_from: string, date_to: string, period_year: int}|null
     */
    public function suggestedPeriodForEmployee(BPEmployee $employee, ?Carbon $on = null): ?array
    {
        return EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn($employee, $on);
    }

    public function resolveDefaultPeriodId(BPEmployee $employee, ?Carbon $on = null): ?int
    {
        $suggested = $this->suggestedPeriodForEmployee($employee, $on);
        if (! $suggested || ! $employee->employee_num) {
            return null;
        }

        return EmployeeAssessmentPeriod::query()
            ->where('employee_num', $employee->employee_num)
            ->where('date_from', $suggested['date_from'])
            ->where('date_to', $suggested['date_to'])
            ->value('id');
    }

    /**
     * Move reviewers to the current due annual period after a completed prior cycle.
     */
    public function shouldAdvanceToRecommendedPeriod(
        BPEmployee $employee,
        EmployeeAssessmentPeriod $selected,
        ?EmployeeAssessmentPeriod $recommended,
    ): bool {
        if (! $recommended || (int) $selected->id === (int) $recommended->id) {
            return false;
        }

        if (! EmployeeAssessmentPeriodCalculator::isPeriodLoadable($recommended)) {
            return false;
        }

        if ($selected->date_from && $recommended->date_from
            && $recommended->date_from->lte($selected->date_from)) {
            return false;
        }

        if (! $this->competencyCompleted($employee->employee_num, (int) $selected->id)) {
            return false;
        }

        if ($this->competencyCompleted($employee->employee_num, (int) $recommended->id)) {
            return false;
        }

        return true;
    }

    protected function competencyCompleted(
        string $employeeNum,
        int $periodId,
        ?EmployeeCompetencyAssessment $assessment = null,
    ): bool {
        $assessment ??= EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employeeNum)
            ->where('assessment_period_id', $periodId)
            ->first();

        return $assessment && AssessmentWorkflowStatus::isCompleted($assessment->workflowStatus());
    }

    public function resolveActivePeriodIdForReview(
        BPEmployee $employee,
        Collection $periods,
        ?int $requestedPeriodId,
        ?Carbon $on = null,
        bool $viewingHistoricalPeriod = false,
    ): ?int {
        $recommendedPeriodId = $this->resolveDefaultPeriodId($employee, $on);
        $recommendedPeriod = $recommendedPeriodId
            ? $periods->firstWhere('id', $recommendedPeriodId)
            : null;

        if (! $requestedPeriodId) {
            return $recommendedPeriodId;
        }

        $requestedPeriod = $periods->firstWhere('id', $requestedPeriodId);
        if (! $requestedPeriod) {
            return $recommendedPeriodId;
        }

        if (! EmployeeAssessmentPeriodCalculator::isPeriodLoadable($requestedPeriod)) {
            return null;
        }

        if ((string) $requestedPeriod->employee_num !== (string) $employee->employee_num) {
            return null;
        }

        if (! $viewingHistoricalPeriod
            && $recommendedPeriod
            && $this->shouldAdvanceToRecommendedPeriod($employee, $requestedPeriod, $recommendedPeriod)) {
            return (int) $recommendedPeriod->id;
        }

        return $requestedPeriodId;
    }

    public function periodBelongsToEmployee(int $periodId, string $employeeNum): bool
    {
        return EmployeeAssessmentPeriod::query()
            ->whereKey($periodId)
            ->where('employee_num', $employeeNum)
            ->exists();
    }

    public function assertPeriodBelongsToEmployee(int $periodId, string $employeeNum): void
    {
        if (! $this->periodBelongsToEmployee($periodId, $employeeNum)) {
            abort(403, 'This assessment period does not belong to this employee.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function modalDataForEmployee(BPEmployee $employee, ?string $reviewDate = null): array
    {
        $on = $reviewDate ? Carbon::parse($reviewDate)->startOfDay() : now()->startOfDay();
        $this->syncAnnualPeriodsForEmployee($employee, $on);

        $periods = $this->periodsForEmployee($employee);
        $recommended = EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn($employee, $on);
        $containing = EmployeeAssessmentPeriodCalculator::annualPeriodContaining($employee, $on);
        $anchor = EmployeeAssessmentPeriodCalculator::resolveAnchorDate($employee);

        $recommendedRow = null;
        if ($recommended) {
            $recommendedRow = $periods->first(
                fn (EmployeeAssessmentPeriod $period) => $period->date_from?->toDateString() === $recommended['date_from']
                    && $period->date_to?->toDateString() === $recommended['date_to']
            );
        }

        $performanceByPeriod = EmployeePerformanceAssessment::query()
            ->where('employee_num', $employee->employee_num)
            ->get()
            ->keyBy('assessment_period_id');
        $competencyByPeriod = EmployeeCompetencyAssessment::query()
            ->where('employee_num', $employee->employee_num)
            ->get()
            ->keyBy('assessment_period_id');

        $history = $periods->map(function (EmployeeAssessmentPeriod $period) use ($employee, $recommended, $performanceByPeriod, $competencyByPeriod) {
            $performance = $performanceByPeriod->get($period->id);
            $competency = $competencyByPeriod->get($period->id);

            $isRecommended = $recommended
                && $period->date_from?->toDateString() === $recommended['date_from']
                && $period->date_to?->toDateString() === $recommended['date_to'];

            $performanceStatus = '';
            if ($performance) {
                $performanceStatus = AssessmentWorkflowStatus::label($performance->workflowStatus());
            }

            $competencyStatus = '';
            if ($competency && filled($competency->status)) {
                $competencyStatus = AssessmentWorkflowStatus::label($competency->workflowStatus());
            }

            return [
                'id' => $period->id,
                'date_from' => $period->date_from?->format('Y-m-d'),
                'date_to' => $period->date_to?->format('Y-m-d'),
                'period_year' => $period->period_year,
                'review_type' => $period->review_type,
                'review_type_label' => $period->review_type === 'Q' ? 'Quarterly' : 'Annual',
                'can_load' => EmployeeAssessmentPeriodCalculator::isPeriodLoadable($period),
                'can_delete' => $period->canBeDeleted(),
                'is_recommended' => $isRecommended,
                'performance_status' => $performanceStatus,
                'competency_status' => $competencyStatus,
            ];
        })->values()->all();

        $usesRehire = method_exists($employee, 'usesRehireDate') && $employee->usesRehireDate();

        $recommendedPayload = null;
        if ($recommended) {
            $recommendedPerformance = $recommendedRow ? $performanceByPeriod->get($recommendedRow->id) : null;
            $recommendedCompetency = $recommendedRow ? $competencyByPeriod->get($recommendedRow->id) : null;

            $recommendedPayload = [
                'period_id' => $recommendedRow?->id,
                'exists' => $recommendedRow !== null,
                'date_from' => $recommended['date_from'],
                'date_to' => $recommended['date_to'],
                'period_year' => $recommended['period_year'],
                'can_load' => EmployeeAssessmentPeriodCalculator::isPeriodLoadable([
                    'period_year' => $recommended['period_year'],
                    'date_from' => $recommended['date_from'],
                    'date_to' => $recommended['date_to'],
                ]),
                'performance_status' => $recommendedPerformance
                    ? AssessmentWorkflowStatus::label($recommendedPerformance->workflowStatus())
                    : '',
                'competency_status' => $recommendedCompetency && filled($recommendedCompetency->status)
                    ? AssessmentWorkflowStatus::label($recommendedCompetency->workflowStatus())
                    : '',
            ];
        }

        return [
            'has_anchor' => $anchor !== null,
            'anchor_source' => $usesRehire ? 'Rehire Date' : 'Original Hire Date',
            'anchor_label' => $anchor?->format('Y-m-d') ?? '',
            'recommended' => $recommendedPayload,
            'containing' => $containing,
            'history' => $history,
            'loadable_year_range' => EmployeeAssessmentPeriodCalculator::loadableYearRange(),
        ];
    }
}
