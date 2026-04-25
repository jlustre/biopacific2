<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('marital_status_id')->nullable()->after('assignment_id');
            $table->unsignedBigInteger('ethnic_group_id')->nullable()->after('marital_status_id');
            $table->unsignedBigInteger('military_status_id')->nullable()->after('ethnic_group_id');
            $table->unsignedBigInteger('citizenship_status_id')->nullable()->after('military_status_id');
        });
    }

    public function down()
    {
        Schema::table('bp_employees', function (Blueprint $table) {
            $table->dropColumn(['marital_status_id', 'ethnic_group_id', 'military_status_id', 'citizenship_status_id']);
        });
    }
};
