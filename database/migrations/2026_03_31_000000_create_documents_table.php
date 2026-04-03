<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::create('documents', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('facility_id');
        //     $table->unsignedBigInteger('user_id')->nullable();
        //     $table->string('type');
        //     $table->string('file_path');
        //     $table->string('original_filename');
        //     $table->timestamp('uploaded_at')->useCurrent();
        //     $table->timestamp('expires_at')->nullable();
        //     $table->text('description')->nullable();
        //     $table->timestamps();
        //
        //     $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
