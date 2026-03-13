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
        Schema::create('bp_positions', function (Blueprint $table) {
            $table->id('position_id');
            $table->string('position_code')->unique();
            $table->string('position_title');
            $table->string('dept_code'); // Foreign key to bp_departments.dept_code
            $table->boolean('has_supervisor_role')->default(false);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_positions');
    }
};
