<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ln_competency_skill_responses', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
        Schema::table('ln_competency_skill_responses', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->string('employee_num')->index()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('ln_competency_skill_responses', function (Blueprint $table) {
            $table->dropIndex(['employee_num']);
        });
        Schema::table('ln_competency_skill_responses', function (Blueprint $table) {
            $table->dropColumn('employee_num');
            $table->unsignedBigInteger('user_id')->index()->after('id');
        });
    }
};
