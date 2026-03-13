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
        Schema::table('bp_bargaining_units', function (Blueprint $table) {
            $table->dropColumn('seniority_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bp_bargaining_units', function (Blueprint $table) {
            $table->date('seniority_dt')->nullable()->after('unit_name');
        });
    }
};