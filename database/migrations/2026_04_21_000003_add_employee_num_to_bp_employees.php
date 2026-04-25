<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            // Add employee_num column
            if (!Schema::hasColumn('bp_employees', 'employee_num')) {
                $table->string('employee_num')->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            if (Schema::hasColumn('bp_employees', 'employee_num')) {
                $table->dropUnique(['employee_num']);
                $table->dropColumn('employee_num');
            }
        });
    }
};
