<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            // Only add columns that do not already exist
            if (!Schema::hasColumn('pre_employment_applications', 'position_id')) {
                $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete()->after('hired_date');
            }
            if (!Schema::hasColumn('pre_employment_applications', 'hired_at')) {
                $table->timestamp('hired_at')->nullable()->after('position_id');
            }
            if (!Schema::hasColumn('pre_employment_applications', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('hired_at');
            }
            if (!Schema::hasColumn('pre_employment_applications', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            $table->dropColumn(['hired_date', 'position_id', 'hired_at', 'rejected_at', 'rejection_reason']);
        });
    }
};
