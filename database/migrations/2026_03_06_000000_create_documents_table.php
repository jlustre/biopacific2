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
        // Schema::create('documents', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('facility_id')->nullable();
        //     $table->unsignedBigInteger('user_id')->nullable();
        //     $table->string('document_type');
        //     $table->string('file_name');
        //     $table->string('file_path');
        //     $table->string('mime_type')->nullable();
        //     $table->unsignedBigInteger('file_size')->nullable();
        //     $table->unsignedBigInteger('created_by')->nullable();
        //     $table->timestamps();
        //
        //     $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        //     $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        //     $table->index('facility_id');
        //     $table->index('user_id');
        //     $table->index('document_type');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
