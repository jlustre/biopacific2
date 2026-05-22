<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bp_emp_addresses') || !Schema::hasColumn('bp_emp_addresses', 'address_type')) {
            return;
        }

        DB::statement('UPDATE `bp_emp_addresses` SET `address_type` = UPPER(`address_type`)');

        DB::statement("ALTER TABLE `bp_emp_addresses` MODIFY `address_type` ENUM('H','W','O','M') NOT NULL DEFAULT 'H'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('bp_emp_addresses') || !Schema::hasColumn('bp_emp_addresses', 'address_type')) {
            return;
        }

        DB::table('bp_emp_addresses')
            ->where('address_type', 'M')
            ->update(['address_type' => 'H']);

        DB::statement("ALTER TABLE `bp_emp_addresses` MODIFY `address_type` ENUM('H','W','O') NOT NULL DEFAULT 'H'");
    }
};
