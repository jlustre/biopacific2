<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ln_competency_skill_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_assessment_period_id')->nullable()->after('employee_num');
        });
    }

    public function down(): void
    {
        Schema::table('ln_competency_skill_responses', function (Blueprint $table) {
            $table->dropColumn('employee_assessment_period_id');
        });
    }
};
