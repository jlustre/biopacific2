<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class EmployeePortalRoleService
{
    public function __construct(
        protected PositionPortalRoleMappingService $mappingService,
    ) {}

    public function portalRoleForEmployee(BPEmployee $employee): ?string
    {
        $employee->loadMissing('currentAssignment.position');

        $positionId = $employee->currentAssignment?->position_id;
        $roleFromMapping = $this->mappingService->portalRoleForPositionId($positionId);

        if ($roleFromMapping) {
            return $roleFromMapping;
        }

        $title = trim((string) ($employee->currentAssignment?->position?->title ?? ''));

        if ($title === '') {
            return null;
        }

        $normalizedTitle = Str::lower($title);
        $map = $this->mappingService->legacyTitleRoleMap();

        return $map[$normalizedTitle] ?? null;
    }

    public function assignRegistrationRole(User $user, BPEmployee $employee): void
    {
        $roleName = $this->portalRoleForEmployee($employee);

        if ($roleName && Role::query()->where('name', $roleName)->exists()) {
            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            return;
        }

        if (! $user->hasRole('regular-user') && Role::query()->where('name', 'regular-user')->exists()) {
            $user->assignRole('regular-user');
        }
    }
}
