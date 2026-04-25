<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update foreign keys and columns in bp_emp_credentials, bp_emp_compensation, etc.
        Schema::table('bp_emp_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_credentials', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        Schema::table('bp_emp_compensation', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_compensation', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        Schema::table('bp_emp_health_screenings', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_health_screenings', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
    }
    public function down(): void
    {
        Schema::table('bp_emp_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_credentials', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        Schema::table('bp_emp_compensation', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_compensation', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        Schema::table('bp_emp_health_screenings', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_health_screenings', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
    }
};
