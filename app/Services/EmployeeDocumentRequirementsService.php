<?php

namespace App\Services;

use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use App\Models\Position;
use App\Models\UploadType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmployeeDocumentRequirementsService
{
    /**
     * General document types that can be assigned to positions (not employee-file checklist sync types).
     */
    public function generalUploadTypesForDepartment(?int $departmentId): Collection
    {
        return UploadType::query()
            ->generalPositionAssignable()
            ->when(
                $departmentId,
                fn (Builder $query) => $this->applyDepartmentScope($query, $departmentId),
                fn (Builder $query) => $this->applyOrganizationWideScope($query)
            )
            ->orderBy('name')
            ->get();
    }

    /**
     * Required general upload types for a position (pivot), scoped to department when provided.
     */
    public function requiredGeneralUploadTypesForPosition(Position $position, ?int $departmentId = null): Collection
    {
        $departmentId ??= $position->department_id ? (int) $position->department_id : null;

        return $position->requiredUploadTypes()
            ->wherePivot('is_required', true)
            ->generalPositionAssignable()
            ->when(
                $departmentId,
                fn (Builder $query) => $this->applyDepartmentScope($query, $departmentId),
                fn (Builder $query) => $this->applyOrganizationWideScope($query)
            )
            ->orderBy('name')
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
        if (!$position) {
            return false;
        }

        return $this->requiredGeneralUploadTypesForPosition($position, $departmentId)
            ->contains(fn (UploadType $type) => (int) $type->id === $uploadTypeId);
    }

    public function isChecklistUploadTypeApplicable(BPEmployee $employee, UploadType $uploadType): bool
    {
        $checklistItem = $uploadType->checklistItem;
        if (!$checklistItem) {
            return false;
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        return ChecklistItem::query()
            ->whereKey($checklistItem->id)
            ->applicableToPosition($positionId)
            ->whereIn('section', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)
            ->exists();
    }

    public function isTypeAvailableForDepartment(UploadType $uploadType, ?int $departmentId): bool
    {
        $departmentIds = $uploadType->department_ids;

        if ($departmentIds === null || $departmentIds === [] || $departmentIds === '') {
            return true;
        }

        if (!$departmentId) {
            return false;
        }

        return in_array((int) $departmentId, array_map('intval', (array) $departmentIds), true);
    }

    /**
     * Whether the given user may upload this document type for the employee.
     * Admins may upload any department-scoped general type; members only position-required types.
     */
    public function canUploadTypeForEmployee(?User $user, BPEmployee $employee, UploadType $uploadType): bool
    {
        $uploadType->loadMissing('checklistItem');

        if ($uploadType->checklist_item_id) {
            return $this->isChecklistUploadTypeApplicable($employee, $uploadType);
        }

        $departmentId = $employee->currentAssignment?->dept_id
            ? (int) $employee->currentAssignment->dept_id
            : null;

        if (!$this->isTypeAvailableForDepartment($uploadType, $departmentId)) {
            return false;
        }

        if ($this->userCanBypassPositionRequirements($user, $employee)) {
            return true;
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        if (!$positionId) {
            return false;
        }

        return $this->isGeneralTypeRequiredForPosition((int) $positionId, (int) $uploadType->id, $departmentId);
    }

    /**
     * Upload type options shown in member portal / employee self-service upload UI.
     *
     * @param  list<array<string, mixed>>  $documentComplianceItems
     * @return list<array{id: int, name: string, requires_expiry: bool, section: string, kind: string}>
     */
    public function uploadOptionsForEmployee(?BPEmployee $employee, array $documentComplianceItems = []): array
    {
        if (!$employee) {
            return [];
        }

        $positionId = $employee->currentAssignment?->position_id
            ?? $employee->currentAssignment?->position?->id;

        $options = collect($documentComplianceItems)
            ->map(fn ($item) => [
                'id' => (int) ($item['upload_type_id'] ?? 0),
                'name' => $item['name'] ?? 'Document',
                'requires_expiry' => (bool) ($item['requires_expiry'] ?? false),
                'section' => 'Required for your position',
                'kind' => 'upload_type',
            ])
            ->filter(fn ($item) => $item['id'] > 0);

        $checklistTypes = UploadType::query()
            ->whereNotNull('checklist_item_id')
            ->employeeFileSections()
            ->when($positionId, function (Builder $query) use ($positionId): void {
                $query->whereHas('checklistItem', function (Builder $checklistQuery) use ($positionId): void {
                    $checklistQuery->applicableToPosition($positionId);
                });
            }, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->orderedForDisplay()
            ->get(['id', 'name', 'requires_expiry', 'checklist_section']);

        foreach ($checklistTypes as $type) {
            $options->push([
                'id' => (int) $type->id,
                'name' => $type->name,
                'requires_expiry' => (bool) $type->requires_expiry,
                'section' => $type->checklist_section ?? 'Employee file',
                'kind' => 'upload_type',
            ]);
        }

        $sectionOrder = [
            'Required for your position' => 1,
            'PART A' => 2,
            'PART B' => 3,
            'PART C' => 4,
            'PART D' => 5,
        ];

        return $options
            ->unique('id')
            ->sortBy(fn ($item) => [
                $sectionOrder[$item['section'] ?? 'Required for your position'] ?? 99,
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

        $payload = collect($uploadTypeIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->intersect($allowedIds)
            ->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])
            ->all();

        $position->requiredUploadTypes()->sync($payload);
    }

    public function copyRequirementsToPositions(Position $source, iterable $targetPositions): int
    {
        $sourceIds = $source->requiredUploadTypes()
            ->wherePivot('is_required', true)
            ->pluck('upload_types.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $count = 0;

        foreach ($targetPositions as $target) {
            if ((int) $target->id === (int) $source->id) {
                continue;
            }

            $allowedIds = $this->generalUploadTypesForDepartment(
                $target->department_id ? (int) $target->department_id : null
            )->pluck('id')->map(fn ($id) => (int) $id);

            $target->requiredUploadTypes()->sync(
                $sourceIds
                    ->intersect($allowedIds)
                    ->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])
                    ->all()
            );

            $count++;
        }

        return $count;
    }

    protected function userCanBypassPositionRequirements(?User $user, BPEmployee $employee): bool
    {
        if (!$user) {
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
