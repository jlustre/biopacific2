<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->boolean('is_shutdown')->default(false);
            $table->string('shutdown_message')->nullable();
            $table->timestamp('shutdown_eta')->nullable();
        });
        Schema::create('global_shutdowns', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_shutdown')->default(false);
            $table->string('shutdown_message')->nullable();
            $table->timestamp('shutdown_eta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['is_shutdown', 'shutdown_message', 'shutdown_eta']);
        });
        Schema::dropIfExists('global_shutdowns');
    }
};
