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
        Schema::create('bp_emp_addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->string('employee_num'); // Foreign key to bp_employees.employee_num
            $table->enum('address_type', ['H', 'W', 'O', 'M'])->default('H'); // home, work, other, mailing
            $table->date('effdt'); // Effective date for the address
            $table->integer('effseq')->default(0); // Effective sequence for the address
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->default('ca');
            $table->string('zip', 10)->nullable();
            $table->string('country', 50)->default('usa');
            $table->enum('is_primary', ['Y', 'N'])->default('N');
            $table->timestamps();
            $table->foreign('employee_num')->references('employee_num')->on('bp_employees');
            $table->index(['employee_num', 'address_type'], 'idx_emp_addr_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_addresses');
    }
};
