<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_training_items', function (Blueprint $table) {
            $table->string('content_url', 500)->nullable()->after('description');
            $table->string('provider_label', 120)->nullable()->after('content_url');
        });
    }

    public function down(): void
    {
        Schema::table('employee_training_items', function (Blueprint $table) {
            $table->dropColumn(['content_url', 'provider_label']);
        });
    }
};
