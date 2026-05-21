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
            $table->string('union_code', 50)->nullable()->after('action_id');
            $table->date('effdt_of_membership')->nullable()->after('union_code');
            $table->string('email', 191)->nullable()->after('effdt_of_membership');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            $table->dropColumn([
                'union_code',
                'effdt_of_membership',
                'email',
            ]);
        });
    }
};