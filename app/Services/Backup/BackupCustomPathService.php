<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use ZipArchive;

class BackupCustomPathService
{
    public const SESSION_KEY = 'backup.last_custom_folder';

    public const DISK_NAME = 'backup_custom';

    /**
     * @return list<string>
     */
    protected function blockedPathPrefixes(): array
    {
        return array_values(array_filter([
            PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows' : '/etc',
            PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files' : '/usr',
            PHP_OS_FAMILY === 'Windows' ? null : '/bin',
            PHP_OS_FAMILY === 'Windows' ? null : '/sbin',
        ]));
    }

    public function normalize(string $path): string
    {
        $path = trim($path);

        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    public function remember(string $path): string
    {
        $resolved = $this->ensureExists($path, requireWritable: false);
        session([self::SESSION_KEY => $resolved]);

        return $resolved;
    }

    public function lastUsed(): ?string
    {
        $path = session(self::SESSION_KEY);

        if (! is_string($path) || $path === '' || ! is_dir($path)) {
            return null;
        }

        return $path;
    }

    public function ensureExists(string $path, bool $requireWritable = true): string
    {
        $normalized = $this->normalize($path);

        if ($normalized === '') {
            throw new InvalidArgumentException('Folder path is required.');
        }

        if (! File::isDirectory($normalized)) {
            File::ensureDirectoryExists($normalized);
        }

        $resolved = realpath($normalized);
        if (! $resolved || ! is_dir($resolved)) {
            throw new InvalidArgumentException('Unable to access folder: ' . $path);
        }

        $this->assertAllowedPath($resolved);

        if (! is_readable($resolved)) {
            throw new InvalidArgumentException('Folder is not readable: ' . $resolved);
        }

        if ($requireWritable && ! is_writable($resolved)) {
            throw new InvalidArgumentException('Folder is not writable: ' . $resolved);
        }

        return $resolved;
    }

    public function registerDisk(string $path): string
    {
        $resolved = $this->ensureExists($path, requireWritable: false);

        config([
            'filesystems.disks.' . self::DISK_NAME => [
                'driver' => 'local',
                'root' => $resolved,
                'throw' => false,
                'report' => false,
            ],
        ]);

        return self::DISK_NAME;
    }

    /**
     * @return array{key: string, label: string, description: string, disk: string, directory: string, icon: string, custom_path: string}
     */
    public function destinationForPath(string $path): array
    {
        $resolved = $this->ensureExists($path, requireWritable: true);
        $this->registerDisk($resolved);

        return [
            'key' => 'custom',
            'label' => 'Custom folder',
            'description' => 'Save to: ' . $resolved,
            'disk' => self::DISK_NAME,
            'directory' => '',
            'icon' => 'folder',
            'custom_path' => $resolved,
        ];
    }

    /**
     * @return list<array{filename: string, path: string, size: int, size_label: string, modified_at: string}>
     */
    public function listZipArchives(string $path): array
    {
        $resolved = $this->ensureExists($path, requireWritable: false);
        $archives = [];

        foreach (File::files($resolved) as $file) {
            if (strtolower($file->getExtension()) !== 'zip') {
                continue;
            }

            $archives[] = [
                'filename' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => (int) $file->getSize(),
                'size_label' => \App\Models\Backup::formatBytes((int) $file->getSize()),
                'modified_at' => date('M j, Y g:i A', $file->getMTime()),
            ];
        }

        usort($archives, fn (array $a, array $b) => strcmp($b['filename'], $a['filename']));

        return $archives;
    }

    public function readManifestFromAbsoluteFile(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new InvalidArgumentException('Backup file not found.');
        }

        $zip = new ZipArchive;
        if ($zip->open($absolutePath) !== true) {
            throw new InvalidArgumentException('Selected file is not a valid ZIP backup.');
        }

        $manifest = $zip->getFromName('manifest.json');
        $zip->close();

        if ($manifest === false) {
            throw new InvalidArgumentException('Selected backup is missing manifest.json.');
        }

        $decoded = json_decode($manifest, true);
        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Selected backup manifest is invalid JSON.');
        }

        return $decoded;
    }

    protected function assertAllowedPath(string $resolved): void
    {
        foreach ($this->blockedPathPrefixes() as $blocked) {
            if (str_starts_with(strtolower($resolved), strtolower($blocked))) {
                throw new InvalidArgumentException('This folder path is not allowed for backup operations.');
            }
        }
    }

    public function isAllowedPath(string $path): bool
    {
        $resolved = realpath($this->normalize($path)) ?: $this->normalize($path);

        try {
            $this->assertAllowedPath($resolved);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * @return list<array{path: string, name: string, writable: bool}>
     */
    public function listRoots(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $roots = [];

            foreach (range('A', 'Z') as $letter) {
                $drive = $letter . ':\\';
                if (! is_dir($drive)) {
                    continue;
                }

                $roots[] = [
                    'path' => $drive,
                    'name' => $drive,
                    'writable' => is_writable($drive),
                ];
            }

            return $roots;
        }

        return [[
            'path' => '/',
            'name' => '/',
            'writable' => is_writable('/'),
        ]];
    }

    /**
     * @return array{
     *     current: ?string,
     *     parent: ?string,
     *     roots: list<array{path: string, name: string, writable: bool}>,
     *     entries: list<array{path: string, name: string, writable: bool}>
     * }
     */
    public function browse(?string $path = null): array
    {
        if ($path === null || trim($path) === '') {
            return [
                'current' => null,
                'parent' => null,
                'roots' => $this->listRoots(),
                'entries' => [],
            ];
        }

        $normalized = $this->normalize($path);
        if (! is_dir($normalized)) {
            throw new InvalidArgumentException('Folder not found: ' . $path);
        }

        $resolved = realpath($normalized);
        if (! $resolved || ! is_dir($resolved)) {
            throw new InvalidArgumentException('Unable to access folder: ' . $path);
        }

        $this->assertAllowedPath($resolved);

        if (! is_readable($resolved)) {
            throw new InvalidArgumentException('Folder is not readable: ' . $resolved);
        }

        $entries = [];

        foreach (File::directories($resolved) as $directory) {
            if (! $this->isAllowedPath($directory) || ! is_readable($directory)) {
                continue;
            }

            $entries[] = [
                'path' => $directory,
                'name' => basename($directory),
                'writable' => is_writable($directory),
            ];
        }

        usort($entries, fn (array $a, array $b) => strcasecmp($a['name'], $b['name']));

        return [
            'current' => $resolved,
            'parent' => $this->parentDirectory($resolved),
            'roots' => [],
            'entries' => $entries,
        ];
    }

    protected function parentDirectory(string $resolved): ?string
    {
        if (PHP_OS_FAMILY === 'Windows' && preg_match('/^[A-Z]:\\\\$/i', $resolved)) {
            return null;
        }

        $parent = dirname($resolved);

        if ($parent === $resolved || $parent === '.') {
            return null;
        }

        return $parent;
    }
}
