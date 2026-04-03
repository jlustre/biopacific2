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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained('job_openings')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('applicant_code', 6)->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('desired_position')->nullable();
            $table->string('department')->nullable();
            $table->string('employment_type', 20)->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('status')->default('pending'); // pending, reviewed, accepted, rejected
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('access_token', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->json('audit_log')->nullable();
            $table->timestamp('viewed_at')->nullable();
            // Additional applicant fields
            $table->string('position_applied_for')->nullable();
            $table->enum('employment_type_detail', ['full_time', 'part_time', 'temporary', 'other'])->nullable();
            $table->string('employment_type_other')->nullable();
            $table->string('shift_preference')->nullable();
            $table->date('date_available')->nullable();
            $table->string('wage_salary_expected')->nullable();
            $table->boolean('worked_here_before')->default(false);
            $table->text('worked_here_when_where')->nullable();
            $table->boolean('relatives_work_here')->default(false);
            $table->text('relatives_details')->nullable();
            $table->string('how_heard_about_us')->nullable();
            $table->string('how_heard_other')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
