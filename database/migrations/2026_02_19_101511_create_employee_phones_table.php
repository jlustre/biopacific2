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
        Schema::create('employee_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number', 20);
            $table->string('phone_type')->default('primary'); // primary, mobile, home, work
            $table->date('effective_date');
            $table->integer('effective_sequence')->default(1);
            $table->timestamps();
            
            // Index for efficient querying
            $table->index(['employee_id', 'effective_date', 'effective_sequence'], 'emp_phone_eff_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_phones');
    }
};
