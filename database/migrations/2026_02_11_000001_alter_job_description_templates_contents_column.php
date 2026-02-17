<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('job_description_templates', function (Blueprint $table) {
            // Change contents column from string to longText to accommodate rich HTML content
            $table->longText('contents')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('job_description_templates', function (Blueprint $table) {
            // Revert back to string
            $table->string('contents')->nullable()->change();
        });
    }
};
