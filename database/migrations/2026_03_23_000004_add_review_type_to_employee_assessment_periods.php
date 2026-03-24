<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('employee_assessment_periods', function (Blueprint $table) {
            $table->enum('review_type', ['Q', 'A'])->default('A')->after('date_to');
        });
    }

    public function down()
    {
        Schema::table('employee_assessment_periods', function (Blueprint $table) {
            $table->dropColumn('review_type');
        });
    }
};
