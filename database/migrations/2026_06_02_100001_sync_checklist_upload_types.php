<?php

use App\Services\DocumentsManagementSeedService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(DocumentsManagementSeedService::class)->syncChecklistDocumentTypes();
    }

    public function down(): void
    {
        // Keep synced upload types; only the schema migration removes linkage columns.
    }
};
