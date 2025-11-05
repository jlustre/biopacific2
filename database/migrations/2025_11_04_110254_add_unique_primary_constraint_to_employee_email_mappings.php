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
        Schema::table('employee_email_mappings', function (Blueprint $table) {
            // Add unique constraint to ensure only one primary contact per facility/category
            // This is a partial unique index that only applies when is_primary = 1
            $table->unique(['facility_id', 'category', 'is_primary'], 'unique_primary_per_facility_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_email_mappings', function (Blueprint $table) {
            // Remove the unique constraint
            $table->dropIndex('unique_primary_per_facility_category');
        });
    }
};
