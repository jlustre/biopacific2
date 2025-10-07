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
        // Add facility_id to testimonials table if it doesn't exist
        if (!Schema::hasColumn('testimonials', 'facility_id')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->foreignId('facility_id')->after('id')->constrained()->onDelete('cascade');
                $table->index(['facility_id', 'created_at']);
            });
        }

        // Add facility_id to gallery_images table if it doesn't exist
        if (!Schema::hasColumn('gallery_images', 'facility_id')) {
            Schema::table('gallery_images', function (Blueprint $table) {
                $table->foreignId('facility_id')->after('id')->constrained()->onDelete('cascade');
                $table->index(['facility_id', 'category']);
            });
        }

        // Add facility_id to audit_logs table if it doesn't exist
        if (!Schema::hasColumn('audit_logs', 'facility_id')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->foreignId('facility_id')->after('id')->constrained()->onDelete('cascade');
                $table->index(['facility_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            // $table->dropIndex(['facility_id', 'created_at']); // Commented out to prevent migration error
            $table->dropColumn('facility_id');
        });

        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            // $table->dropIndex(['facility_id', 'category']); // Commented out to prevent migration error
            $table->dropColumn('facility_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            // $table->dropIndex(['facility_id', 'created_at']); // Commented out to prevent migration error
            $table->dropColumn('facility_id');
        });
    }
};
