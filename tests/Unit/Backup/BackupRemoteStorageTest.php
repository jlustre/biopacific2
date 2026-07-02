<?php

namespace Tests\Unit\Backup;

use App\Services\Backup\BackupRemoteStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupRemoteStorageTest extends TestCase
{
    public function test_it_mirrors_local_backup_archive_to_remote_disk(): void
    {
        Storage::fake('local');
        Storage::fake('s3');

        config([
            'backup.remote_mirror_enabled' => true,
            'backup.remote_disk' => 's3',
            'backup.remote_directory' => 'offsite-backups',
        ]);

        Storage::disk('local')->put('backups/sample.zip', 'zip-binary-content');

        $remotePath = app(BackupRemoteStorage::class)->mirrorFromLocal('backups/sample.zip', 'local');

        $this->assertSame('offsite-backups/backups/sample.zip', $remotePath);
        $this->assertTrue(Storage::disk('s3')->exists('offsite-backups/backups/sample.zip'));
        $this->assertSame('zip-binary-content', Storage::disk('s3')->get('offsite-backups/backups/sample.zip'));
    }

    public function test_mirror_is_skipped_when_remote_storage_disabled(): void
    {
        Storage::fake('local');

        config([
            'backup.remote_mirror_enabled' => false,
            'backup.remote_disk' => 's3',
        ]);

        Storage::disk('local')->put('backups/sample.zip', 'zip-binary-content');

        $remotePath = app(BackupRemoteStorage::class)->mirrorFromLocal('backups/sample.zip', 'local');

        $this->assertNull($remotePath);
    }
}
