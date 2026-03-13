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
        Schema::create('bp_emp_credentials', function (Blueprint $table) {
            $table->id('credential_id');
            $table->string('emp_id'); // Foreign key to bp_employees.emp_id
            $table->string('credential_type', 50); // e.g. rn, lvn, cna, etc.
            $table->string('credential_number', 100);
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issuing_authority', 100)->nullable();
            $table->string('verified_via')->nullable(); // cdph website, etc.
            $table->date('last_verified_dt')->nullable();
            $table->enum('status', ['a', 'e', 's']); // active, expired, suspended
            $table->timestamps();

            $table->foreign('emp_id')->references('emp_id')->on('bp_employees');
            $table->index(['emp_id', 'credential_type', 'expiry_date'], 'idx_emp_cred_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_emp_credentials');
    }
};
