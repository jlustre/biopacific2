<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
            $table->string('recipient');
            $table->string('full_name');
            $table->string('relationship')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->date('preferred_date');
            $table->string('preferred_time');
            $table->json('interests')->nullable();
            $table->text('message')->nullable();
            $table->boolean('consent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tour_requests');
    }
}