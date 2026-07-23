<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bp_employees') || Schema::hasColumn('bp_employees', 'is_active')) {
            return;
        }

        Schema::table('bp_employees', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('user_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bp_employees') || ! Schema::hasColumn('bp_employees', 'is_active')) {
            return;
        }

        Schema::table('bp_employees', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
