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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->timestamp('event_date')->nullable();
            $table->string('location')->nullable();
            $table->boolean('status')->default(true); // true=published, false=draft
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->enum('scope', ['company', 'facility'])->default('company');
            $table->timestamps();
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
