<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            if (Schema::hasColumn('uploads', 'employee_num') && !Schema::hasColumn('uploads', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
            // Update foreign key if exists
            if (Schema::hasColumn('uploads', 'employee_num')) {
                try {
                    $table->dropForeign(['employee_num']);
                } catch (\Exception $e) {}
                $table->foreign('employee_num')->references('employee_num')->on('bp_employees')->onDelete('set null');
            }
        });
    }
    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            if (Schema::hasColumn('uploads', 'employee_num')) {
                $table->dropForeign(['employee_num']);
                $table->renameColumn('employee_num', 'employee_num');
                $table->foreign('employee_num')->references('employee_num')->on('bp_employees')->onDelete('set null');
            }
        });
    }
};
