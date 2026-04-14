<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('scheduled_report_runs', function (Blueprint $table) {
            $table->longText('result_json')->nullable()->after('result_path')->comment('Stores the actual result data as JSON');
        });
    }

    public function down()
    {
        Schema::table('scheduled_report_runs', function (Blueprint $table) {
            $table->dropColumn('result_json');
        });
    }
};
