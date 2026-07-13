<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_training_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('frequency', 16)->default('annual'); // hiring | annual
            $table->json('position_ids')->nullable(); // null/["global"] = all positions
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['frequency', 'is_active']);
        });

        Schema::create('employee_training_completions', function (Blueprint $table) {
            $table->id();
            $table->string('employee_num');
            $table->foreignId('employee_training_item_id')
                ->constrained('employee_training_items')
                ->cascadeOnDelete();
            /** hire = hiring trainings; otherwise assessment period id as string */
            $table->string('period_key', 32);
            $table->unsignedBigInteger('assessment_period_id')->nullable();
            $table->string('status', 16)->default('not_started'); // not_started | in_progress | submitted | rejected | completed | na
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('assessment_period_id')
                ->references('id')
                ->on('employee_assessment_periods')
                ->nullOnDelete();

            $table->unique(
                ['employee_num', 'employee_training_item_id', 'period_key'],
                'etc_emp_item_period_unique'
            );
            $table->index(['employee_num', 'assessment_period_id'], 'etc_emp_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_training_completions');
        Schema::dropIfExists('employee_training_items');
    }
};
