<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('uploads')
            ->whereNull('uploaded_at')
            ->update(['uploaded_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // No-op: cannot reliably distinguish backfilled rows from originally set values.
    }
};
