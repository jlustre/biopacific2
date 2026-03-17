<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('skills_competency', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->date('verified_dt')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills_competency');
    }
};
