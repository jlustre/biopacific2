<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // New table for assessment periods (date range)
        Schema::create('employee_assessment_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('period_year');
            $table->integer('period_sequence')->default(0);
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            // No employee_num, periods are now global
            $table->index(['period_year', 'period_sequence'], 'ea_periods_year_seq_idx');
        });

        // Redesign employee_performance_assessments to reference assessment_period_id
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('assessment_period_id')->nullable()->after('id');
            $table->dropColumn(['eff_date', 'period_year', 'period_sequence', 'next_assessment_date']);
        });
    }

    public function down()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->dropColumn('assessment_period_id');
            // Not restoring dropped columns for simplicity
        });
        Schema::dropIfExists('employee_assessment_periods');
    }
};
