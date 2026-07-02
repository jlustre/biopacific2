<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupRemoteStorage
{
    public function isEnabled(): bool
    {
        $disk = (string) config('backup.remote_disk', '');

        return (bool) config('backup.remote_mirror_enabled', false)
            && $disk !== ''
            && array_key_exists($disk, config('filesystems.disks', []));
    }

    public function disk(): string
    {
        return (string) config('backup.remote_disk', 's3');
    }

    public function remotePath(string $localRelativePath): string
    {
        $prefix = trim((string) config('backup.remote_directory', 'backups'), '/');

        return $prefix === ''
            ? ltrim($localRelativePath, '/')
            : $prefix . '/' . ltrim($localRelativePath, '/');
    }

    public function mirrorFromLocal(string $localRelativePath, string $localDisk): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $local = Storage::disk($localDisk);
        if (! $local->exists($localRelativePath)) {
            throw new RuntimeException('Local backup archive not found for remote mirror.');
        }

        $remotePath = $this->remotePath($localRelativePath);
        $stream = $local->readStream($localRelativePath);

        if ($stream === false) {
            throw new RuntimeException('Unable to read local backup archive for remote mirror.');
        }

        Storage::disk($this->disk())->writeStream($remotePath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $remotePath;
    }

    public function exists(?string $remotePath): bool
    {
        return is_string($remotePath)
            && $remotePath !== ''
            && $this->isEnabled()
            && Storage::disk($this->disk())->exists($remotePath);
    }

    public function delete(?string $remotePath): void
    {
        if (! is_string($remotePath) || $remotePath === '' || ! $this->isEnabled()) {
            return;
        }

        Storage::disk($this->disk())->delete($remotePath);
    }

    public function downloadStream(?string $remotePath)
    {
        if (! $this->exists($remotePath)) {
            return null;
        }

        return Storage::disk($this->disk())->readStream($remotePath);
    }

    public function downloadFilename(?string $remotePath): string
    {
        return basename((string) $remotePath) ?: 'backup.zip';
    }
}
