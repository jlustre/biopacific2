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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_global')->default(true);
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('set null');
            $table->unsignedBigInteger('author')->nullable();
            $table->foreign('author')->references('id')->on('users')->onDelete('set null');
            $table->string('status')->default('draft');
            $table->string('photo1')->nullable();
            $table->string('photo2')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('version')->default('1.0');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
