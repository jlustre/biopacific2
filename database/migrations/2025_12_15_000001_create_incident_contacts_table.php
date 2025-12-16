<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incident_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('incident_contacts');
    }
};
