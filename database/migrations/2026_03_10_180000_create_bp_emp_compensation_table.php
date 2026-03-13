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
        Schema::create('bp_emp_compensation', function (Blueprint $table) {
            $table->id('comp_id');
            $table->string('emp_id'); // Foreign key to bp_employees.emp_id
            $table->date('effdt'); // Effective date
            $table->integer('effseq')->default(0); // Sequence for same-day changes
            $table->decimal('base_rate', 12, 2); // Base hourly or salary rate
            $table->enum('rate_type', ['h', 's']); // e.g. 'hourly', 'salary'
            $table->decimal('fte', 4, 2)->nullable(); // Full-time equivalent
            $table->enum('pay_frequency', ['b', 'm', 'w'])->nullable(); // e.g. 'biweekly', 'monthly'
            $table->string('reason_code', 10)->nullable(); // e.g. 'hire', 'promo', 'adjust'
            $table->timestamps();

            $table->foreign('emp_id')->references('emp_id')->on('bp_employees');
            $table->index(['emp_id', 'effdt', 'effseq'], 'idx_emp_comp_hist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_compensation');
    }
};
