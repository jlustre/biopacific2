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
        // BP_EMPLOYEES: The "Person" Master Record
        Schema::create('bp_employees', function (Blueprint $table) {
            $table->id('id'); // Primary Key
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('emp_id')->unique(); // Business Identifier
            $table->string('ssn', 11)->unique(); // Encrypt in production
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('dob');
            $table->date('original_hire_dt');
            $table->enum('gender', ['M', 'F', 'O', 'N']);
            $table->boolean('is_active')->default(true);
            // Audit Columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps(); // Generates created_at and updated_at
            $table->softDeletes(); // For compliance: never hard delete
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
