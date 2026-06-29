<?php

namespace Database\Seeders;

use App\Services\DocumentsManagementSeedService;
use Illuminate\Database\Seeder;

/**
 * Idempotent create/update seeder for Documents Management (upload_types).
 *
 * - General document types from database/seeders/data/documents_management_general_types.php
 * - Employee file types (PART A–D) synced from checklist_items
 *
 * Safe to re-run in any environment:
 *   php artisan db:seed --class=DocumentsManagementSeeder
 */
class DocumentsManagementSeeder extends Seeder
{
    public function run(): void
    {
        $result = app(DocumentsManagementSeedService::class)->seedAll();

        $general = $result['general'];
        $checklistSynced = (int) ($result['checklist_synced'] ?? 0);

        $this->command?->info(sprintf(
            'Documents Management: %d general types (%d created, %d updated); %d checklist-linked types synced.',
            (int) ($general['total'] ?? 0),
            (int) ($general['created'] ?? 0),
            (int) ($general['updated'] ?? 0),
            $checklistSynced
        ));
    }
}
