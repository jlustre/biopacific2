<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('bp_employees', 'badge_num')) {
                $table->string('badge_num', 50)->nullable()->after('employee_num');
            }
            if (!Schema::hasColumn('bp_employees', 'badge_eff_dt')) {
                $table->date('badge_eff_dt')->nullable()->after('badge_num');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            $drops = [];
            foreach (['badge_eff_dt', 'badge_num'] as $column) {
                if (Schema::hasColumn('bp_employees', $column)) {
                    $drops[] = $column;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
