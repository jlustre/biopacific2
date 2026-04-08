<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('optionstypes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('isActive')->default(1);
            $table->timestamps();
        });

        Schema::create('selectoptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id');
            $table->integer('sort_order')->default(0);
            $table->string('name');
            $table->string('value')->nullable();
            $table->boolean('isActive')->default(1);
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('optionstypes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('selectoptions');
        Schema::dropIfExists('optionstypes');
    }
};
