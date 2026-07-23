<?php

namespace App\Services;

use App\Models\ChecklistItem;
use App\Models\Position;
use App\Models\UploadType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChecklistUploadTypeSyncService
{
    public const EMPLOYEE_FILE_SECTIONS = [
        'PART A',
        'PART B',
        'PART C',
        'PART D',
    ];

    /**
     * Sync all PART A–D upload types into checklist_items (upload_types is source of truth).
     */
    public function syncAll(): int
    {
        $synced = 0;

        $query = UploadType::withoutGlobalScopes()
            ->whereIn('checklist_section', self::EMPLOYEE_FILE_SECTIONS);

        if (Schema::hasColumn('upload_types', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        $query->orderBy('name')->orderBy('id')
            ->each(function (UploadType $uploadType) use (&$synced): void {
                if ($this->syncUploadType($uploadType) !== null) {
                    $synced++;
                }
            });

        return $synced;
    }

    /**
     * Upsert the checklist item projection for a catalog upload type.
     */
    public function syncUploadType(UploadType $uploadType): ?ChecklistItem
    {
        $section = (string) ($uploadType->checklist_section ?? '');

        if (! in_array($section, self::EMPLOYEE_FILE_SECTIONS, true)) {
            if ($uploadType->checklist_item_id) {
                UploadType::withoutGlobalScopes()->whereKey($uploadType->id)->update([
                    'checklist_item_id' => null,
                ]);
            }

            return null;
        }

        return DB::transaction(function () use ($uploadType, $section) {
            $item = $uploadType->checklist_item_id
                ? ChecklistItem::query()->find($uploadType->checklist_item_id)
                : null;

            $positionIds = $this->positionIdsForUploadType($uploadType);

            $payload = [
                'name' => trim((string) $uploadType->name),
                'section' => $section,
                'isExpiring' => (bool) $uploadType->requires_expiry,
                'is_required' => true,
                'is_license_or_certification' => (bool) $uploadType->is_license_or_certification,
                'position_ids' => $positionIds,
            ];

            if (Schema::hasColumn('checklist_items', 'doc_type_id')) {
                $payload['doc_type_id'] = $uploadType->doc_type_id ?? null;
            }
            if (Schema::hasColumn('checklist_items', 'order')) {
                $payload['order'] = (int) ($uploadType->sort_order ?? 0);
            }

            if (! $item) {
                $item = ChecklistItem::query()->create($payload);
            } else {
                $item->fill($payload);
                $item->save();
            }

            if ((int) $uploadType->checklist_item_id !== (int) $item->id) {
                UploadType::withoutGlobalScopes()->whereKey($uploadType->id)->update([
                    'checklist_item_id' => $item->id,
                ]);
                $uploadType->checklist_item_id = $item->id;
            }

            return $item->fresh();
        });
    }

    /**
     * Backward-compatible bridge when checklist items are edited from the items UI.
     * Pushes checklist fields onto the linked upload type, then re-syncs projection.
     */
    public function syncChecklistItem(ChecklistItem $item): ?UploadType
    {
        if (! in_array((string) $item->section, self::EMPLOYEE_FILE_SECTIONS, true)) {
            UploadType::withoutGlobalScopes()
                ->where('checklist_item_id', $item->id)
                ->update([
                    'checklist_item_id' => null,
                    'checklist_section' => null,
                ]);

            return null;
        }

        $uploadType = UploadType::withoutGlobalScopes()->firstOrNew([
            'checklist_item_id' => $item->id,
        ]);

        $appliesToAll = $item->position_ids === null;
        $attributes = [
            'name' => trim((string) $item->name),
            'description' => $uploadType->description
                ?: 'Employee file document type item (' . $item->section . ').',
            'requires_expiry' => (bool) $item->isExpiring,
            'is_license_or_certification' => (bool) $item->is_license_or_certification,
            'checklist_section' => $item->section,
        ];

        if (Schema::hasColumn('upload_types', 'doc_type_id')) {
            $attributes['doc_type_id'] = $item->doc_type_id;
        }
        if (Schema::hasColumn('upload_types', 'sort_order')) {
            $attributes['sort_order'] = (int) ($item->order ?? 0);
        }
        if (Schema::hasColumn('upload_types', 'applies_to_all_positions')) {
            $attributes['applies_to_all_positions'] = $appliesToAll;
        }

        $uploadType->fill($attributes);
        $uploadType->save();

        if (Schema::hasTable('position_upload_type_requirements')) {
            if (! $appliesToAll && is_array($item->position_ids)) {
                $this->syncPivotPositions($uploadType, $item->position_ids);
            } elseif ($appliesToAll) {
                $uploadType->positions()->detach();
            }
        }

        $this->syncUploadType($uploadType->fresh());

        return UploadType::withoutGlobalScopes()->find($uploadType->id);
    }

    public function displayName(ChecklistItem $item): string
    {
        return trim((string) $item->name);
    }

    /**
     * @return list<int>|null null = all positions; [] = none
     */
    protected function positionIdsForUploadType(UploadType $uploadType): ?array
    {
        if (Schema::hasColumn('upload_types', 'applies_to_all_positions') && $uploadType->applies_to_all_positions) {
            return null;
        }

        if (! Schema::hasTable('position_upload_type_requirements')) {
            return $uploadType->applies_to_all_positions ?? true ? null : [];
        }

        $ids = $uploadType->positions()
            ->wherePivot('is_required', true)
            ->pluck('positions.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids === [] && ! Schema::hasColumn('upload_types', 'applies_to_all_positions')) {
            // Legacy: no position pivot and no flag => treat as all positions.
            return null;
        }

        return $ids;
    }

    /**
     * @param  list<int>  $positionIds
     */
    protected function syncPivotPositions(UploadType $uploadType, array $positionIds): void
    {
        $validIds = Position::query()
            ->whereIn('id', collect($positionIds)->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $payload = collect($validIds)
            ->unique()
            ->mapWithKeys(fn ($id) => [$id => ['is_required' => true]])
            ->all();

        $uploadType->positions()->sync($payload);
    }
}
