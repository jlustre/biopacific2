<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('bp_emp_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_assignments', 'reports_to_employee_num')) {
                $table->renameColumn('reports_to_employee_num', 'reports_to');
            }
        });
    }

    public function down()
    {
        Schema::table('bp_emp_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('bp_emp_assignments', 'reports_to')) {
                $table->renameColumn('reports_to', 'reports_to_employee_num');
            }
        });
    }
};
