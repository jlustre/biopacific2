<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->integer('period_year')->nullable()->after('eff_date');
            $table->integer('period_sequence')->nullable()->after('period_year');
        });
    }

    public function down()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->dropColumn(['period_year', 'period_sequence']);
        });
    }
};
