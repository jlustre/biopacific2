<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('applicant_code', 6)->nullable()->index()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['applicant_code']);
            $table->dropColumn('applicant_code');
        });
    }
};
