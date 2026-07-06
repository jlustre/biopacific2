<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('upload_types') || ! Schema::hasTable('position_upload_type_requirements')) {
            return;
        }

        $uploadTypeId = DB::table('upload_types')
            ->where('name', 'Driver License/ID')
            ->value('id');

        if ($uploadTypeId === null) {
            return;
        }

        DB::table('position_upload_type_requirements')
            ->where('upload_type_id', $uploadTypeId)
            ->delete();
    }

    public function down(): void
    {
        // Re-apply position requirements via PositionDocumentRequirementsSeeder after restoring seeder config.
    }
};
