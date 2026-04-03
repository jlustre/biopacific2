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
            $table->string('emp_id'); // Foreign key to bp_employees.emp_id
            $table->enum('address_type', ['H', 'W', 'O']); // e.g. 'home', 'work', 'other', etc.
            $table->date('effdt'); // Effective date for the address
            $table->integer('effseq')->default(0); // Effective sequence for the address
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->default('ca');
            $table->string('zip', 10)->nullable();
            $table->string('country', 50)->default('usa');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->foreign('emp_id')->references('emp_id')->on('bp_employees');
            $table->index(['emp_id', 'address_type'], 'idx_emp_addr_type');
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
