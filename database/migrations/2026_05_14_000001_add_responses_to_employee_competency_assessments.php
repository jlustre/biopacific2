<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_competency_assessments', function (Blueprint $table) {
            $table->json('responses')->nullable()->after('comments');
        });
    }

    public function down(): void
    {
        Schema::table('employee_competency_assessments', function (Blueprint $table) {
            $table->dropColumn('responses');
        });
    }
};
