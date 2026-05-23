<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\EmployeeAssessmentPeriod;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Annual assessment windows are anchored to original_hire_dt, or rehire_dt when
 * Action is Rehire. Default assessment period = prior completed annual cycle.
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

    public static function containingPeriodStartYear(Carbon $anchor, Carbon $on): int
    {
        $startYear = $on->year;
        $anniversaryOn = Carbon::create($on->year, $anchor->month, $anchor->day)->startOfDay();

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
        $from = Carbon::create($startYear, $anchor->month, $anchor->day)->startOfDay();
        $to = Carbon::create($startYear + 1, $anchor->month, $anchor->day)->subDay()->startOfDay();

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'period_year' => $startYear,
        ];
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
        $anchor = self::resolveAnchorDate($employee);
        if (! $anchor) {
            return null;
        }

        $on = ($on ?? now())->copy()->startOfDay();
        $priorStart = self::containingPeriodStartYear($anchor, $on) - 1;

        if ($priorStart < $anchor->year) {
            return null;
        }

        return self::annualPeriodForStartYear($anchor, $priorStart);
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
}
