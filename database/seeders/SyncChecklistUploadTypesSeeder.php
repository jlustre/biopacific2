<?php

namespace Database\Seeders;

use App\Services\DocumentsManagementSeedService;
use Illuminate\Database\Seeder;

/**
 * @deprecated Use DocumentsManagementSeeder. Kept for backward-compatible class references.
 */
class SyncChecklistUploadTypesSeeder extends Seeder
{
    public function run(): void
    {
        $synced = app(DocumentsManagementSeedService::class)->syncChecklistDocumentTypes();

        $this->command?->info("Synced {$synced} employee file checklist document types.");
    }
}
