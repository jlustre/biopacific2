<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('import_mapping_presets', function (Blueprint $table) {
            $table->unsignedBigInteger('facility_id')->default(99)->after('user_id');
            $table->index('facility_id');
        });
    }
    public function down()
    {
        Schema::table('import_mapping_presets', function (Blueprint $table) {
            $table->dropIndex(['facility_id']);
            $table->dropColumn('facility_id');
        });
    }
};
