<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\MemberProfileExpiringItem;
use App\Models\MemberProfileRecognition;
use App\Models\Upload;
use App\Models\User;
use Carbon\Carbon;

class PersonalProfilePanelsService
{
    public const URGENT_DAYS = 60;

    public const WARNING_DAYS = 120;

    /**
     * @return list<array{label: string, days_left: int, expires_at: string, tone: string, tone_label: string}>
     */
    public function upcomingExpirations(User $user, ?BPEmployee $employee = null): array
    {
        $today = Carbon::today();
        $items = collect();

        MemberProfileExpiringItem::query()
            ->where('user_id', $user->id)
            ->whereDate('expires_at', '>=', $today)
            ->orderBy('expires_at')
            ->get()
            ->each(function (MemberProfileExpiringItem $row) use ($items, $today) {
                $items->push($this->mapExpirationRow($row->label, $row->expires_at, $today));
            });

        if ($employee) {
            Upload::query()
                ->where('employee_num', $employee->employee_num)
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', '>=', $today)
                ->whereHas('uploadType', fn ($q) => $q->where('is_license_or_certification', true))
                ->with('uploadType')
                ->orderBy('expires_at')
                ->get()
                ->each(function (Upload $upload) use ($items, $today) {
                    $label = $upload->uploadType?->name ?? 'Credential document';
                    $items->push($this->mapExpirationRow($label, $upload->expires_at, $today));
                });
        }

        return $items
            ->unique(fn (array $row) => $row['label'] . '|' . $row['expires_at'])
            ->sortBy('days_left')
            ->values()
            ->all();
    }

    /**
     * @return list<array{icon: string, label: string}>
     */
    public function recognitions(User $user, ?BPEmployee $employee = null): array
    {
        $items = collect();

        if ($employee?->original_hire_dt) {
            $hire = Carbon::parse($employee->original_hire_dt);
            $anniversary = $this->nextAnnualDate($hire, Carbon::today());
            $items->push([
                'icon' => '🎉',
                'label' => 'Work Anniversary: ' . $anniversary->format('M j, Y'),
                'sort_key' => $anniversary->format('Y-m-d'),
            ]);
        }

        if ($employee?->formattedDateOfBirth('F j')) {
            $items->push([
                'icon' => '🎂',
                'label' => 'Birthday: ' . $employee->formattedDateOfBirth('F j'),
                'sort_key' => '0000-' . $employee->dateOfBirthInputValue(),
            ]);
        }

        MemberProfileRecognition::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('recognized_on')
            ->get()
            ->each(function (MemberProfileRecognition $row) use ($items) {
                $items->push([
                    'icon' => $row->icon ?: '🏆',
                    'label' => $row->label,
                    'sort_key' => $row->recognized_on?->format('Y-m-d') ?? '9999-12-31',
                ]);
            });

        return $items
            ->sortBy('sort_key')
            ->map(fn (array $row) => ['icon' => $row['icon'], 'label' => $row['label']])
            ->values()
            ->all();
    }

    protected function mapExpirationRow(string $label, $expiresAt, Carbon $today): array
    {
        $expiry = Carbon::parse($expiresAt)->startOfDay();
        $daysLeft = (int) $today->diffInDays($expiry, false);
        $tone = $this->toneForDaysLeft($daysLeft);

        return [
            'label' => $label,
            'days_left' => max(0, $daysLeft),
            'expires_at' => $expiry->toDateString(),
            'tone' => $tone,
            'tone_label' => $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left',
        ];
    }

    public function toneForDaysLeft(int $daysLeft): string
    {
        if ($daysLeft <= self::URGENT_DAYS) {
            return 'urgent';
        }

        if ($daysLeft <= self::WARNING_DAYS) {
            return 'warning';
        }

        return 'ok';
    }

    protected function nextAnnualDate(Carbon $sourceDate, Carbon $today): Carbon
    {
        $candidate = Carbon::create($today->year, $sourceDate->month, $sourceDate->day)->startOfDay();

        if ($candidate->lt($today)) {
            $candidate = $candidate->addYear();
        }

        return $candidate;
    }
}
