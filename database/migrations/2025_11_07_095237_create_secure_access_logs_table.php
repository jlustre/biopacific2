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
        Schema::create('secure_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('token_type'); // 'inquiry', 'tour_request', 'job_application'
            $table->unsignedBigInteger('record_id'); // ID of the inquiry/tour_request/job_application
            $table->unsignedBigInteger('facility_id');
            $table->string('access_token', 64);
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->string('staff_email')->nullable(); // Only set if staff verification passed
            $table->string('access_status'); // 'success', 'unauthorized', 'expired', 'invalid'
            $table->json('request_headers')->nullable(); // For forensic analysis
            $table->string('session_id')->nullable();
            $table->timestamp('access_time');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['token_type', 'record_id']);
            $table->index(['facility_id', 'access_time']);
            $table->index('access_token');
            $table->index(['ip_address', 'access_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secure_access_logs');
    }
};
