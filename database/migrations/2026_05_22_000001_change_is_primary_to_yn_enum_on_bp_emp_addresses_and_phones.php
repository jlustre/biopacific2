<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertIsPrimaryColumn('bp_emp_phones');
        $this->convertIsPrimaryColumn('bp_emp_addresses');
    }

    public function down(): void
    {
        $this->revertIsPrimaryColumn('bp_emp_phones');
        $this->revertIsPrimaryColumn('bp_emp_addresses');
    }

    protected function convertIsPrimaryColumn(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'is_primary')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('is_primary_yn', 1)->default('N');
            });
            DB::table($table)->update([
                'is_primary_yn' => DB::raw("CASE WHEN is_primary IN (1, '1') THEN 'Y' ELSE 'N' END"),
            ]);
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('is_primary');
            });
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('is_primary_yn', 'is_primary');
            });

            return;
        }

        DB::statement("ALTER TABLE `{$table}` ADD COLUMN `is_primary_yn` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `is_primary`");

        DB::statement("UPDATE `{$table}` SET `is_primary_yn` = CASE WHEN `is_primary` IN (1, '1') THEN 'Y' ELSE 'N' END");

        DB::statement("ALTER TABLE `{$table}` DROP COLUMN `is_primary`");
        DB::statement("ALTER TABLE `{$table}` CHANGE `is_primary_yn` `is_primary` ENUM('Y','N') NOT NULL DEFAULT 'N'");
    }

    protected function revertIsPrimaryColumn(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'is_primary')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->boolean('is_primary_bool')->default(false);
            });
            DB::table($table)->update([
                'is_primary_bool' => DB::raw("CASE WHEN is_primary = 'Y' THEN 1 ELSE 0 END"),
            ]);
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('is_primary');
            });
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('is_primary_bool', 'is_primary');
            });

            return;
        }

        DB::statement("ALTER TABLE `{$table}` ADD COLUMN `is_primary_bool` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_primary`");

        DB::statement("UPDATE `{$table}` SET `is_primary_bool` = CASE WHEN `is_primary` = 'Y' THEN 1 ELSE 0 END");

        DB::statement("ALTER TABLE `{$table}` DROP COLUMN `is_primary`");
        DB::statement("ALTER TABLE `{$table}` CHANGE `is_primary_bool` `is_primary` TINYINT(1) NOT NULL DEFAULT 0");
    }
};
