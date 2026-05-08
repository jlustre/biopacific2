<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_competency_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('employee_num');
            $table->unsignedBigInteger('assessment_period_id');
            $table->string('status', 32)->default('draft');
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('total_score')->default(0);
            $table->decimal('average_score', 6, 2)->default(0);
            $table->string('overall_rating', 32)->nullable();
            $table->text('comments')->nullable();
            $table->text('further_action_required')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_title')->nullable();
            $table->date('review_date')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employee_title')->nullable();
            $table->timestamp('employee_signed_at')->nullable();
            $table->timestamp('reviewer_signed_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->json('snapshot_json')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('assessment_period_id')
                ->references('id')
                ->on('employee_assessment_periods')
                ->cascadeOnDelete();
            $table->foreign('submitted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['employee_num', 'assessment_period_id'], 'eca_emp_period_unique');
            $table->index(['status', 'assessment_period_id'], 'eca_status_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_competency_assessments');
    }
};