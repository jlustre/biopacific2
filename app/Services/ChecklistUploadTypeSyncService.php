<?php

namespace App\Services;

use App\Models\ChecklistItem;
use App\Models\UploadType;

class ChecklistUploadTypeSyncService
{
    public const EMPLOYEE_FILE_SECTIONS = [
        'PART A',
        'PART B',
        'PART C',
        'PART D',
    ];

    public function syncAll(): int
    {
        $synced = 0;

        ChecklistItem::query()
            ->whereIn('section', self::EMPLOYEE_FILE_SECTIONS)
            ->orderBy('order')
            ->orderBy('id')
            ->each(function (ChecklistItem $item) use (&$synced): void {
                if ($this->syncChecklistItem($item) !== null) {
                    $synced++;
                }
            });

        return $synced;
    }

    public function syncChecklistItem(ChecklistItem $item): ?UploadType
    {
        if (! in_array((string) $item->section, self::EMPLOYEE_FILE_SECTIONS, true)) {
            UploadType::query()
                ->where('checklist_item_id', $item->id)
                ->update([
                    'checklist_item_id' => null,
                    'checklist_section' => null,
                ]);

            return null;
        }

        $uploadType = UploadType::query()->firstOrNew([
            'checklist_item_id' => $item->id,
        ]);

        $uploadType->fill([
            'name' => $this->displayName($item),
            'description' => 'Employee file document type item (' . $item->section . ').',
            'requires_expiry' => (bool) $item->isExpiring,
            'is_license_or_certification' => (bool) $item->is_license_or_certification,
            'checklist_section' => $item->section,
        ]);

        $uploadType->save();

        return $uploadType;
    }

    public function displayName(ChecklistItem $item): string
    {
        return trim((string) $item->name);
    }
}
