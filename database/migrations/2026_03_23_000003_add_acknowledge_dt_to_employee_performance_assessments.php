<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->date('acknowledge_dt')->nullable()->after('review_dt');
        });
    }

    public function down()
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            $table->dropColumn('acknowledge_dt');
        });
    }
};
