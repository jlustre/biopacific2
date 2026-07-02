<?php

namespace App\Http\Controllers\Concerns;

use App\Helpers\FacilityDataHelper;
use App\Models\Facility;
use App\Models\News;
use App\Services\MemberDashboardService;

trait ProvidesMemberPortalContext
{
    protected function memberPortalContext($user): array
    {
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee([
                'currentAssignment.position.reportsToPosition',
                'currentAssignment.reportsToPosition',
                'currentAssignment.facility',
                'currentAssignment.department',
                'phone',
                'address',
            ])
            : null;

        $facility = $this->resolveMemberFacility($user, $employee);
        $displayName = trim(($employee?->first_name ?? '') . ' ' . ($employee?->last_name ?? '')) ?: ($user->name ?? 'Employee');
        $nameParts = preg_split('/\s+/', trim($displayName), 2);
        $firstName = $nameParts[0] ?? $displayName;
        $lastName = $nameParts[1] ?? '';
        $firstNameOnly = explode(' ', trim($displayName))[0] ?? $displayName;
        $portalAlerts = app(MemberDashboardService::class)->buildPortalAlerts($user);

        return [
            'user' => $user,
            'employee' => $employee,
            'facility' => $facility,
            'displayName' => $displayName,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'firstNameOnly' => $firstNameOnly,
            'positionTitle' => $employee?->currentAssignment?->position?->title ?? 'Team Member',
            'departmentName' => $employee?->currentAssignment?->department?->name ?? '—',
            'reportsToName' => $this->resolveReportsToName($employee),
            'facilityName' => $employee?->currentAssignment?->facility?->name ?? ($facility?->name ?? '—'),
            'employeeId' => $employee?->employee_num ?? '—',
            'hireDate' => $employee?->original_hire_dt
                ? \Carbon\Carbon::parse($employee->original_hire_dt)->format('M j, Y')
                : '—',
            'initials' => $user->initials ?? strtoupper(substr($firstNameOnly, 0, 1)),
            'avatarUrl' => $user->profileAvatarUrl(),
            'profileComplete' => $this->calculatePersonalProfileComplete($user, $employee),
            'documentsNeededCount' => (int) ($portalAlerts['documents_needed'] ?? 0),
            'portalNotifications' => $portalAlerts['items'] ?? [],
            'portalNotificationCount' => (int) ($portalAlerts['count'] ?? 0),
            'newsEventsCount' => $this->countMemberNewsEvents($facility),
            'userRoles' => $user->rolesForDisplay(),
            'primaryRoleLabel' => $user->primaryRoleLabel(),
        ];
    }

    protected function calculatePersonalProfileComplete($user, $employee): int
    {
        $score = 0;

        if (filled($user->name)) {
            $score += 20;
        }
        if (filled($user->email)) {
            $score += 20;
        }
        if ($user->hasVerifiedEmail()) {
            $score += 20;
        }
        if ($employee?->displayPhoneNumber()) {
            $score += 20;
        }
        if ($this->formatPersonalAddress($employee?->address)) {
            $score += 20;
        }

        return min(100, $score);
    }

    protected function formatPersonalAddress($address): ?string
    {
        if (!$address) {
            return null;
        }

        $line1 = trim((string) ($address->address1 ?? ''));
        $line2 = trim((string) ($address->address2 ?? ''));
        $city = trim((string) ($address->city ?? ''));
        $state = trim((string) ($address->state ?? ''));
        $zip = trim((string) ($address->zip ?? ''));

        $cityLine = trim(implode(', ', array_filter([
            $city,
            trim($state . ($zip !== '' ? ' ' . $zip : '')),
        ])));

        $parts = array_filter([$line1, $line2, $cityLine]);

        return $parts === [] ? null : implode(' · ', $parts);
    }

    protected function resolveReportsToName($employee): string
    {
        $assignment = $employee?->currentAssignment;
        if (! $assignment) {
            return '—';
        }

        $fromAssignment = $assignment->reportsToPosition?->title;
        if (filled($fromAssignment)) {
            return $fromAssignment;
        }

        $fromPosition = $assignment->reportsToPositionTitle();
        if (filled($fromPosition)) {
            return $fromPosition;
        }

        return '—';
    }

    protected function formatProfileLastUpdated($user, $employee): ?string
    {
        $latest = collect([
            $user->updated_at,
            $employee?->updated_at,
            $employee?->displayPhoneRecord()?->updated_at,
            $employee?->address?->updated_at,
        ])->filter()->max();

        return $latest?->timezone(config('app.timezone'))->diffForHumans();
    }

    protected function resolveMemberFacility($user, $employee = null): ?Facility
    {
        if ($employee?->currentAssignment?->facility) {
            return $employee->currentAssignment->facility;
        }

        if ($user->facility_id) {
            return Facility::find($user->facility_id);
        }

        return $user->facility;
    }

    protected function countMemberNewsEvents(?Facility $facility): int
    {
        if (!$facility) {
            return News::query()
                ->where('status', true)
                ->where('is_global', true)
                ->count();
        }

        return FacilityDataHelper::getNews($facility)->count();
    }
}
