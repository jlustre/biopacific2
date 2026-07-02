<?php

namespace Tests\Unit\Backup;

use App\Services\Backup\BackupDestinationResolver;
use App\Services\Backup\BackupTableResolver;
use Tests\TestCase;

class BackupFolderAndDestinationTest extends TestCase
{
    public function test_available_folders_lists_individual_paths(): void
    {
        $folders = app(BackupTableResolver::class)->availableFolders();

        $this->assertArrayHasKey('public::employee_documents', $folders);
        $this->assertSame('employee_documents', $folders['public::employee_documents']['path']);
    }

    public function test_resolve_file_paths_uses_selected_folders_only(): void
    {
        $resolver = app(BackupTableResolver::class);

        $paths = $resolver->resolveFilePaths(['public::avatars'], []);

        $this->assertCount(1, $paths);
        $this->assertSame('avatars', $paths[0]['path']);
    }

    public function test_local_destination_is_always_available(): void
    {
        $destinations = app(BackupDestinationResolver::class)->available();

        $this->assertArrayHasKey('local', $destinations);
        $this->assertSame('local', $destinations['local']['disk']);
    }
}
