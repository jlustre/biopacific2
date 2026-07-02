<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class BackupDestinationResolver
{
    /**
     * @return array<string, array{key: string, label: string, description: string, disk: string, directory: string, icon: string}>
     */
    public function available(): array
    {
        $destinations = [
            'local' => [
                'key' => 'local',
                'label' => 'Local server storage',
                'description' => 'Save to the application backup folder on this server.',
                'disk' => (string) config('backup.disk', 'local'),
                'directory' => trim((string) config('backup.directory', 'backups'), '/'),
                'icon' => 'server',
            ],
        ];

        if ($this->cloudAvailable()) {
            $destinations['cloud'] = [
                'key' => 'cloud',
                'label' => 'Cloud storage',
                'description' => 'Save directly to configured cloud storage (e.g. Amazon S3).',
                'disk' => (string) config('backup.remote_disk', 's3'),
                'directory' => trim((string) config('backup.remote_directory', 'backups'), '/'),
                'icon' => 'cloud',
            ];
        }

        if ($this->externalAvailable()) {
            $path = (string) config('backup.external_path');
            $destinations['external'] = [
                'key' => 'external',
                'label' => 'External drive / USB (.env)',
                'description' => 'Save to mounted external path: ' . $path,
                'disk' => 'backup_external',
                'directory' => trim((string) config('backup.external_directory', 'biopacific-backups'), '/'),
                'icon' => 'usb',
            ];
        }

        $destinations['custom'] = [
            'key' => 'custom',
            'label' => 'Choose folder on demand',
            'description' => 'Type any folder on this server (local drive, USB, or network path).',
            'disk' => BackupCustomPathService::DISK_NAME,
            'directory' => '',
            'icon' => 'folder',
            'needs_path' => true,
        ];

        return $destinations;
    }

    /**
     * @return array{key: string, label: string, description: string, disk: string, directory: string, icon: string}
     */
    public function resolve(string $key): array
    {
        if ($key === 'custom') {
            throw new InvalidArgumentException('Custom destinations require a folder path.');
        }

        $destinations = $this->available();

        if (! isset($destinations[$key])) {
            throw new InvalidArgumentException("Unknown or unavailable backup destination: {$key}");
        }

        return $destinations[$key];
    }

    public function cloudAvailable(): bool
    {
        if (! (bool) config('backup.destinations.cloud.enabled', false)) {
            return false;
        }

        $disk = (string) config('backup.remote_disk', 's3');

        return array_key_exists($disk, config('filesystems.disks', []));
    }

    public function externalAvailable(): bool
    {
        if (! (bool) config('backup.destinations.external.enabled', false)) {
            return false;
        }

        $path = config('backup.external_path');

        return is_string($path)
            && $path !== ''
            && is_dir($path)
            && is_writable($path);
    }

    public function ensureDestinationReady(string $key): void
    {
        $destination = $this->resolve($key);
        $disk = $destination['disk'];

        if (! array_key_exists($disk, config('filesystems.disks', []))) {
            throw new InvalidArgumentException("Backup destination disk [{$disk}] is not configured.");
        }

        Storage::disk($disk)->makeDirectory($destination['directory']);
    }

    public function label(string $key): string
    {
        return $this->available()[$key]['label'] ?? ucfirst($key);
    }
}
