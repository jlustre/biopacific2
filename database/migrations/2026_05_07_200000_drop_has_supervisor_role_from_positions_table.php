<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('positions', 'has_supervisor_role')) {
            return;
        }

        DB::table('positions')->update([
            'supervisor_role' => DB::raw('COALESCE(has_supervisor_role, supervisor_role, 0)'),
        ]);

        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('has_supervisor_role');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('positions', 'has_supervisor_role')) {
            return;
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('has_supervisor_role')->default(false)->after('supervisor_role');
        });

        DB::table('positions')->update([
            'has_supervisor_role' => DB::raw('supervisor_role'),
        ]);
    }
};