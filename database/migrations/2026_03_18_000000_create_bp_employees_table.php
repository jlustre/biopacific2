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
        Schema::create('bp_employees', function (Blueprint $table) {
            $table->id('id'); // Primary Key
            $table->string('employee_num')->unique(); // Business Identifier
            $table->string('ssn', 11)->unique(); // Social Security Number
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->enum('gender', ['M', 'F', 'O', 'N'])->nullable();
            $table->unsignedBigInteger('assignment_id')->nullable();
            // $table->foreign('assignment_id')->references('assign_id')->on('bp_emp_assignments')->nullOnDelete(); // Removed to break circular dependency
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_employees');
    }
};
