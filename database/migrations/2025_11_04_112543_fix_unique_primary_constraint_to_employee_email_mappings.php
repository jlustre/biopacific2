<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, try to drop the existing problematic unique constraint
        try {
            Schema::table('employee_email_mappings', function (Blueprint $table) {
                $table->dropIndex('unique_primary_per_facility_category');
            });
        } catch (\Exception $e) {
            // Index might not exist or have different name, continue
        }

        // Also try alternative index names that Laravel might have created
        try {
            DB::statement('DROP INDEX IF EXISTS unique_primary_per_facility_category ON employee_email_mappings');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
        
        try {
            DB::statement('DROP INDEX IF EXISTS employee_email_mappings_facility_id_category_is_primary_unique ON employee_email_mappings');
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }

        // Create a simple application-level constraint for now
        // We'll enforce uniqueness in the application code instead of database
        // This avoids MySQL partial index limitations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback since we're using application-level constraints
    }
};
