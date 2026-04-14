<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->string('report_format')->default('csv')->after('notifications_enabled');
        });
    }
    public function down()
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->dropColumn('report_format');
        });
    }
};
