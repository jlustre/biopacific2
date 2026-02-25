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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'current_address',
                'phone_number',
                'city',
                'state',
                'zip_code',
                'county'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('current_address')->after('last_name');
            $table->string('phone_number')->after('current_address');
            $table->string('city')->after('phone_number');
            $table->string('state')->after('city');
            $table->string('zip_code')->after('state');
            $table->string('county')->nullable()->after('zip_code');
        });
    }
};
