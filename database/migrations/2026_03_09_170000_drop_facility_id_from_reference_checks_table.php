<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropColumn('facility_id');
        });
    }

    public function down()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->foreignId('facility_id')->nullable()->constrained();
        });
    }
};
