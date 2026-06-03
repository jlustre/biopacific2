<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_leadership_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('role_key', 64);
            $table->string('role_label')->nullable();
            $table->string('name')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_custom')->default(false);
            $table->timestamps();

            $table->unique(['facility_id', 'role_key']);
            $table->index(['facility_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_leadership_assignments');
    }
};
