<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bp_emp_tax_data', function (Blueprint $table) {
            $table->id('tax_id');
            $table->string('employee_num');
            $table->date('effdt');
            $table->integer('effseq')->default(0);
            $table->enum('fed_tax_data', ['1', '2'])->nullable(); //1=single, 2=married
            $table->decimal('fed_withholding_allowance', 10, 2)->nullable();
            $table->enum('state_tax_data', ['1', '2'])->nullable(); //1=single, 2=married
            $table->decimal('state_withholding_allowance1', 10, 2)->nullable();
            $table->enum('resident', ['Y', 'N'])->nullable(); //Y=resident, N=non-resident
            $table->decimal('local_withholding_allowance', 10, 2)->nullable();
            $table->string('locality', 100)->nullable();
            $table->string('county', 100)->nullable();
            $table->decimal('addl_withholding_percentage1', 10, 2)->nullable();
            $table->decimal('addl_withholding_amount1', 10, 2)->nullable();
            $table->decimal('addl_withholding_percentage2', 10, 2)->nullable();
            $table->decimal('addl_withholding_amount2', 10, 2)->nullable();
            $table->string('resident_state', 2)->default('CA');
            $table->timestamps();

            $table->foreign('employee_num')->references('employee_num')->on('bp_employees');
            $table->index(['employee_num', 'effdt', 'effseq'], 'idx_bp_emp_tax_hist');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bp_emp_tax_data');
    }
};
