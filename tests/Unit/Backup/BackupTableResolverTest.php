<?php

namespace Tests\Unit\Backup;

use App\Services\Backup\BackupTableResolver;
use App\Support\Backup\BackupType;
use Tests\BackupTestCase;

class BackupTableResolverTest extends BackupTestCase
{
    public function test_structural_backup_uses_configured_tables_when_no_sections_selected(): void
    {
        $resolver = app(BackupTableResolver::class);

        $tables = $resolver->tablesForBackup(BackupType::STRUCTURAL);

        $this->assertContains('users', $tables);
        $this->assertContains('settings', $tables);
        $this->assertNotContains('audit_logs', $tables);
    }

    public function test_section_selection_limits_exported_tables(): void
    {
        $resolver = app(BackupTableResolver::class);

        $tables = $resolver->tablesForBackup(BackupType::STRUCTURAL, ['system_settings']);

        $this->assertContains('settings', $tables);
        $this->assertNotContains('users', $tables);
    }

    public function test_transactional_backup_excludes_rbac_tables_by_default(): void
    {
        $resolver = app(BackupTableResolver::class);

        $tables = $resolver->tablesForBackup(BackupType::TRANSACTIONAL);

        $this->assertNotContains('roles', $tables);
        $this->assertNotContains('users', $tables);
    }
}
