<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_performance_assessments', 'finalized')) {
                $table->boolean('finalized')->default(false)->after('comments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            if (Schema::hasColumn('employee_performance_assessments', 'finalized')) {
                $table->dropColumn('finalized');
            }
        });
    }
};