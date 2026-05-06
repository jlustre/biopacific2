<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_performance_items', function (Blueprint $table) {
            $table->json('position_ids')->nullable()->after('item');
        });

        DB::table('employee_performance_items')
            ->whereNull('position_ids')
            ->update(['position_ids' => json_encode(['global'])]);
    }

    public function down(): void
    {
        Schema::table('employee_performance_items', function (Blueprint $table) {
            $table->dropColumn('position_ids');
        });
    }
};