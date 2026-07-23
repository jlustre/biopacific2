<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Annual assessment windows are anchored to original_hire_dt, or rehire_dt when
 * Action is Rehire. The assessment year identifies the calendar year in which
 * the anniversary-based annual cycle ends.
 */
class EmployeeAssessmentPeriodCalculator
{
    public const LOADABLE_YEAR_OFFSET = 2;

    public static function resolveAnchorDate(BPEmployee $employee): ?Carbon
    {
        if (method_exists($employee, 'usesRehireDate') && $employee->usesRehireDate() && $employee->rehire_dt) {
            return Carbon::parse($employee->rehire_dt)->startOfDay();
        }

        if ($employee->original_hire_dt) {
            return Carbon::parse($employee->original_hire_dt)->startOfDay();
        }

        return null;
    }

    public static function firstAssessmentDueDate(BPEmployee $employee): ?Carbon
    {
        $anchor = self::resolveAnchorDate($employee);
        if (! $anchor) {
            return null;
        }

        $firstAnniversary = self::anniversaryInYear($anchor, $anchor->year + 1);

        return ComplianceDueDate::before($firstAnniversary);
    }

    /**
     * Due date for an annual assessment period (30 days before the hire anniversary
     * that ends the cycle).
     *
     * @param  EmployeeAssessmentPeriod|array{date_to?: mixed}|null  $period
     */
    public static function dueDateForPeriod(EmployeeAssessmentPeriod|array|null $period): ?Carbon
    {
        return ComplianceDueDate::forPeriod($period);
    }

    public static function dueDateForPeriodEnd(CarbonInterface|string|null $dateTo): ?Carbon
    {
        return ComplianceDueDate::forPeriodEnd($dateTo);
    }

    public static function isAssessmentDue(BPEmployee $employee, ?Carbon $on = null): bool
    {
        $dueDate = self::firstAssessmentDueDate($employee);
        if (! $dueDate) {
            return false;
        }

        return ($on ?? now())->copy()->startOfDay()->gte($dueDate);
    }

    public static function containingPeriodStartYear(Carbon $anchor, Carbon $on): int
    {
        $startYear = $on->year;
        $anniversaryOn = self::anniversaryInYear($anchor, $on->year);

        if ($on->lt($anniversaryOn)) {
            $startYear = $on->year - 1;
        }

        return max($anchor->year, $startYear);
    }

    /**
     * @return array{date_from: string, date_to: string, period_year: int}
     */
    public static function annualPeriodForStartYear(Carbon $anchor, int $startYear): array
    {
        $from = self::anniversaryInYear($anchor, $startYear);
        $to = self::anniversaryInYear($anchor, $startYear + 1)->subDay();

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'period_year' => $startYear,
        ];
    }

    /**
     * The assessment year is the year in which the annual cycle ends.
     *
     * @return array{date_from: string, date_to: string, period_year: int}|null
     */
    public static function annualPeriodForAssessmentYear(BPEmployee $employee, int $assessmentYear): ?array
    {
        $anchor = self::resolveAnchorDate($employee);
        $startYear = $assessmentYear - 1;

        if (! $anchor || $startYear < $anchor->year) {
            return null;
        }

        return self::annualPeriodForStartYear($anchor, $startYear);
    }

    /**
     * @return array{date_from: string, date_to: string, period_year: int}|null
     */
    public static function annualPeriodContaining(BPEmployee $employee, ?Carbon $on = null): ?array
    {
        $anchor = self::resolveAnchorDate($employee);
        if (! $anchor) {
            return null;
        }

        $on = ($on ?? now())->copy()->startOfDay();

        return self::annualPeriodForStartYear($anchor, self::containingPeriodStartYear($anchor, $on));
    }

    /**
     * @return array{date_from: string, date_to: string, period_year: int}|null
     */
    public static function annualPeriodForAssessmentOn(BPEmployee $employee, ?Carbon $on = null): ?array
    {
        $on = ($on ?? now())->copy()->startOfDay();
        $anchor = self::resolveAnchorDate($employee);
        if (! $anchor) {
            return null;
        }
        if (! self::isAssessmentDue($employee, $on)) {
            return self::annualPeriodForStartYear($anchor, $anchor->year);
        }

        return self::annualPeriodForAssessmentYear($employee, $on->year);
    }

    public static function syncEndYear(BPEmployee $employee, ?Carbon $on = null): ?int
    {
        $recommended = self::annualPeriodForAssessmentOn($employee, $on);
        if ($recommended) {
            return (int) $recommended['period_year'];
        }

        $anchor = self::resolveAnchorDate($employee);
        if (! $anchor) {
            return null;
        }

        $on = ($on ?? now())->copy()->startOfDay();
        $containingStart = self::containingPeriodStartYear($anchor, $on);

        return $containingStart > $anchor->year ? $containingStart - 1 : null;
    }

    /**
     * @return Collection<int, array{date_from: string, date_to: string, period_year: int}>
     */
    public static function periodsToSync(BPEmployee $employee, ?Carbon $on = null): Collection
    {
        $anchor = self::resolveAnchorDate($employee);
        if (! $anchor) {
            return collect();
        }

        $endYear = self::syncEndYear($employee, $on);
        if ($endYear === null) {
            return collect();
        }

        $periods = collect();
        for ($year = $anchor->year; $year <= $endYear; $year++) {
            $periods->push(self::annualPeriodForStartYear($anchor, $year));
        }

        return $periods->unique(fn (array $period) => $period['date_from'].'|'.$period['date_to']);
    }

    /**
     * @param  EmployeeAssessmentPeriod|array<string, mixed>  $period
     */
    public static function resolvePeriodYear(EmployeeAssessmentPeriod|array $period): ?int
    {
        if ($period instanceof EmployeeAssessmentPeriod) {
            if ($period->period_year) {
                return (int) $period->period_year;
            }

            $period = [
                'period_year' => $period->period_year,
                'date_from' => $period->date_from,
                'date_to' => $period->date_to,
            ];
        }

        if (! empty($period['period_year'])) {
            return (int) $period['period_year'];
        }

        if (! empty($period['date_from'])) {
            return (int) Carbon::parse($period['date_from'])->year;
        }

        if (! empty($period['date_to'])) {
            return (int) Carbon::parse($period['date_to'])->year;
        }

        return null;
    }

    /**
     * @return array{min: int, max: int, current: int}
     */
    public static function loadableYearRange(?int $currentYear = null): array
    {
        $current = $currentYear ?? (int) date('Y');

        return [
            'min' => $current - self::LOADABLE_YEAR_OFFSET,
            'max' => $current + self::LOADABLE_YEAR_OFFSET,
            'current' => $current,
        ];
    }

    /**
     * @param  EmployeeAssessmentPeriod|array<string, mixed>  $period
     */
    public static function isPeriodLoadable(EmployeeAssessmentPeriod|array $period, ?int $currentYear = null): bool
    {
        $year = self::resolvePeriodYear($period);
        if (! $year) {
            return false;
        }

        $range = self::loadableYearRange($currentYear);

        return $year >= $range['min'] && $year <= $range['max'];
    }

    private static function anniversaryInYear(Carbon $anchor, int $year): Carbon
    {
        $monthStart = Carbon::create($year, $anchor->month, 1)->startOfDay();
        $day = min($anchor->day, $monthStart->daysInMonth);

        return $monthStart->day($day);
    }
}
