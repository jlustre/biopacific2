<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bp_emp_assignments', function (Blueprint $table) {
            $table->id('assign_id');
            $table->string('emp_id');
            $table->date('effdt'); // Effective Date
            $table->integer('effseq')->default(0); // Sequence for same-day changes
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->unsignedBigInteger('dept_id')->nullable();
            $table->unsignedBigInteger('job_code_id')->nullable();
            $table->unsignedBigInteger('reports_to_emp_id')->nullable();
            $table->enum('reg_temp', ['r', 't'])->default('r'); // Regular, Temporary
            $table->enum('full_part_time', ['ft', 'pt', 'pd'])->default('ft'); // Full-time, Part-time, Per Diem
            // Standard Audit
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
            // Constraints & Performance Indexing
            // $table->foreign('emp_id')->references('emp_id')->on('bp_employees'); // Removed to break circular dependency
            $table->index(['emp_id', 'effdt', 'effseq'], 'idx_bp_assign_hist');
            // Link to the bargaining unit; NULL if the employee is Non-Union
            $table->unsignedBigInteger('bargaining_unit_id')->nullable();
            $table->date('union_seniority_dt')->nullable(); // Often different from hire date
            $table->foreign('bargaining_unit_id')->references('unit_id')->on('bp_bargaining_units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_assignments');
    }
};
