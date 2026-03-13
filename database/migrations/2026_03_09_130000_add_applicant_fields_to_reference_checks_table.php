<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->string('applicant_name')->nullable();
            $table->string('applicant_signature')->nullable();
            $table->date('signature_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('reference_checks', function (Blueprint $table) {
            $table->dropColumn(['applicant_name', 'applicant_signature', 'signature_date']);
        });
    }
};
