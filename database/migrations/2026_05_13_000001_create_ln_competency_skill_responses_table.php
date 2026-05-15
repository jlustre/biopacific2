<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ln_competency_skill_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('competency_item_id');
            $table->string('response', 8)->nullable(); // E, S, U, N, yes, no, na, etc.
            $table->text('comments')->nullable();
            $table->boolean('is_draft')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'competency_item_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // You may want to add a foreign key for competency_item_id if the table exists
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ln_competency_skill_responses');
    }
};
