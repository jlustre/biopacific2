<?php

namespace App\Http\Controllers\Concerns;

use App\Helpers\FacilityDataHelper;
use App\Models\Facility;
use App\Models\News;

trait ProvidesMemberPortalContext
{
    protected function memberPortalContext($user): array
    {
        $employee = method_exists($user, 'resolvedBpEmployee')
            ? $user->resolvedBpEmployee(['currentAssignment.position', 'currentAssignment.facility', 'currentAssignment.department'])
            : null;

        $facility = $this->resolveMemberFacility($user, $employee);
        $displayName = trim(($employee?->first_name ?? '') . ' ' . ($employee?->last_name ?? '')) ?: ($user->name ?? 'Employee');
        $nameParts = preg_split('/\s+/', trim($displayName), 2);
        $firstName = $nameParts[0] ?? $displayName;
        $lastName = $nameParts[1] ?? '';
        $firstNameOnly = explode(' ', trim($displayName))[0] ?? $displayName;

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
            'facilityName' => $employee?->currentAssignment?->facility?->name ?? ($facility?->name ?? '—'),
            'employeeId' => $employee?->employee_num ?? '—',
            'hireDate' => $employee?->original_hire_dt
                ? \Carbon\Carbon::parse($employee->original_hire_dt)->format('M j, Y')
                : '—',
            'initials' => $user->initials ?? strtoupper(substr($firstNameOnly, 0, 1)),
            'profileComplete' => 88,
            'newsEventsCount' => $this->countMemberNewsEvents($facility),
        ];
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
