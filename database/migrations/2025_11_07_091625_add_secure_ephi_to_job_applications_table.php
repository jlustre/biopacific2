<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('status');
            $table->timestamp('expires_at')->nullable()->after('access_token');
            $table->json('audit_log')->nullable()->after('expires_at');
            $table->timestamp('viewed_at')->nullable()->after('audit_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['access_token', 'expires_at', 'audit_log', 'viewed_at']);
        });
    }
};
