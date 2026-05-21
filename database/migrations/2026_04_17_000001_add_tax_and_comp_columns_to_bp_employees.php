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
        Schema::table('bp_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('action_id')->nullable()->after('ssn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            $table->dropColumn([
                'action_id',
            ]);
        });
    }
};