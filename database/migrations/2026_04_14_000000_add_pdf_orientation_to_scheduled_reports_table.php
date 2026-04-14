<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->char('pdf_orientation', 1)->nullable()->comment('P=Portrait, L=Landscape');
        });
    }

    public function down()
    {
        Schema::table('scheduled_reports', function (Blueprint $table) {
            $table->dropColumn('pdf_orientation');
        });
    }
};
