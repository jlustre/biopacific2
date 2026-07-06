<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_performance_assessments', 'employee_signature_path')) {
                $table->string('employee_signature_path')->nullable()->after('acknowledge_dt');
            }

            if (! Schema::hasColumn('employee_performance_assessments', 'employee_confirmation_snapshot')) {
                $table->json('employee_confirmation_snapshot')->nullable()->after('employee_signature_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_performance_assessments', function (Blueprint $table) {
            if (Schema::hasColumn('employee_performance_assessments', 'employee_confirmation_snapshot')) {
                $table->dropColumn('employee_confirmation_snapshot');
            }

            if (Schema::hasColumn('employee_performance_assessments', 'employee_signature_path')) {
                $table->dropColumn('employee_signature_path');
            }
        });
    }
};
