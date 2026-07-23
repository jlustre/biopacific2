<?php

namespace App\Services;

use App\Models\ChecklistItem;
use App\Models\UploadType;

class DocumentsManagementSeedService
{
    public function seedGeneralDocumentTypes(): array
    {
        $definitions = require database_path('seeders/data/documents_management_general_types.php');
        $created = 0;
        $updated = 0;

        foreach ($definitions as $definition) {
            $name = trim((string) ($definition['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $attributes = [
                'description' => (string) ($definition['description'] ?? ''),
                'requires_expiry' => (bool) ($definition['requires_expiry'] ?? false),
                'is_license_or_certification' => (bool) ($definition['is_license_or_certification'] ?? false),
                'checklist_item_id' => null,
                'checklist_section' => null,
            ];

            // Prefer an existing catalog row of any kind (including PART A–D) so seeders never recreate duplicates.
            $existing = UploadType::withoutGlobalScopes()->where('name', $name)->first();

            if ($existing) {
                if ($existing->checklist_item_id) {
                    continue;
                }

                $existing->fill($attributes);
                if ($existing->isDirty()) {
                    $existing->save();
                    $updated++;
                }

                continue;
            }

            UploadType::query()->create(array_merge(['name' => $name], $attributes));
            $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($definitions),
        ];
    }

    /**
     * Bootstrap missing PART A–D upload_types from checklist_items (used by seeders),
     * then project catalog names back onto checklist_items.
     */
    public function syncChecklistDocumentTypes(): int
    {
        $sync = app(ChecklistUploadTypeSyncService::class);
        $bootstrapped = 0;

        ChecklistItem::query()
            ->whereIn('section', ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS)
            ->orderBy('order')
            ->orderBy('id')
            ->each(function (ChecklistItem $item) use ($sync, &$bootstrapped): void {
                $exists = UploadType::withoutGlobalScopes()
                    ->where('checklist_item_id', $item->id)
                    ->exists();

                $sync->syncChecklistItem($item);

                if (! $exists) {
                    $bootstrapped++;
                }
            });

        $sync->syncAll();

        return $bootstrapped;
    }

    /**
     * @return array{general: array{created: int, updated: int, total: int}, checklist_synced: int}
     */
    public function seedAll(): array
    {
        return [
            'general' => $this->seedGeneralDocumentTypes(),
            'checklist_synced' => $this->syncChecklistDocumentTypes(),
        ];
    }
}
