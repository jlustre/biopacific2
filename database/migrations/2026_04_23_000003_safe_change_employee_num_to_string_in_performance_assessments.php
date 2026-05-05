<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Drop existing foreign key if it exists to avoid duplicate constraint error
        try {
            DB::statement('ALTER TABLE employee_performance_assessments DROP FOREIGN KEY employee_performance_assessments_employee_num_foreign');
        } catch (\Exception $e) {
            // Ignore if the foreign key does not exist
        }
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->string('employee_num')->change();
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
