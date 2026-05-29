<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->enum('type', ['employee', 'applicant']);
            $table->string('employee_num')->nullable();
            $table->unsignedBigInteger('job_application_id')->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email');
            $table->string('ssn_last4', 4)->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('used_by_user_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['employee_num', 'used_at']);
            $table->index(['job_application_id', 'used_at']);
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('used_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_codes');
    }
};
