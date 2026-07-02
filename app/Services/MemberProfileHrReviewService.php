<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\MemberEmergencyContact;
use App\Models\User;
use App\Support\MemberPortal\ProfileHrStatus;
use Illuminate\Support\Collection;

class MemberProfileHrReviewService
{
    public function __construct(
        protected MemberDashboardService $memberDashboard,
    ) {}

    /**
     * @return array{
     *     portal_complete: bool,
     *     emergency_complete: bool,
     *     ready_to_submit: bool,
     *     hr_confirmed: bool,
     *     pending_hr: bool,
     *     profile_complete_percent: int,
     *     missing: list<string>
     * }
     */
    public function assess(User $user, ?BPEmployee $employee = null): array
    {
        $employee = $employee ?? $user->resolvedBpEmployee(['phone', 'address', 'phones']);

        $missing = [];

        if (! filled($user->name)) {
            $missing[] = 'display name';
        }
        if (! filled($user->email)) {
            $missing[] = 'email address';
        }
        if (! $user->hasVerifiedEmail()) {
            $missing[] = 'verified email';
        }

        $portalComplete = $missing === [];

        $primaryContact = $user->emergencyContacts()
            ->where('is_primary', true)
            ->first();

        $emergencyComplete = $primaryContact
            && filled($primaryContact->first_name)
            && filled($primaryContact->last_name)
            && filled($primaryContact->phone);

        if (! $emergencyComplete) {
            $missing[] = 'primary emergency contact';
        }

        $status = (string) ($user->profile_hr_status ?? ProfileHrStatus::INCOMPLETE);

        return [
            'portal_complete' => $portalComplete,
            'emergency_complete' => $emergencyComplete,
            'ready_to_submit' => $portalComplete && $emergencyComplete,
            'hr_confirmed' => $status === ProfileHrStatus::CONFIRMED,
            'pending_hr' => $status === ProfileHrStatus::PENDING_HR,
            'profile_complete_percent' => $this->profilePercent($user, $employee, $emergencyComplete),
            'missing' => $missing,
        ];
    }

    public function submitForHrReview(User $user, ?BPEmployee $employee = null): bool
    {
        $assessment = $this->assess($user, $employee);

        if (! $assessment['ready_to_submit']) {
            return false;
        }

        if ($assessment['hr_confirmed']) {
            return true;
        }

        if ($assessment['pending_hr']) {
            return true;
        }

        $user->forceFill([
            'profile_hr_status' => ProfileHrStatus::PENDING_HR,
            'profile_submitted_at' => now(),
        ])->save();

        return true;
    }

    public function confirmProfile(User $user, User $reviewer): void
    {
        $user->forceFill([
            'profile_hr_status' => ProfileHrStatus::CONFIRMED,
            'profile_confirmed_at' => now(),
            'profile_confirmed_by' => $reviewer->id,
        ])->save();
    }

    /**
     * @param  Collection<int, BPEmployee>  $team
     * @return list<array<string, mixed>>
     */
    public function pendingHrQueueItems(Collection $team): array
    {
        if ($team->isEmpty()) {
            return [];
        }

        $userIds = $team->pluck('user_id')->filter()->values();

        $users = User::query()
            ->where('profile_hr_status', ProfileHrStatus::PENDING_HR)
            ->where(function ($query) use ($userIds, $team) {
                if ($userIds->isNotEmpty()) {
                    $query->whereIn('id', $userIds);
                }

                $emails = $team->pluck('email')->filter()->unique()->values();
                if ($emails->isNotEmpty()) {
                    $query->orWhereIn('email', $emails);
                }
            })
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($team as $employee) {
            $user = $employee->user_id ? $users->get($employee->user_id) : null;

            if (! $user && $employee->email) {
                $user = $users->first(fn (User $candidate) => strcasecmp((string) $candidate->email, (string) $employee->email) === 0);
            }

            if (! $user || $user->profile_hr_status !== ProfileHrStatus::PENDING_HR) {
                continue;
            }

            $submitted = $user->profile_submitted_at?->timezone(config('app.timezone'))->diffForHumans() ?? 'recently';

            $items[] = [
                'employee_num' => $employee->employee_num,
                'name' => $employee->formalName(),
                'last_name' => (string) ($employee->last_name ?? ''),
                'first_name' => (string) ($employee->first_name ?? ''),
                'middle_name' => (string) ($employee->middle_name ?? ''),
                'position' => $employee->currentAssignment?->position?->title ?? 'Team Member',
                'department' => $employee->currentAssignment?->department?->name ?? '—',
                'status' => 'pending_hr',
                'priority' => 'high',
                'summary' => 'Profile submitted for HR confirmation · ' . $submitted,
                'manage_url' => route('admin.employees.edit', [
                    'employee' => $employee->employee_num,
                    'tab' => 'personal',
                ]) . '#profile-hr-review',
                'kind' => 'profile_hr_review',
            ];
        }

        return $items;
    }

    protected function profilePercent(User $user, ?BPEmployee $employee, bool $emergencyComplete): int
    {
        $score = 0;

        if (filled($user->name)) {
            $score += 15;
        }
        if (filled($user->email)) {
            $score += 15;
        }
        if ($user->hasVerifiedEmail()) {
            $score += 15;
        }
        if ($employee?->displayPhoneNumber()) {
            $score += 15;
        }
        if ($this->hasMailingAddress($employee)) {
            $score += 15;
        }
        if ($emergencyComplete) {
            $score += 25;
        }

        return min(100, $score);
    }

    protected function hasMailingAddress(?BPEmployee $employee): bool
    {
        $address = $employee?->address;
        if (! $address) {
            return false;
        }

        return filled($address->address1) && filled($address->city) && filled($address->state);
    }
}
