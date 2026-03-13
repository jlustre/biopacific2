<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('bp_bargaining_units', function (Blueprint $table) {
    $table->id('unit_id');
    $table->string('unit_name');
    $table->string('description')->nullable();
    $table->string('union_code'); // e.g., SEIU-UHW
    $table->string('local_number'); // e.g., Local 2015
    $table->string('contract_name');
    $table->date('contract_expiry'); // Critical for HR alerts
    $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bp_bargaining_units');
    }
};
