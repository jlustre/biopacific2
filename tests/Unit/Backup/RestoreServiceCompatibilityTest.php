<?php

namespace Tests\Unit\Backup;

use App\Services\Backup\RestoreService;
use App\Support\Backup\BackupType;
use Tests\TestCase;

class RestoreServiceCompatibilityTest extends TestCase
{
    public function test_structural_restore_allows_full_and_structural_backups(): void
    {
        $service = app(RestoreService::class);

        $service->validateRestoreCompatibility(BackupType::FULL, BackupType::STRUCTURAL);
        $service->validateRestoreCompatibility(BackupType::STRUCTURAL, BackupType::STRUCTURAL);

        $this->assertTrue(true);
    }

    public function test_structural_restore_rejects_files_only_backup(): void
    {
        $service = app(RestoreService::class);

        $this->expectException(\RuntimeException::class);
        $service->validateRestoreCompatibility(BackupType::FILES, BackupType::STRUCTURAL);
    }

    public function test_full_restore_requires_full_backup(): void
    {
        $service = app(RestoreService::class);

        $this->expectException(\RuntimeException::class);
        $service->validateRestoreCompatibility(BackupType::STRUCTURAL, BackupType::FULL);
    }
}
