<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('positions', 'position_title')) {
                $columnsToDrop[] = 'position_title';
            }

            if (Schema::hasColumn('positions', 'dept_code')) {
                $columnsToDrop[] = 'dept_code';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'position_title')) {
                $table->string('position_title')->nullable()->after('position_code');
            }

            if (!Schema::hasColumn('positions', 'dept_code')) {
                $table->string('dept_code')->nullable()->after('department_id');
            }
        });
    }
};