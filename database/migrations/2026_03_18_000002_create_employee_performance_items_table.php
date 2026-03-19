<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employee_performance_items', function (Blueprint $table) {
            $table->id();
            $table->string('section'); // e.g. I. JOB SKILLS AND KNOWLEDGE
            $table->string('item'); // e.g. 'Understands the job role and duties.'
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_performance_items');
    }
};
