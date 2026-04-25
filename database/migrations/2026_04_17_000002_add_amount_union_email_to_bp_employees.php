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
            $table->decimal('amount', 15, 2)->nullable()->after('compensation_rate_id');
            $table->string('union_code', 50)->nullable()->after('amount');
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
                'amount',
                'union_code',
                'effdt_of_membership',
                'email',
            ]);
        });
    }
};