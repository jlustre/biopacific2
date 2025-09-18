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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->default('Quality care for your loved ones');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('hero_image_url')->nullable();
            $table->string('facility_image')->nullable();
            $table->string('headline')->nullable();
            $table->string('subheadline')->nullable();
            $table->string('about_image_url')->nullable();
            $table->string('hero_video_id')->nullable();
            $table->text('about_text')->nullable();
            $table->string('location_map')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('hours')->nullable();
            $table->unsignedSmallInteger('beds')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('years')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->json('hipaa_flags')->nullable();
            $table->string('npp_url')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
