<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_help_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->string('category', 64);
            $table->string('priority', 16)->default('normal');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 32)->nullable();
            $table->string('employee_num', 64)->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('preferred_contact', 16)->default('email');
            $table->string('best_time_to_reach', 32)->nullable();
            $table->text('steps_to_reproduce')->nullable();
            $table->json('attachments')->nullable();
            $table->boolean('no_phi_confirmed')->default(false);
            $table->string('status', 32)->default('open');
            $table->boolean('is_read')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_help_requests');
    }
};
