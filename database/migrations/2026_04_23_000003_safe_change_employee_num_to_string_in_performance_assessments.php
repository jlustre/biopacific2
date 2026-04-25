<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            // Change column type to string
            $table->string('employee_num')->change();
            // Add new foreign key to bp_employees.employee_num (string)
            $table->foreign('employee_num')->references('employee_num')->on('bp_employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->dropForeign(['employee_num']);
            // $table->unsignedBigInteger('employee_num')->change(); // Only if you want to revert
        });
    }
};
