<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('upload_types')) {
            return;
        }

        // PART E is orientation/skills, not a document catalog entry.
        DB::table('upload_types')
            ->where('checklist_section', 'PART E')
            ->update([
                'checklist_item_id' => null,
                'checklist_section' => null,
            ]);
    }

    public function down(): void
    {
        // Irreversible cleanup.
    }
};
