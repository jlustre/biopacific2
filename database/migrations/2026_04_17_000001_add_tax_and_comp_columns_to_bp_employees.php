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
            $table->unsignedBigInteger('hourly_status_id')->nullable()->after('action_id');
            $table->integer('std_hrs_week')->nullable()->after('hourly_status_id');
            $table->unsignedBigInteger('federal_tax_data_id')->nullable()->after('std_hrs_week');
            $table->unsignedBigInteger('state_tax_data_id')->nullable()->after('federal_tax_data_id');
            $table->unsignedBigInteger('local_tax_data_id')->nullable()->after('state_tax_data_id');
            $table->unsignedBigInteger('compensation_rate_id')->nullable()->after('local_tax_data_id');
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
                'hourly_status_id',
                'std_hrs_week',
                'federal_tax_data_id',
                'state_tax_data_id',
                'local_tax_data_id',
                'compensation_rate_id',
            ]);
        });
    }
};