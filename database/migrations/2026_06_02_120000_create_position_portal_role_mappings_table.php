<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_portal_role_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->string('role_name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('position_id');
            $table->index(['role_name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_portal_role_mappings');
    }
};
