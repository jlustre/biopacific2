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
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            $table->string('current_address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state', 2)->nullable()->change();
            $table->string('zip_code', 10)->nullable()->change();
            $table->string('employment_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            $table->string('current_address')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('state', 2)->nullable(false)->change();
            $table->string('zip_code', 10)->nullable(false)->change();
            $table->string('employment_type')->nullable(false)->change();
        });
    }
};
