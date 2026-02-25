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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('current_address');
            $table->string('phone_number');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('county')->nullable();
            
            // Position Desired
            $table->string('position_applied_for');
            $table->enum('employment_type', ['full_time', 'part_time', 'temporary', 'other'])->default('full_time');
            $table->string('employment_type_other')->nullable();
            $table->string('shift_preference')->nullable(); // day, evening, weekend, any
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
            
            // Referral Source
            $table->string('how_heard_about_us')->nullable();
            $table->string('how_heard_other')->nullable();
            
            // Work Authorization
            $table->boolean('authorized_to_work_usa')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
