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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // New field for service name
            $table->string('short_description')->nullable(); // New field for short description
            $table->boolean('is_global')->default(true)->index(); // Already present
            $table->text('detailed_description')->nullable(); // New field for detailed description
            $table->string('image')->nullable(); // New field for image path
            $table->text('icon')->nullable(); // inline SVG ok
            $table->json('features')->nullable(); // New field for features (array of strings)
            $table->unsignedSmallInteger('order')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
