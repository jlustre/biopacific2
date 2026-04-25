<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Change column type to string
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->string('employee_num')->change();
        });

        // Add new foreign key to bp_employees.employee_num (string)
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->foreign('employee_num')->references('employee_num')->on('bp_employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->dropForeign(['employee_num']);
            // You may want to revert to unsignedBigInteger if needed, but this will fail if data is not convertible
            // $table->unsignedBigInteger('employee_num')->change();
        });
    }
};
