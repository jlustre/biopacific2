<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_help_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32); // hr_inquiry | support
            $table->string('responsibility', 16)->default('secondary'); // primary | secondary
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('on_vacation')->default(false);
            $table->date('vacation_starts_at')->nullable();
            $table->date('vacation_ends_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['channel', 'is_active']);
            $table->index(['channel', 'responsibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_help_recipients');
    }
};
