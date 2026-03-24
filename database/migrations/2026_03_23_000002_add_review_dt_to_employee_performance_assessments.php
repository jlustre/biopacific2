<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->date('review_dt')->nullable()->after('assessment_date');
        });
    }

    public function down()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->dropColumn('review_dt');
        });
    }
};
