<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facility_faq', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('faq_id');
            $table->timestamps();
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->foreign('faq_id')->references('id')->on('faqs')->onDelete('cascade');
            $table->unique(['facility_id', 'faq_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_faq');
    }
};
