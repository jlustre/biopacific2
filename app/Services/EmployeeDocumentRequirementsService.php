<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\Position;
use App\Models\UploadType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeeDocumentRequirementsService
{
    /**
     * Document types that can be assigned to positions (full catalog).
     */
    public function generalUploadTypesForDepartment(?int $departmentId): Collection
    {
        return UploadType::query()
            ->catalogPositionAssignable()
            ->when(
                $departmentId,
                fn (Builder $query) => $this->applyDepartmentScope($query, $departmentId),
                fn (Builder $query) => $this->applyOrganizationWideScope($query)
            )
            ->orderedForDisplay()
            ->get();
    }

    /**
     * Required upload types for a position (applies_to_all + pivot), scoped to department when provided.
     */
    public function requiredGeneralUploadTypesForPosition(Position $position, ?int $departmentId = null): Collection
    {
        $departmentId ??= $position->department_id ? (int) $position->department_id : null;
        $positionId = (int) $position->id;

        return UploadType::query()
            ->applicableToPosition($positionId)
            ->when(
                $departmentId,
                fn (Builder $query) => $this->applyDepartmentScope($query, $departmentId),
                fn (Builder $query) => $this->applyOrganizationWideScope($query)
            )
            ->orderedForDisplay()
            ->get();
    }

    /**
     * @return list<int>
     */
    public function requiredGeneralUploadTypeIdsForPosition(Position $position, ?int $departmentId = null): array
    {
        return $this->requiredGeneralUploadTypesForPosition($position, $departmentId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isGeneralTypeRequiredForPosition(int $positionId, int $uploadTypeId, ?int $departmentId = null): bool
    {
        $position = Position::query()->find($positionId);
        if (! $position) {
            return false;
        }

        return $this->requiredGeneralUploadTypesForPosition($position, $departmentId)
            ->contains(fn (UploadType $type) => (int) $type->id === $uploadTypeId);
    }

    public function isTypeAvailableForDepartment(UploadType $uploadType, ?int $departmentId): bool
    {
        $departmentIds = $uploadType->department_ids;

        if ($departmentIds === null || $departmentIds === [] || $departmentIds === '') {
            return true;
        }

        if (! $departmentId) {
            return false;
        }

        return in_array((int) $departmentId, array_map('intval', (array) $departmentIds), true);
    }

    public function isUploadTypeApplicableToEmployee(BPEmployee $employee, UploadType $uploadType): bool
    {
        $departmentId = $employee->currentAssignment?->dept_id
            ? (int) $employee->currentAssignment->dept_id
            : null;

        if (! $this->isTypeAvailableForDepartment($uploadType, $departmentId)) {
            return false;
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        if ($uploadType->applies_to_all_positions) {
            return true;
        }

        if (! $positionId) {
            return false;
        }

        return $uploadType->positions()
            ->where('positions.id', $positionId)
            ->wherePivot('is_required', true)
            ->exists();
    }

    /**
     * @deprecated Use isUploadTypeApplicableToEmployee
     */
    public function isChecklistUploadTypeApplicable(BPEmployee $employee, UploadType $uploadType): bool
    {
        return $this->isUploadTypeApplicableToEmployee($employee, $uploadType);
    }

    /**
     * Whether the given user may upload this document type for the employee.
     */
    public function canUploadTypeForEmployee(?User $user, BPEmployee $employee, UploadType $uploadType): bool
    {
        if ($this->isUploadTypeApplicableToEmployee($employee, $uploadType)) {
            return true;
        }

        return $this->userCanBypassPositionRequirements($user, $employee)
            && $this->isTypeAvailableForDepartment(
                $uploadType,
                $employee->currentAssignment?->dept_id ? (int) $employee->currentAssignment->dept_id : null
            );
    }

    /**
     * Position-aware catalog collection for admin/facility employee document UIs.
     */
    public function catalogUploadTypesForEmployee(?BPEmployee $employee): Collection
    {
        if (! $employee) {
            return UploadType::query()->orderedForDisplay()->get();
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;
        $departmentId = $employee->currentAssignment?->dept_id
            ? (int) $employee->currentAssignment->dept_id
            : null;

        if (! $positionId) {
            return UploadType::query()
                ->where('applies_to_all_positions', true)
                ->when($departmentId, fn (Builder $q) => $this->applyDepartmentScope($q, $departmentId))
                ->orderedForDisplay()
                ->get();
        }

        return UploadType::query()
            ->applicableToPosition((int) $positionId)
            ->when($departmentId, fn (Builder $q) => $this->applyDepartmentScope($q, $departmentId))
            ->orderedForDisplay()
            ->get();
    }

    /**
     * Full catalog for facility upload forms when no employee is selected.
     */
    public function fullCatalogUploadTypes(): Collection
    {
        return UploadType::query()->orderBy('name')->get();
    }

    /**
     * Upload type options shown in member portal / employee self-service upload UI.
     *
     * @param  list<array<string, mixed>>  $documentComplianceItems
     * @return list<array{id: int, name: string, requires_expiry: bool, section: string, kind: string}>
     */
    public function uploadOptionsForEmployee(?BPEmployee $employee, array $documentComplianceItems = []): array
    {
        if (! $employee) {
            return [];
        }

        $types = $this->catalogUploadTypesForEmployee($employee);

        $sectionOrder = [
            'PART A' => 1,
            'PART B' => 2,
            'PART C' => 3,
            'PART D' => 4,
            'Required for your position' => 5,
            'General' => 6,
        ];

        return $types
            ->map(fn (UploadType $type) => [
                'id' => (int) $type->id,
                'name' => $type->name,
                'requires_expiry' => (bool) $type->requires_expiry,
                'section' => $type->checklist_section ?: 'General',
                'kind' => 'upload_type',
            ])
            ->unique('id')
            ->sortBy(fn ($item) => [
                $sectionOrder[$item['section'] ?? 'General'] ?? 99,
                $item['name'] ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, requires_expiry: bool, is_license_or_certification: bool}>
     */
    public function requiredGeneralTypesSummaryForPosition(Position $position): array
    {
        return $this->requiredGeneralUploadTypesForPosition($position)
            ->map(fn (UploadType $type) => [
                'id' => (int) $type->id,
                'name' => $type->name,
                'requires_expiry' => (bool) $type->requires_expiry,
                'is_license_or_certification' => (bool) ($type->is_license_or_certification ?? false),
            ])
            ->values()
            ->all();
    }

    public function syncPositionRequirements(Position $position, array $uploadTypeIds): void
    {
        $allowedIds = $this->generalUploadTypesForDepartment(
            $position->department_id ? (int) $position->department_id : null
        )->pluck('id')->map(fn ($id) => (int) $id);

        $selectedIds = collect($uploadTypeIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->intersect($allowedIds)
            ->values();

        $allPositionsIds = UploadType::query()
            ->whereIn('id', $allowedIds)
            ->where('applies_to_all_positions', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        // Unchecking a previously "all positions" type clears the global flag.
        $deselectedAll = $allPositionsIds->diff($selectedIds);
        if ($deselectedAll->isNotEmpty()) {
            UploadType::query()
                ->whereIn('id', $deselectedAll->all())
                ->update(['applies_to_all_positions' => false]);
        }

        // Keep globally-required types off the per-position pivot.
        $stillAll = $allPositionsIds->intersect($selectedIds);
        $pivotIds = $selectedIds->diff($stillAll);

        $position->requiredUploadTypes()->sync(
            $pivotIds->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])->all()
        );

        $sync = app(ChecklistUploadTypeSyncService::class);
        UploadType::query()
            ->whereIn('id', $deselectedAll->merge($pivotIds)->merge($stillAll)->unique()->all())
            ->employeeFileSections()
            ->each(fn (UploadType $type) => $sync->syncUploadType($type));
    }

    /**
     * Mark a document type as required for all positions.
     */
    public function setAppliesToAllPositions(UploadType $uploadType, bool $applies): void
    {
        $uploadType->applies_to_all_positions = $applies;
        $uploadType->save();

        if ($applies) {
            $uploadType->positions()->detach();
        }

        if ($uploadType->isEmployeeFileChecklistType()) {
            app(ChecklistUploadTypeSyncService::class)->syncUploadType($uploadType->fresh());
        }
    }

    /**
     * @param  list<int>  $positionIds
     * @param  list<int>  $uploadTypeIds
     */
    public function addRequirementsToPositions(array $positionIds, array $uploadTypeIds): int
    {
        $pairs = 0;

        foreach (Position::query()->whereIn('id', $positionIds)->get() as $position) {
            $existingIds = $this->requiredGeneralUploadTypeIdsForPosition($position);
            $merged = array_values(array_unique(array_merge($existingIds, $uploadTypeIds)));
            $this->syncPositionRequirements($position, $merged);
            $pairs += count($uploadTypeIds);
        }

        return $pairs;
    }

    /**
     * @param  list<int>  $positionIds
     * @param  list<int>  $uploadTypeIds
     */
    public function removeRequirementsFromPositions(array $positionIds, array $uploadTypeIds): int
    {
        $removed = 0;
        $removeIds = collect($uploadTypeIds)->map(fn ($id) => (int) $id)->unique()->values();

        foreach (Position::query()->whereIn('id', $positionIds)->get() as $position) {
            $remaining = collect($this->requiredGeneralUploadTypeIdsForPosition($position))
                ->diff($removeIds)
                ->values()
                ->all();

            $this->syncPositionRequirements($position, $remaining);
            $removed += $removeIds->count();
        }

        // Also clear applies_to_all when removing those types from "all".
        UploadType::query()
            ->whereIn('id', $removeIds->all())
            ->where('applies_to_all_positions', true)
            ->each(function (UploadType $type): void {
                $this->setAppliesToAllPositions($type, false);
            });

        return $removed;
    }

    /**
     * @param  list<int>  $positionIds
     * @param  list<int>  $uploadTypeIds
     */
    public function replaceRequirementsForPositions(array $positionIds, array $uploadTypeIds): int
    {
        $count = 0;

        foreach (Position::query()->whereIn('id', $positionIds)->get() as $position) {
            $this->syncPositionRequirements($position, $uploadTypeIds);
            $count++;
        }

        return $count;
    }

    public function copyRequirementsToPositions(Position $source, iterable $targetPositions): int
    {
        $sourceIds = collect($this->requiredGeneralUploadTypeIdsForPosition($source));

        $count = 0;

        foreach ($targetPositions as $target) {
            if ((int) $target->id === (int) $source->id) {
                continue;
            }

            $this->syncPositionRequirements($target, $sourceIds->all());
            $count++;
        }

        return $count;
    }

    protected function userCanBypassPositionRequirements(?User $user, BPEmployee $employee): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return true;
        }

        $facilityId = $employee->currentAssignment?->facility_id;
        if ($facilityId && method_exists($user, 'canManageFacility')) {
            return $user->canManageFacility((int) $facilityId);
        }

        return false;
    }

    protected function applyDepartmentScope(Builder $query, int $departmentId): Builder
    {
        return $query->where(function (Builder $scope) use ($departmentId) {
            $scope->whereNull('department_ids')
                ->orWhereJsonLength('department_ids', 0)
                ->orWhereJsonContains('department_ids', $departmentId);
        });
    }

    protected function applyOrganizationWideScope(Builder $query): Builder
    {
        return $query->where(function (Builder $scope) {
            $scope->whereNull('department_ids')
                ->orWhereJsonLength('department_ids', 0);
        });
    }
}
