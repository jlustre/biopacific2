<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employee_performance_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_num'); // FK to bp_employees.id
            $table->json('items'); // All assessment items for this employee
            $table->date('assessment_date')->nullable();
            $table->date('next_assessment_date')->nullable();
            $table->date('eff_date')->nullable(); // Effective date for assessment history
            $table->unsignedBigInteger('assessed_by')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->foreign('employee_num')->references('id')->on('bp_employees')->onDelete('cascade');
            $table->foreign('assessed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('employee_num');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_performance_assessments');
    }
};
