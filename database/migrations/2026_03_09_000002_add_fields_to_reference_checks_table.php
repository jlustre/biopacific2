<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->string('reference_phone')->nullable();
            $table->string('reference_email')->nullable();
            $table->string('company')->nullable();
            $table->boolean('signed')->default(false);
            $table->date('signed_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->dropColumn(['reference_phone', 'reference_email', 'company', 'signed', 'signed_date']);
        });
    }
};
