<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class BackupTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function migrateFreshUsing(): array
    {
        return [
            '--path' => [
                base_path('database/migrations/0001_01_01_000000_create_users_table.php'),
                base_path('database/migrations/2025_08_13_003724_create_permission_tables.php'),
                base_path('database/migrations/2025_11_14_000001_create_settings_table.php'),
                base_path('database/migrations/2025_08_15_000001_create_audit_logs_table.php'),
                base_path('database/migrations/2026_07_02_000001_create_backups_table.php'),
            ],
            '--realpath' => true,
        ];
    }
}
