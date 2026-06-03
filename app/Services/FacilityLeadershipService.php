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

        $sort = 0;
        foreach ($this->roleDefinitions() as $role) {
            $column = $role['facility_column'] ?? null;
            if (!$column) {
                $sort++;

                continue;
            }

            $name = trim((string) ($facility->{$column} ?? ''));
            if ($name === '') {
                $sort++;

                continue;
            }

            FacilityLeadershipAssignment::query()->create([
                'facility_id' => $facility->id,
                'role_key' => $role['key'],
                'role_label' => $role['label'] ?? null,
                'name' => $name,
                'sort_order' => $sort,
                'is_custom' => false,
            ]);
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

        $rows = [];
        $sort = 0;

        foreach ($this->roleDefinitions() as $role) {
            $key = $role['key'] ?? '';
            if ($key === '') {
                continue;
            }

            $assignment = $assignments->get($key);
            $rows[] = [
                'role_key' => $key,
                'role_label' => $role['label'] ?? $key,
                'abbrev' => $role['abbrev'] ?? strtoupper($key),
                'name' => $assignment?->name ?? $this->legacyFacilityColumnValue($facility, $role),
                'is_custom' => false,
                'assignment_id' => $assignment?->id,
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

        foreach ($this->roleDefinitions() as $role) {
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

        $supervisors ??= $this->supervisorsByPositionTitle($facility);
        $roleMap = $this->roleDefinitionMap();
        $usedNames = [];
        $leadership = [];
        $rank = 1;
        $ordered = collect();
        $sort = 0;

        foreach ($this->roleDefinitions() as $role) {
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
    protected function resolveFallbackName(Facility $facility, array $roleDef, array $supervisors, array $usedNames): string
    {
        $column = $roleDef['facility_column'] ?? null;
        if ($column) {
            $fromFacility = trim((string) ($facility->{$column} ?? ''));
            if ($fromFacility !== '' && !isset($usedNames[$this->normalizeName($fromFacility)])) {
                return $fromFacility;
            }
        }

        foreach ($roleDef['position_titles'] ?? [] as $title) {
            $normalized = Str::lower(trim($title));
            $candidate = $supervisors[$normalized] ?? '';
            if ($candidate === '' || isset($usedNames[$this->normalizeName($candidate)])) {
                continue;
            }

            return $candidate;
        }

        return '';
    }

    /**
     * @param  array<string, string>  $supervisors
     * @return list<array{rank: int, key: string, office: string, abbrev: string, name: string, vacant: bool}>
     */
    protected function rosterFromDefinitionsOnly(Facility $facility, array $supervisors): array
    {
        $usedNames = [];
        $leadership = [];
        $rank = 1;

        foreach ($this->roleDefinitions() as $role) {
            $key = $role['key'] ?? '';
            if ($key === '') {
                continue;
            }

            $name = $this->resolveFallbackName($facility, $role, $supervisors, $usedNames);
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
     * @return array<string, string>
     */
    public function supervisorsByPositionTitle(Facility $facility): array
    {
        $map = [];

        BPEmployee::query()
            ->with('currentAssignment.position')
            ->whereHas('currentAssignment', function ($query) use ($facility) {
                $query->where('facility_id', $facility->id);
            })
            ->whereHas('currentAssignment.position', function ($query) {
                $query->where('supervisor_role', true);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
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

                $displayName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                $map[$normalizedTitle] = $displayName !== '' ? $displayName : (string) $employee->employee_num;
            });

        return $map;
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
