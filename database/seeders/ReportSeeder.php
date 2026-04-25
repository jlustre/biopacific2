<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optionally truncate the table first
        // Schema::disableForeignKeyConstraints();
        // DB::table('reports')->truncate();
        // Schema::enableForeignKeyConstraints();

        DB::table('reports')->insert([
            [
                'id' => 1,
                'category_id' => 7,
                'name' => 'Get All Facilities domain',
                'description' => 'Get facilities id, name and domain names.',
                'sql_template' => 'SELECT `id`,`name`,`domain` FROM `facilities` WHERE `is_active` = :is_active',
                'parameters' => '[{"name": "is_active", "type": "integer", "label": "Is Active"}]',
                'is_active' => 1,
                'visibility' => 'roles',
                'visible_roles' => '["admin", "facility-admin", "facility-editor"]',
                'visible_facilities' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'category_id' => 14,
                'name' => 'Generate Reports Seeder',
                'description' => 'Use for generating seeder for Reports table',
                'sql_template' => 'SELECT `id`,`category_id`,`name`,`description`,`sql_template`,`parameters`,`is_active`, `visibility`,`visible_roles`,`visible_facilities` FROM `reports`',
                'parameters' => '[]',
                'is_active' => 1,
                'visibility' => 'admin',
                'visible_roles' => '[]',
                'visible_facilities' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'category_id' => 13,
                'name' => 'List of Documents That need Expiry Tracking',
                'description' => 'List of Documents That need to be tracked its expiration date.',
                'sql_template' => 'SELECT `id`,`name` FROM `upload_types` WHERE `requires_expiry` = :requires_expiry',
                'parameters' => '[{"name": "requires_expiry", "type": "boolean", "label": "Requires Expiry"}]',
                'is_active' => 1,
                'visibility' => 'admin',
                'visible_roles' => '[]',
                'visible_facilities' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
