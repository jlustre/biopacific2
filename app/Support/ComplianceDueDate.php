<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Business rule: compliance items are due 30 days before the hire anniversary
 * or document expiration date (configurable via compliance.due_before_days).
 */
class ComplianceDueDate
{
    public static function offsetDays(): int
    {
        return max(0, (int) config('compliance.due_before_days', 30));
    }

    /**
     * Due date for an event that occurs on $eventDate (anniversary or expiration).
     */
    public static function before(CarbonInterface|string|null $eventDate): ?Carbon
    {
        if ($eventDate === null || $eventDate === '') {
            return null;
        }

        return Carbon::parse($eventDate)->startOfDay()->subDays(self::offsetDays());
    }

    /**
     * Assessment periods end the day before the hire anniversary.
     * Due = anniversary − offset = (date_to + 1 day) − offset.
     */
    public static function forPeriodEnd(CarbonInterface|string|null $dateTo): ?Carbon
    {
        if ($dateTo === null || $dateTo === '') {
            return null;
        }

        $anniversary = Carbon::parse($dateTo)->startOfDay()->addDay();

        return self::before($anniversary);
    }

    /**
     * @param  array{date_from?: mixed, date_to?: mixed}|object|null  $period
     */
    public static function forPeriod(array|object|null $period): ?Carbon
    {
        if ($period === null) {
            return null;
        }

        $dateTo = is_array($period)
            ? ($period['date_to'] ?? null)
            : ($period->date_to ?? null);

        return self::forPeriodEnd($dateTo);
    }

    public static function forExpiration(CarbonInterface|string|null $expiresAt): ?Carbon
    {
        return self::before($expiresAt);
    }

    /**
     * True when today is on or after the due date (and a due date exists).
     */
    public static function isPastDue(?CarbonInterface $dueDate, ?CarbonInterface $asOf = null): bool
    {
        if (! $dueDate) {
            return false;
        }

        $asOf = ($asOf ?? now())->copy()->startOfDay();

        return $asOf->gte($dueDate->copy()->startOfDay());
    }

    /**
     * Days from $asOf until due (negative when overdue).
     */
    public static function daysUntil(?CarbonInterface $dueDate, ?CarbonInterface $asOf = null): ?int
    {
        if (! $dueDate) {
            return null;
        }

        $asOf = ($asOf ?? now())->copy()->startOfDay();

        return (int) $asOf->diffInDays($dueDate->copy()->startOfDay(), false);
    }
}
