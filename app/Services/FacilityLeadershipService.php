<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\Facility;
use App\Models\FacilityLeadershipAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FacilityLeadershipService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function activeRoleDefinitionsForFacility(Facility $facility): array
    {
        $disabled = $this->disabledRoleKeysForFacility($facility);

        return array_values(array_filter(
            $this->roleDefinitions(),
            fn (array $role) => ! in_array($role['key'] ?? '', $disabled, true)
        ));
    }

    /**
     * @return list<string>
     */
    public function disabledRoleKeysForFacility(Facility $facility): array
    {
        $settings = $facility->settings ?? [];
        $keys = $settings['leadership_disabled_roles'] ?? [];

        return is_array($keys)
            ? array_values(array_unique(array_filter($keys, fn ($key) => is_string($key) && $key !== '')))
            : [];
    }

    public function authorizeRoleRemoval(User $user): void
    {
        if ($user->hasRole(['admin', 'super-admin', 'facility-dsd'])) {
            return;
        }

        abort(403, 'You do not have permission to remove leadership roles.');
    }

    public function removeStandardRole(Facility $facility, string $roleKey): void
    {
        $roleDef = $this->roleDefinitionMap()[$roleKey] ?? null;
        if (! $roleDef) {
            abort(404);
        }

        $assignment = FacilityLeadershipAssignment::query()
            ->where('facility_id', $facility->id)
            ->where('role_key', $roleKey)
            ->first();

        $employeesByPosition = $this->employeesByPositionTitle($facility);
        if ($this->roleIsInUseAtFacility($roleDef, $assignment, $employeesByPosition, $facility)) {
            abort(422, 'This leadership role is in use at this facility and cannot be removed.');
        }

        $settings = $facility->settings ?? [];
        $disabled = $this->disabledRoleKeysForFacility($facility);
        if (! in_array($roleKey, $disabled, true)) {
            $disabled[] = $roleKey;
        }
        $settings['leadership_disabled_roles'] = $disabled;
        $facility->settings = $settings;

        FacilityLeadershipAssignment::query()
            ->where('facility_id', $facility->id)
            ->where('role_key', $roleKey)
            ->delete();

        $this->syncLegacyFacilityColumn($facility, $roleDef, '');
        $facility->save();
    }

    /**
     * @param  array<string, mixed>  $roleDef
     * @param  array<string, string>  $employeesByPosition
     */
    public function roleIsInUseAtFacility(array $roleDef, ?FacilityLeadershipAssignment $assignment, array $employeesByPosition, ?Facility $facility = null): bool
    {
        if ($this->roleHasEmployeeAtFacility($roleDef, $employeesByPosition)) {
            return true;
        }

        if (trim((string) ($assignment?->name ?? '')) !== '') {
            return true;
        }

        if ($facility !== null && trim($this->legacyFacilityColumnValue($facility, $roleDef)) !== '') {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $roleDef
     * @param  array<string, string>  $employeesByPosition
     */
    protected function roleHasEmployeeAtFacility(array $roleDef, array $employeesByPosition): bool
    {
        foreach ($roleDef['position_titles'] ?? [] as $title) {
            $normalized = Str::lower(trim($title));
            if (($employeesByPosition[$normalized] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function roleDefinitions(): array
    {
        return config('facility-dashboard.leadership_roles', []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function roleDefinitionMap(): array
    {
        $map = [];
        foreach ($this->roleDefinitions() as $role) {
            $key = $role['key'] ?? '';
            if ($key !== '') {
                $map[$key] = $role;
            }
        }

        return $map;
    }

    /**
     * Ensure legacy facility columns are represented in assignments (one-time per facility).
     */
    public function ensureSeededFromFacilityRecord(Facility $facility): void
    {
        if (FacilityLeadershipAssignment::query()->where('facility_id', $facility->id)->exists()) {
            return;
        }

        $employeesByPosition = $this->employeesByPositionTitle($facility);
        $usedNames = [];
        $sort = 0;

        foreach ($this->roleDefinitions() as $role) {
            $key = $role['key'] ?? '';
            if ($key === '' || in_array($key, $this->disabledRoleKeysForFacility($facility), true)) {
                continue;
            }

            $name = $this->resolveNameFromEmployees($role, $employeesByPosition, $usedNames);
            if ($name === '') {
                $name = $this->legacyFacilityColumnValue($facility, $role);
            }

            if ($name === '') {
                $sort++;

                continue;
            }

            FacilityLeadershipAssignment::query()->create([
                'facility_id' => $facility->id,
                'role_key' => $key,
                'role_label' => $role['label'] ?? null,
                'name' => $name,
                'sort_order' => $sort,
                'is_custom' => false,
            ]);
            $usedNames[$this->normalizeName($name)] = true;
            $sort++;
        }
    }

    /**
     * Rows for admin edit form (standard roles + custom roles).
     *
     * @return list<array<string, mixed>>
     */
    public function formRowsForFacility(Facility $facility): array
    {
        $this->ensureSeededFromFacilityRecord($facility);

        $assignments = FacilityLeadershipAssignment::query()
            ->where('facility_id', $facility->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('role_key');

        $employeesByPosition = $this->employeesByPositionTitle($facility);
        $usedNames = [];
        $rows = [];
        $sort = 0;

        foreach ($this->activeRoleDefinitionsForFacility($facility) as $role) {
            $key = $role['key'] ?? '';
            if ($key === '') {
                continue;
            }

            $assignment = $assignments->get($key);
            $savedName = trim((string) ($assignment?->name ?? ''));
            $name = $this->resolveNameFromEmployees($role, $employeesByPosition, $usedNames);

            if ($name === '') {
                $name = $savedName !== '' ? $savedName : $this->legacyFacilityColumnValue($facility, $role);
            }

            if ($name !== '') {
                $usedNames[$this->normalizeName($name)] = true;
            }

            $rows[] = [
                'role_key' => $key,
                'role_label' => $role['label'] ?? $key,
                'abbrev' => $role['abbrev'] ?? strtoupper($key),
                'name' => $name,
                'is_custom' => false,
                'assignment_id' => $assignment?->id,
                'can_delete' => ! $this->roleIsInUseAtFacility($role, $assignment, $employeesByPosition, $facility),
                'sort_order' => $sort++,
            ];
        }

        foreach ($assignments->where('is_custom', true) as $custom) {
            $rows[] = [
                'role_key' => $custom->role_key,
                'role_label' => $custom->role_label ?? 'Custom role',
                'abbrev' => 'Other',
                'name' => $custom->name ?? '',
                'is_custom' => true,
                'assignment_id' => $custom->id,
                'sort_order' => $custom->sort_order,
            ];
        }

        usort($rows, fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function syncFacility(Facility $facility, array $validated): void
    {
        $standardNames = $validated['leadership'] ?? [];
        $customRoles = $validated['custom_roles'] ?? [];
        $deleteCustomIds = $validated['delete_custom_ids'] ?? [];

        $roleMap = $this->roleDefinitionMap();
        $sort = 0;

        foreach ($this->activeRoleDefinitionsForFacility($facility) as $role) {
            $key = $role['key'] ?? '';
            if ($key === '') {
                continue;
            }

            $name = trim((string) ($standardNames[$key] ?? ''));
            $this->upsertAssignment($facility->id, $key, $role['label'] ?? null, $name, $sort, false);
            $this->syncLegacyFacilityColumn($facility, $role, $name);
            $sort++;
        }

        if ($deleteCustomIds !== []) {
            FacilityLeadershipAssignment::query()
                ->where('facility_id', $facility->id)
                ->where('is_custom', true)
                ->whereIn('id', $deleteCustomIds)
                ->delete();
        }

        foreach ($customRoles as $custom) {
            $label = trim((string) ($custom['role_label'] ?? ''));
            $name = trim((string) ($custom['name'] ?? ''));
            $id = !empty($custom['id']) ? (int) $custom['id'] : null;

            if ($label === '' && $name === '') {
                if ($id) {
                    FacilityLeadershipAssignment::query()
                        ->where('facility_id', $facility->id)
                        ->where('is_custom', true)
                        ->where('id', $id)
                        ->delete();
                }

                continue;
            }

            if ($label === '') {
                continue;
            }

            if ($id) {
                $existing = FacilityLeadershipAssignment::query()
                    ->where('facility_id', $facility->id)
                    ->where('is_custom', true)
                    ->find($id);

                if ($existing) {
                    $existing->update([
                        'role_label' => $label,
                        'name' => $name !== '' ? $name : null,
                        'sort_order' => $sort,
                    ]);
                    $sort++;

                    continue;
                }
            }

            $roleKey = 'custom_' . Str::slug($label) . '_' . Str::lower(Str::random(4));
            $this->upsertAssignment($facility->id, $roleKey, $label, $name, $sort, true);
            $sort++;
        }

        $facility->save();
    }

    /**
     * Leadership roster for dashboards (DB first, then legacy columns, then supervisors).
     *
     * @return list<array{rank: int, key: string, office: string, abbrev: string, name: string, vacant: bool}>
     */
    public function rosterForFacility(Facility $facility, ?array $supervisors = null): array
    {
        $this->ensureSeededFromFacilityRecord($facility);

        $assignments = FacilityLeadershipAssignment::query()
            ->where('facility_id', $facility->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('role_key');

        $supervisors ??= $this->employeesByPositionTitle($facility);
        $roleMap = $this->roleDefinitionMap();
        $usedNames = [];
        $leadership = [];
        $rank = 1;
        $ordered = collect();
        $sort = 0;

        foreach ($this->activeRoleDefinitionsForFacility($facility) as $role) {
            $key = $role['key'] ?? '';
            if ($key === '') {
                continue;
            }

            $ordered->push(
                $assignments->get($key) ?? new FacilityLeadershipAssignment([
                    'facility_id' => $facility->id,
                    'role_key' => $key,
                    'role_label' => $role['label'] ?? null,
                    'name' => null,
                    'sort_order' => $sort,
                    'is_custom' => false,
                ])
            );
            $sort++;
        }

        foreach ($assignments->where('is_custom', true) as $custom) {
            $ordered->push($custom);
        }

        foreach ($ordered as $assignment) {
            $roleKey = $assignment->role_key;
            $roleDef = $roleMap[$roleKey] ?? null;
            $office = $assignment->role_label
                ?? ($roleDef['label'] ?? 'Leadership role');
            $abbrev = $roleDef['abbrev'] ?? ($assignment->is_custom ? 'Other' : strtoupper($roleKey));

            $name = trim((string) ($assignment->name ?? ''));
            if ($name === '' && $roleDef) {
                $name = $this->resolveFallbackName($facility, $roleDef, $supervisors, $usedNames);
            }

            $vacant = $name === '';
            $leadership[] = [
                'rank' => $rank,
                'key' => $roleKey,
                'office' => $office,
                'abbrev' => $abbrev,
                'name' => $vacant ? '—' : $name,
                'vacant' => $vacant,
            ];
            $rank++;

            if (!$vacant) {
                $usedNames[$this->normalizeName($name)] = true;
            }
        }

        if ($leadership !== []) {
            return $leadership;
        }

        return $this->rosterFromDefinitionsOnly($facility, $supervisors);
    }

    /**
     * @return Collection<int, FacilityLeadershipAssignment>
     */
    protected function virtualAssignmentsFromDefinitions(Facility $facility): Collection
    {
        return collect($this->roleDefinitions())->map(function (array $role, int $index) use ($facility) {
            $key = $role['key'] ?? '';

            return new FacilityLeadershipAssignment([
                'facility_id' => $facility->id,
                'role_key' => $key,
                'role_label' => $role['label'] ?? null,
                'name' => $this->legacyFacilityColumnValue($facility, $role),
                'sort_order' => $index,
                'is_custom' => false,
            ]);
        });
    }

    /**
     * @param  array<string, string>  $supervisors
     * @param  array<string, true>  $usedNames
     */
    protected function resolveFallbackName(Facility $facility, array $roleDef, array $employeesByPosition, array $usedNames): string
    {
        $fromEmployees = $this->resolveNameFromEmployees($roleDef, $employeesByPosition, $usedNames);
        if ($fromEmployees !== '') {
            return $fromEmployees;
        }

        $column = $roleDef['facility_column'] ?? null;
        if ($column) {
            $fromFacility = trim((string) ($facility->{$column} ?? ''));
            if ($fromFacility !== '' && !isset($usedNames[$this->normalizeName($fromFacility)])) {
                return $fromFacility;
            }
        }

        return '';
    }

    /**
     * @param  array<string, string>  $employeesByPosition
     * @param  array<string, true>  $usedNames
     */
    protected function resolveNameFromEmployees(array $roleDef, array $employeesByPosition, array $usedNames): string
    {
        foreach ($roleDef['position_titles'] ?? [] as $title) {
            $normalized = Str::lower(trim($title));
            $candidate = $employeesByPosition[$normalized] ?? '';
            if ($candidate === '' || isset($usedNames[$this->normalizeName($candidate)])) {
                continue;
            }

            return $candidate;
        }

        return '';
    }

    /**
     * Map normalized position titles to employee display names (current facility assignment).
     *
     * @return array<string, string>
     */
    public function employeesByPositionTitle(Facility $facility): array
    {
        $map = [];

        BPEmployee::query()
            ->with('currentAssignment.position')
            ->whereHas('currentAssignment', function ($query) use ($facility) {
                $query->where('facility_id', $facility->id);
            })
            ->orderedByName()
            ->get()
            ->each(function (BPEmployee $employee) use (&$map) {
                $title = trim((string) ($employee->currentAssignment?->position?->title ?? ''));
                if ($title === '') {
                    return;
                }

                $normalizedTitle = Str::lower($title);
                if (isset($map[$normalizedTitle])) {
                    return;
                }

                $displayName = $this->employeeDisplayName($employee);
                $map[$normalizedTitle] = $displayName !== '' ? $displayName : (string) $employee->employee_num;
            });

        return $map;
    }

    /**
     * @param  array<string, string>  $employeesByPosition
     * @return list<array{rank: int, key: string, office: string, abbrev: string, name: string, vacant: bool}>
     */
    protected function rosterFromDefinitionsOnly(Facility $facility, array $employeesByPosition): array
    {
        $usedNames = [];
        $leadership = [];
        $rank = 1;

        foreach ($this->activeRoleDefinitionsForFacility($facility) as $role) {
            $key = $role['key'] ?? '';
            if ($key === '') {
                continue;
            }

            $name = $this->resolveFallbackName($facility, $role, $employeesByPosition, $usedNames);
            $vacant = $name === '';
            $leadership[] = [
                'rank' => $rank,
                'key' => $key,
                'office' => $role['label'] ?? $key,
                'abbrev' => $role['abbrev'] ?? strtoupper($key),
                'name' => $vacant ? '—' : $name,
                'vacant' => $vacant,
            ];
            $rank++;

            if (!$vacant) {
                $usedNames[$this->normalizeName($name)] = true;
            }
        }

        return $leadership;
    }

    /**
     * Options for leadership name dropdowns (current facility employees).
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public function employeeNameOptionsForFacility(Facility $facility): Collection
    {
        return BPEmployee::query()
            ->whereHas('currentAssignment', function ($query) use ($facility) {
                $query->where('facility_id', $facility->id);
            })
            ->orderedByName()
            ->get()
            ->map(function (BPEmployee $employee) {
                $value = $this->employeeDisplayName($employee);
                if ($value === '') {
                    return null;
                }

                return [
                    'value' => $value,
                    'label' => $this->employeeSelectLabel($employee),
                ];
            })
            ->filter()
            ->unique('value')
            ->values();
    }

    /**
     * Match a stored leadership name to a dropdown option value.
     */
    public function resolveEmployeeOptionValue(string $storedName, Collection $options): ?string
    {
        $storedName = trim($storedName);
        if ($storedName === '') {
            return null;
        }

        foreach ($options as $option) {
            $value = (string) ($option['value'] ?? '');
            if ($value !== '' && $this->leadershipNamesMatch($storedName, $value)) {
                return $value;
            }
        }

        return null;
    }

    public function leadershipNamesMatch(string $a, string $b): bool
    {
        $a = trim($a);
        $b = trim($b);

        if ($a === '' || $b === '') {
            return false;
        }

        if (strcasecmp($a, $b) === 0) {
            return true;
        }

        $partsA = $this->personNameParts($a);
        $partsB = $this->personNameParts($b);

        return $partsA['last'] !== ''
            && $partsA['last'] === $partsB['last']
            && $partsA['first'] !== ''
            && $partsB['first'] !== ''
            && str_starts_with($partsA['first'], substr($partsB['first'], 0, 1))
            && str_starts_with($partsB['first'], substr($partsA['first'], 0, 1));
    }

    /**
     * @return array{first: string, last: string}
     */
    protected function personNameParts(string $name): array
    {
        $normalized = preg_replace('/([a-z])\.(?=[A-Za-z])/i', '$1. ', $name) ?? $name;
        $tokens = preg_split('/[\s,]+/', trim($normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_map(
            fn (string $token) => strtolower(preg_replace('/[^a-z]/i', '', $token) ?? ''),
            $tokens
        ));
        $tokens = array_values(array_filter($tokens));

        if ($tokens === []) {
            return ['first' => '', 'last' => ''];
        }

        if (count($tokens) === 1) {
            return ['first' => $tokens[0], 'last' => ''];
        }

        $last = (string) array_pop($tokens);
        $first = (string) ($tokens[0] ?? '');

        return ['first' => $first, 'last' => $last];
    }

    protected function employeeSelectLabel(BPEmployee $employee): string
    {
        return $employee->formalName();
    }

    protected function employeeDisplayName(BPEmployee $employee): string
    {
        $parts = array_filter([
            trim((string) $employee->first_name),
            trim((string) $employee->middle_name),
            trim((string) $employee->last_name),
        ], fn (string $part) => $part !== '');

        return trim(implode(' ', $parts));
    }

    public function authorizeFacility(User $user, Facility $facility): void
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return;
        }

        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor'])) {
            if ((int) $user->facility_id === (int) $facility->id) {
                return;
            }
        }

        abort(403, 'You do not have access to manage leadership for this facility.');
    }

    /**
     * @return Collection<int, Facility>
     */
    public function facilitiesForUser(User $user): Collection
    {
        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return Facility::query()->orderBy('name')->get(['id', 'name', 'slug']);
        }

        if ($user->facility_id) {
            $facility = Facility::find($user->facility_id);

            return $facility ? collect([$facility]) : collect();
        }

        return collect();
    }

    protected function upsertAssignment(
        int $facilityId,
        string $roleKey,
        ?string $roleLabel,
        string $name,
        int $sortOrder,
        bool $isCustom
    ): void {
        $payload = [
            'role_label' => $roleLabel,
            'name' => $name !== '' ? $name : null,
            'sort_order' => $sortOrder,
            'is_custom' => $isCustom,
        ];

        FacilityLeadershipAssignment::query()->updateOrCreate(
            ['facility_id' => $facilityId, 'role_key' => $roleKey],
            $payload
        );
    }

    /**
     * @param  array<string, mixed>  $role
     */
    protected function syncLegacyFacilityColumn(Facility $facility, array $role, string $name): void
    {
        $column = $role['facility_column'] ?? null;
        if (!$column || !in_array($column, ['administrator', 'don', 'dsd', 'staffer'], true)) {
            return;
        }

        $facility->{$column} = $name !== '' ? $name : null;
    }

    /**
     * @param  array<string, mixed>  $role
     */
    protected function legacyFacilityColumnValue(Facility $facility, array $role): string
    {
        $column = $role['facility_column'] ?? null;
        if (!$column) {
            return '';
        }

        return trim((string) ($facility->{$column} ?? ''));
    }

    protected function normalizeName(string $name): string
    {
        return Str::lower(preg_replace('/\s+/', ' ', trim($name)) ?? '');
    }
}
