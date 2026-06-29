<?php

namespace App\Support;

use App\Models\Position;
use App\Models\PositionPortalRoleMapping;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PositionPortalRoleMappingService
{
    /**
     * Leadership roster keys mapped to portal roles for default seeding.
     *
     * @var array<string, string>
     */
    private const LEADERSHIP_KEY_ROLES = [
        'administrator' => 'facility-admin',
        'dsd' => 'facility-dsd',
        'staffer' => 'facility-dsd',
        'don' => 'don',
        'ssd' => 'ssd',
        'activities' => 'activities-director',
    ];

    /**
     * @return list<string>
     */
    public function assignableRoleNames(): array
    {
        return config('member-portal.position_registration_assignable_roles', [
            'facility-admin',
            'facility-dsd',
            'don',
            'ssd',
            'activities-director',
            'facility-editor',
            'regular-user',
        ]);
    }

    /**
     * @return Collection<int, Role>
     */
    public function assignableRoles(): Collection
    {
        return Role::query()
            ->whereIn('name', $this->assignableRoleNames())
            ->orderBy('name')
            ->get();
    }

    public function roleLabel(string $roleName): string
    {
        return User::roleDisplayLabel($roleName);
    }

    public function portalRoleForPositionId(?int $positionId): ?string
    {
        if (! $positionId) {
            return null;
        }

        return PositionPortalRoleMapping::query()
            ->active()
            ->where('position_id', $positionId)
            ->value('role_name');
    }

    /**
     * @return array<string, string> lowercase position title => role name
     */
    public function legacyTitleRoleMap(): array
    {
        $map = [];

        foreach (config('facility-dashboard.leadership_roles', []) as $roleDefinition) {
            $leadershipKey = $roleDefinition['key'] ?? '';
            $portalRole = self::LEADERSHIP_KEY_ROLES[$leadershipKey] ?? null;

            if (! $portalRole) {
                continue;
            }

            foreach ($roleDefinition['position_titles'] ?? [] as $positionTitle) {
                $normalized = Str::lower(trim((string) $positionTitle));

                if ($normalized !== '') {
                    $map[$normalized] = $portalRole;
                }
            }
        }

        foreach (config('member-portal.position_registration_roles', []) as $positionTitle => $portalRole) {
            $normalized = Str::lower(trim((string) $positionTitle));
            $role = trim((string) $portalRole);

            if ($normalized !== '' && $role !== '') {
                $map[$normalized] = $role;
            }
        }

        return $map;
    }

    public function syncDefaultMappings(): int
    {
        $count = 0;

        foreach ($this->legacyTitleRoleMap() as $normalizedTitle => $roleName) {
            if (! Role::query()->where('name', $roleName)->exists()) {
                continue;
            }

            $positions = Position::query()
                ->whereRaw('LOWER(title) = ?', [$normalizedTitle])
                ->get();

            foreach ($positions as $position) {
                PositionPortalRoleMapping::query()->updateOrCreate(
                    ['position_id' => $position->id],
                    ['role_name' => $roleName, 'is_active' => true]
                );
                $count++;
            }
        }

        return $count;
    }
}
