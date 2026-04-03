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
            $table->string('emp_id'); // Foreign key to bp_employees.emp_id
            $table->enum('phone_type', ['M', 'H', 'W', 'O']); // mobile, home, work, other
            $table->string('phone_number', 30);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->foreign('emp_id')->references('emp_id')->on('bp_employees');
            $table->index(['emp_id', 'phone_type'], 'idx_emp_phone_type');
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
