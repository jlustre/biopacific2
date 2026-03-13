<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->string('company_address')->nullable()->after('company');
            $table->string('reference_title')->nullable()->after('reference_name');
        });
    }

    public function down()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->dropColumn(['company_address', 'reference_title']);
        });
    }
};
