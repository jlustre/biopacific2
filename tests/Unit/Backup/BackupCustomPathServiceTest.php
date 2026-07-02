<?php

namespace Tests\Unit\Backup;

use App\Services\Backup\BackupCustomPathService;
use App\Services\Backup\BackupDestinationResolver;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupCustomPathServiceTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = storage_path('app/backup-custom-path-test-' . uniqid());
        File::ensureDirectoryExists($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }

        parent::tearDown();
    }

    public function test_custom_destination_is_always_available(): void
    {
        $destinations = app(BackupDestinationResolver::class)->available();

        $this->assertArrayHasKey('custom', $destinations);
        $this->assertTrue($destinations['custom']['needs_path'] ?? false);
    }

    public function test_ensure_exists_creates_missing_folder(): void
    {
        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'nested-backups';
        $service = app(BackupCustomPathService::class);

        $resolved = $service->ensureExists($path, requireWritable: true);

        $this->assertDirectoryExists($resolved);
        $this->assertTrue(is_writable($resolved));
    }

    public function test_register_disk_uses_normalized_path(): void
    {
        $service = app(BackupCustomPathService::class);
        $disk = $service->registerDisk($this->tempDir);

        $this->assertSame(BackupCustomPathService::DISK_NAME, $disk);
        $this->assertSame(realpath($this->tempDir), config('filesystems.disks.' . $disk . '.root'));
    }

    public function test_list_zip_archives_returns_only_zip_files(): void
    {
        file_put_contents($this->tempDir . '/notes.txt', 'ignore me');

        $zipPath = $this->tempDir . '/backup-one.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('manifest.json', json_encode(['backup_type' => 'structural']));
        $zip->close();

        $archives = app(BackupCustomPathService::class)->listZipArchives($this->tempDir);

        $this->assertCount(1, $archives);
        $this->assertSame('backup-one.zip', $archives[0]['filename']);
    }

    public function test_read_manifest_from_absolute_file(): void
    {
        $manifest = [
            'backup_type' => 'structural',
            'created_at' => now()->toIso8601String(),
            'included_tables' => ['users'],
        ];

        $zipPath = $this->tempDir . '/manifest-test.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->close();

        $decoded = app(BackupCustomPathService::class)->readManifestFromAbsoluteFile($zipPath);

        $this->assertSame('structural', $decoded['backup_type']);
        $this->assertSame(['users'], $decoded['included_tables']);
    }

    public function test_remember_stores_path_in_session(): void
    {
        $service = app(BackupCustomPathService::class);
        $resolved = $service->remember($this->tempDir);

        $this->assertSame($resolved, session(BackupCustomPathService::SESSION_KEY));
        $this->assertSame($resolved, $service->lastUsed());
    }

    public function test_browse_lists_subdirectory_entries(): void
    {
        $child = $this->tempDir . DIRECTORY_SEPARATOR . 'child-folder';
        File::ensureDirectoryExists($child);

        $result = app(BackupCustomPathService::class)->browse($this->tempDir);

        $this->assertSame(realpath($this->tempDir), $result['current']);
        $this->assertCount(1, $result['entries']);
        $this->assertSame('child-folder', $result['entries'][0]['name']);
    }

    public function test_browse_without_path_returns_roots_on_windows_or_slash_on_unix(): void
    {
        $result = app(BackupCustomPathService::class)->browse();

        $this->assertNull($result['current']);
        $this->assertNotEmpty($result['roots']);
    }
}
