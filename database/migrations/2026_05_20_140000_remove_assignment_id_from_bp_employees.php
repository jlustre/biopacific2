<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bp_employees') && Schema::hasColumn('bp_employees', 'assignment_id')) {
            Schema::table('bp_employees', function (Blueprint $table) {
                $table->dropColumn('assignment_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bp_employees') && !Schema::hasColumn('bp_employees', 'assignment_id')) {
            Schema::table('bp_employees', function (Blueprint $table) {
                $table->unsignedBigInteger('assignment_id')->nullable()->after('gender');
            });
        }
    }
};
