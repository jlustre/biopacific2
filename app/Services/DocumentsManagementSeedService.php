<?php

namespace App\Services;

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

            $existing = UploadType::query()
                ->where('name', $name)
                ->whereNull('checklist_item_id')
                ->first();

            if ($existing) {
                $existing->fill($attributes);
                if ($existing->isDirty()) {
                    $existing->save();
                    $updated++;
                }

                continue;
            }

            $conflict = UploadType::query()
                ->where('name', $name)
                ->whereNotNull('checklist_item_id')
                ->exists();

            if ($conflict) {
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

    public function syncChecklistDocumentTypes(): int
    {
        return app(ChecklistUploadTypeSyncService::class)->syncAll();
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
