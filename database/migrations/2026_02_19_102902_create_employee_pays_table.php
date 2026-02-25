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
        Schema::create('employee_pays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('pay_type'); // 'hourly' or 'salary'
            $table->decimal('amount', 10, 2); // hourly rate or annual salary
            $table->string('pay_frequency')->nullable(); // 'weekly', 'bi-weekly', 'monthly', etc.
            $table->text('notes')->nullable();
            $table->date('effective_date');
            $table->integer('effective_sequence')->default(1);
            $table->timestamps();
            
            // Index for efficient querying
            $table->index(['employee_id', 'effective_date', 'effective_sequence'], 'emp_pay_eff_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_pays');
    }
};
