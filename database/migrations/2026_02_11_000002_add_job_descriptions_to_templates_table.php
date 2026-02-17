<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_description_templates', function (Blueprint $table) {
            // Add column to store job descriptions as JSON
            $table->json('job_descriptions')->nullable()->after('contents');
        });
    }

    public function down(): void
    {
        Schema::table('job_description_templates', function (Blueprint $table) {
            $table->dropColumn('job_descriptions');
        });
    }
};
