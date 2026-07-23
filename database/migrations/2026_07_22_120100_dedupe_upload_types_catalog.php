<?php

use App\Services\ChecklistUploadTypeSyncService;
use App\Services\DocumentCatalogDedupeService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(DocumentCatalogDedupeService::class)->run();
        app(ChecklistUploadTypeSyncService::class)->syncAll();
    }

    public function down(): void
    {
        // Irreversible data merge.
    }
};
