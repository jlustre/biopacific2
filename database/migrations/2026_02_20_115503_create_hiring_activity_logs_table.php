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
        Schema::create('hiring_activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->unsignedBigInteger('pre_employment_application_id')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable(); // User ID of hiring manager
            $table->unsignedBigInteger('recipient_id')->nullable(); // User ID of applicant
            
            // Activity details
            $table->string('activity_type'); // 'submitted', 'returned', 'completed', 'reviewed', etc.
            $table->string('description'); // Brief description of the action
            $table->longText('notes')->nullable(); // Detailed notes/comments
            
            // Status tracking
            $table->string('status_from')->nullable(); // Previous status
            $table->string('status_to')->nullable(); // New status
            
            // Metadata
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('pre_employment_application_id')->references('id')->on('pre_employment_applications')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('facility_id');
            $table->index('pre_employment_application_id');
            $table->index('performed_by');
            $table->index('recipient_id');
            $table->index('activity_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hiring_activity_logs');
    }
};
