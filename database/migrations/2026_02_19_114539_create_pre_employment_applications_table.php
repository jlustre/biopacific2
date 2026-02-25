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
        Schema::create('pre_employment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft, submitted, completed, hired, rejected
            
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            
            // Current Address & Phone
            $table->string('current_address');
            $table->string('phone_number');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('county')->nullable();
            
            // Position Information
            $table->string('position_applied_for');
            $table->string('employment_type'); // full_time, part_time, temporary, other
            $table->string('employment_type_other')->nullable();
            $table->string('shift_preference')->nullable();
            $table->date('date_available')->nullable();
            $table->string('wage_salary_expected')->nullable();
            
            // Previous Employment
            $table->boolean('worked_here_before')->default(false);
            $table->text('worked_here_when_where')->nullable();
            $table->boolean('relatives_work_here')->default(false);
            $table->text('relatives_details')->nullable();
            
            // Driver's License
            $table->boolean('has_drivers_license')->default(false);
            $table->string('drivers_license_number')->nullable();
            
            // How Heard About Us
            $table->string('how_heard_about_us')->nullable();
            $table->string('how_heard_other')->nullable();
            
            // Work Authorization
            $table->boolean('authorized_to_work_usa')->default(false);
            
            // Contact Current Employer
            $table->boolean('contact_current_employer')->nullable();
            
            // Complex data stored as JSON
            $table->json('work_experience')->nullable(); // Array of work experience entries
            $table->json('previous_addresses')->nullable(); // Array of previous addresses
            $table->json('education')->nullable(); // Array of education entries
            $table->text('work_history_description')->nullable();
            $table->text('additional_references')->nullable();
            $table->text('professional_affiliations')->nullable();
            $table->boolean('license_suspended')->nullable();
            $table->text('license_suspended_explanation')->nullable();
            $table->text('special_skills')->nullable();
            
            // Hiring Information
            $table->date('hired_date')->nullable();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->timestamp('hired_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Index for common queries
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_employment_applications');
    }
};
