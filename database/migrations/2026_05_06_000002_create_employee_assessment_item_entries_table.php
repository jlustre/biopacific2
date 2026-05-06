<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_assessment_item_entries', function (Blueprint $table) {
            $table->id();
            $table->string('employee_num');
            $table->unsignedBigInteger('assessment_period_id');
            $table->string('assessment_type', 32);
            $table->string('item_key', 191);
            $table->string('item_label')->nullable();
            $table->unsignedBigInteger('source_item_id')->nullable();
            $table->string('rating', 1);
            $table->date('assessment_date');
            $table->unsignedBigInteger('assessed_by')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->timestamps();

            $table->foreign('assessment_period_id')
                ->references('id')
                ->on('employee_assessment_periods')
                ->cascadeOnDelete();
            $table->foreign('assessed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('revoked_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['employee_num', 'assessment_period_id', 'assessment_type'], 'eaie_emp_period_type_idx');
            $table->index(['employee_num', 'assessment_period_id', 'item_key'], 'eaie_emp_period_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_assessment_item_entries');
    }
};