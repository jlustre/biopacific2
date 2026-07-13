<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_tasks', function (Blueprint $table) {
            $table->string('action_url', 500)->nullable()->after('description');
            $table->string('action_label', 80)->nullable()->after('action_url');
        });
    }

    public function down(): void
    {
        Schema::table('personal_tasks', function (Blueprint $table) {
            $table->dropColumn(['action_url', 'action_label']);
        });
    }
};
