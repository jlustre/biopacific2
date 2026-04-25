<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // bp_emp_addresses
        Schema::table('bp_emp_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_addresses', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        // bp_emp_assignments
        Schema::table('bp_emp_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_assignments', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
            if (Schema::hasColumn('bp_emp_assignments', 'reports_to_employee_num')) {
                $table->renameColumn('reports_to_employee_num', 'reports_to_employee_num');
            }
        });
        // bp_emp_phones
        Schema::table('bp_emp_phones', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_phones', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        // bp_emp_checklists
        Schema::table('bp_emp_checklists', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_checklists', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
    }
    public function down(): void
    {
        Schema::table('bp_emp_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_addresses', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        Schema::table('bp_emp_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_assignments', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
            if (Schema::hasColumn('bp_emp_assignments', 'reports_to_employee_num')) {
                $table->renameColumn('reports_to_employee_num', 'reports_to_employee_num');
            }
        });
        Schema::table('bp_emp_phones', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_phones', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
        Schema::table('bp_emp_checklists', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_checklists', 'employee_num')) {
                $table->renameColumn('employee_num', 'employee_num');
            }
        });
    }
};
