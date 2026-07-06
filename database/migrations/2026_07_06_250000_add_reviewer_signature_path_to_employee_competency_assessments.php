<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_competency_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_competency_assessments', 'reviewer_signature_path')) {
                $table->string('reviewer_signature_path')->nullable()->after('reviewer_signed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_competency_assessments', function (Blueprint $table) {
            if (Schema::hasColumn('employee_competency_assessments', 'reviewer_signature_path')) {
                $table->dropColumn('reviewer_signature_path');
            }
        });
    }
};
