<?php

namespace Database\Seeders;

use App\Services\DocumentsManagementSeedService;
use Illuminate\Database\Seeder;

/**
 * @deprecated Use DocumentsManagementSeeder. Kept for backward-compatible class references.
 */
class UploadTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $result = app(DocumentsManagementSeedService::class)->seedGeneralDocumentTypes();

        $this->command?->info(sprintf(
            'UploadTypesTableSeeder (general only): %d types (%d created, %d updated).',
            (int) ($result['total'] ?? 0),
            (int) ($result['created'] ?? 0),
            (int) ($result['updated'] ?? 0)
        ));
    }
}
