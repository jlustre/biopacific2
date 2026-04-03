<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bp_emp_health_screenings', function (Blueprint $table) {
            $table->id('screening_id');
            $table->unsignedBigInteger('emp_id'); // Foreign key to bp_employees.id
            $table->string('screening_type', 100); // e.g. tb test, physical, etc.
            $table->date('screening_date');
            $table->date('expiry_date')->nullable();
            $table->string('result', 100)->nullable();
            $table->string('provider', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('emp_id')->references('id')->on('bp_employees');
            $table->index(['emp_id', 'screening_type', 'screening_date'], 'idx_emp_screen_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_health_screenings');
    }
};
