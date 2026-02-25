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
            $table->string('drivers_license_state')->nullable()->after('drivers_license_number');
            $table->date('drivers_license_expiration')->nullable()->after('drivers_license_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_employment_applications', function (Blueprint $table) {
            $table->dropColumn(['drivers_license_state', 'drivers_license_expiration']);
        });
    }
};
