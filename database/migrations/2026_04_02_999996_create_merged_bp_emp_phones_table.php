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
        Schema::create('bp_emp_phones', function (Blueprint $table) {
            $table->id('phone_id');
            $table->string('employee_num'); // Foreign key to bp_employees.employee_num
            $table->enum('phone_type', ['M', 'H', 'W', 'O']); // mobile, home, work, other
            $table->date('effdt');
            $table->integer('effseq')->default(0);
            $table->string('phone_number', 30);
            $table->enum('is_primary', ['Y', 'N'])->default('N');
            $table->timestamps();
            $table->foreign('employee_num')->references('employee_num')->on('bp_employees');
            $table->index(['employee_num', 'phone_type'], 'idx_emp_phone_type');
            $table->index(['employee_num', 'effdt', 'effseq'], 'idx_bp_emp_phone_hist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_phones');
    }
};
