<?php

namespace App\Services\Backup;

use App\Jobs\ProcessRestoreJob;
use App\Models\Backup;
use App\Support\Backup\BackupStatus;
use App\Support\Backup\BackupType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RestoreService
{
    public function __construct(
        protected BackupTableResolver $tableResolver,
        protected BackupFileManager $fileManager,
        protected BackupDatabaseExporter $databaseExporter,
        protected BackupService $backupService,
        protected BackupLogger $logger,
        protected BackupCustomPathService $customPaths,
    ) {}

    public function previewBackup(Backup $backup): array
    {
        $manifest = $this->manifestForBackup($backup);

        return $this->buildPreview($manifest);
    }

    public function previewUploadedFile(UploadedFile $file): array
    {
        $manifest = $this->fileManager->readManifestFromUpload($file);

        return $this->buildPreview($manifest);
    }

    public function previewFolderArchive(string $folderPath, string $filename): array
    {
        $resolvedFolder = $this->customPaths->ensureExists($folderPath, requireWritable: false);
        $absolutePath = $resolvedFolder . DIRECTORY_SEPARATOR . basename($filename);
        $manifest = $this->customPaths->readManifestFromAbsoluteFile($absolutePath);

        return $this->buildPreview($manifest);
    }

    public function restoreFromFolderFile(
        string $folderPath,
        string $filename,
        string $restoreType,
        bool $confirmed,
        bool $createPreBackup = true,
    ): Backup {
        if (! $confirmed) {
            throw new \RuntimeException('Restore confirmation is required.');
        }

        $resolvedFolder = $this->customPaths->remember($folderPath);
        $safeName = basename($filename);
        $absolutePath = $resolvedFolder . DIRECTORY_SEPARATOR . $safeName;

        if (! is_file($absolutePath)) {
            throw new \RuntimeException('Backup file not found in the selected folder.');
        }

        $manifest = $this->customPaths->readManifestFromAbsoluteFile($absolutePath);
        $this->validateManifest($manifest);
        $this->validateRestoreCompatibility($manifest['backup_type'] ?? '', $restoreType);

        $disk = $this->customPaths->registerDisk($resolvedFolder);

        $backup = Backup::query()->create([
            'backup_name' => $manifest['backup_name'] ?? ('Folder restore — ' . $safeName),
            'backup_type' => $manifest['backup_type'] ?? BackupType::FULL,
            'file_path' => $safeName,
            'file_size' => (int) filesize($absolutePath),
            'included_tables' => $manifest['included_tables'] ?? [],
            'included_sections' => $manifest['included_sections'] ?? [],
            'metadata' => [
                'manifest' => $manifest,
                'source' => 'folder',
                'destination' => 'custom',
                'custom_path' => $resolvedFolder,
                'storage_disk' => $disk,
                'storage_directory' => '',
            ],
            'status' => BackupStatus::COMPLETED,
            'created_by' => Auth::id(),
        ]);

        return $this->restoreExisting($backup, $restoreType, true, $createPreBackup);
    }

    public function restoreExisting(
        Backup $backup,
        string $restoreType,
        bool $confirmed,
        bool $createPreBackup = true,
        bool $dispatch = true,
    ): Backup {
        if (! $confirmed) {
            throw new \RuntimeException('Restore confirmation is required.');
        }

        if (! $backup->canRestore()) {
            throw new \RuntimeException('This backup cannot be restored.');
        }

        $manifest = $this->manifestForBackup($backup);
        $this->validateRestoreCompatibility($manifest['backup_type'] ?? '', $restoreType);

        $preBackup = null;
        if ($createPreBackup && config('backup.pre_restore_backup', true)) {
            $preBackup = $this->backupService->createPreRestoreBackup(Auth::user());
        }

        $backup->update([
            'metadata' => array_merge($backup->metadata ?? [], [
                'pending_restore_type' => $restoreType,
                'pre_restore_backup_id' => $preBackup?->id,
            ]),
        ]);

        if ($preBackup) {
            $backup->update(['pre_restore_backup_id' => $preBackup->id]);
        }

        $this->logger->logRestoreStarted($backup, $restoreType, $preBackup);

        if ($dispatch && ! config('backup.process_immediately', true)) {
            ProcessRestoreJob::dispatch($backup->id, $restoreType);
        } else {
            $this->processRestore($backup->fresh(), $restoreType);
        }

        return $backup->fresh();
    }

    public function restoreUploadedFile(
        UploadedFile $file,
        string $restoreType,
        bool $confirmed,
        bool $createPreBackup = true,
    ): Backup {
        if (! $confirmed) {
            throw new \RuntimeException('Restore confirmation is required.');
        }

        $manifest = $this->fileManager->readManifestFromUpload($file);
        $this->validateManifest($manifest);
        $this->validateRestoreCompatibility($manifest['backup_type'] ?? '', $restoreType);

        $storedPath = $this->fileManager->storeUploadedBackup($file);

        $backup = Backup::query()->create([
            'backup_name' => $manifest['backup_name'] ?? ('Uploaded — ' . now()->format('M j, Y g:i A')),
            'backup_type' => $manifest['backup_type'] ?? BackupType::FULL,
            'file_path' => $storedPath,
            'file_size' => (int) $file->getSize(),
            'included_tables' => $manifest['included_tables'] ?? [],
            'included_sections' => $manifest['included_sections'] ?? [],
            'metadata' => ['manifest' => $manifest, 'source' => 'upload'],
            'status' => BackupStatus::COMPLETED,
            'created_by' => Auth::id(),
        ]);

        return $this->restoreExisting($backup, $restoreType, true, $createPreBackup);
    }

    public function processRestore(Backup $backup, string $restoreType): Backup
    {
        $workingDir = storage_path('app/backup-restore/' . $backup->id);
        File::ensureDirectoryExists($workingDir);

        try {
            $backup->registerStorageDisk();
            $this->fileManager->extractArchive($backup->file_path, $workingDir, $backup->disk());
            $manifest = $this->readManifestFromWorkingDir($workingDir);
            $this->validateManifest($manifest);
            $this->validateRestoreCompatibility($manifest['backup_type'] ?? '', $restoreType);

            Schema::disableForeignKeyConstraints();

            if (in_array($restoreType, [BackupType::FULL, BackupType::STRUCTURAL, BackupType::TRANSACTIONAL], true)) {
                $tables = $this->tablesForRestore($manifest, $restoreType);
                $this->databaseExporter->importTables($workingDir, $tables);
            }

            if (in_array($restoreType, [BackupType::FULL, BackupType::FILES], true)) {
                $this->restoreFilesFromWorkingDir($workingDir, $manifest['included_file_paths'] ?? []);
            }

            Schema::enableForeignKeyConstraints();

            $backup->update([
                'status' => BackupStatus::RESTORED,
                'restored_by' => Auth::id(),
                'restored_at' => now(),
                'metadata' => array_merge($backup->metadata ?? [], [
                    'last_restore_type' => $restoreType,
                    'restored_at' => now()->toIso8601String(),
                ]),
            ]);

            $this->logger->logRestoreCompleted($backup, $restoreType);
        } catch (Throwable $e) {
            Schema::enableForeignKeyConstraints();
            $this->logger->logRestoreFailed($backup, $e->getMessage());
            throw $e;
        } finally {
            if (is_dir($workingDir)) {
                File::deleteDirectory($workingDir);
            }
        }

        return $backup->fresh();
    }

    public function validateRestoreCompatibility(string $backupType, string $restoreType): void
    {
        $allowed = config('backup.restore_compatibility.' . $restoreType, []);

        if (! in_array($backupType, $allowed, true)) {
            throw new \RuntimeException(sprintf(
                'Cannot restore a "%s" backup using "%s" restore mode.',
                $backupType,
                $restoreType
            ));
        }
    }

    public function validateManifest(array $manifest): void
    {
        foreach (['backup_type', 'created_at', 'included_tables'] as $key) {
            if (! array_key_exists($key, $manifest)) {
                throw new \RuntimeException('Backup manifest is missing required field: ' . $key);
            }
        }
    }

    /**
     * @return list<string>
     */
    protected function tablesForRestore(array $manifest, string $restoreType): array
    {
        $included = $manifest['included_tables'] ?? [];

        if ($restoreType === BackupType::FULL) {
            return $this->tableResolver->filterExistingTables($included);
        }

        if ($restoreType === BackupType::STRUCTURAL) {
            $structural = config('backup-groups.structural_tables', []);

            return array_values(array_intersect($included, $structural));
        }

        if ($restoreType === BackupType::TRANSACTIONAL) {
            $transactional = config('backup-groups.transactional_tables', []);

            return array_values(array_intersect($included, $transactional));
        }

        return [];
    }

    protected function manifestForBackup(Backup $backup): array
    {
        if (! empty($backup->metadata['manifest']) && is_array($backup->metadata['manifest'])) {
            return $backup->metadata['manifest'];
        }

        $backup->registerStorageDisk();

        return $this->fileManager->readManifest($backup->file_path, $backup->disk());
    }

    protected function readManifestFromWorkingDir(string $workingDir): array
    {
        $path = $workingDir . '/manifest.json';
        if (! is_file($path)) {
            throw new \RuntimeException('Extracted backup is missing manifest.json.');
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Extracted backup manifest is invalid.');
        }

        return $decoded;
    }

    protected function buildPreview(array $manifest): array
    {
        $tables = $manifest['included_tables'] ?? [];
        $backupCounts = $manifest['record_counts'] ?? [];
        $currentCounts = $this->databaseExporter->currentRecordCounts($tables);
        $differences = [];

        foreach ($tables as $table) {
            $differences[$table] = [
                'backup' => $backupCounts[$table] ?? 0,
                'current' => $currentCounts[$table] ?? 0,
                'delta' => ($backupCounts[$table] ?? 0) - ($currentCounts[$table] ?? 0),
            ];
        }

        return [
            'manifest' => $manifest,
            'record_differences' => $differences,
            'backup_type' => $manifest['backup_type'] ?? null,
            'created_at' => $manifest['created_at'] ?? null,
            'created_by' => $manifest['created_by'] ?? null,
            'included_tables' => $tables,
            'included_file_paths' => $manifest['included_file_paths'] ?? [],
            'file_size_label' => isset($manifest['file_size'])
                ? Backup::formatBytes((int) $manifest['file_size'])
                : null,
        ];
    }

    protected function restoreFilesFromWorkingDir(string $workingDir, array $includedFilePaths): void
    {
        foreach ($includedFilePaths as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $diskName = $entry['disk'] ?? 'public';
            $path = $entry['path'] ?? null;
            $group = $entry['group'] ?? 'default';

            if (! $path) {
                continue;
            }

            $source = $workingDir . '/files/' . $group . '/' . $diskName . '/' . $path;
            if (! is_dir($source) && ! is_file($source)) {
                continue;
            }

            $disk = \Illuminate\Support\Facades\Storage::disk($diskName);
            if (is_dir($source)) {
                foreach (\Illuminate\Support\Facades\File::allFiles($source) as $file) {
                    $relative = str_replace($source . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $disk->put($path . '/' . str_replace('\\', '/', $relative), file_get_contents($file->getPathname()));
                }
            } elseif (is_file($source)) {
                $disk->put($path, file_get_contents($source));
            }
        }
    }
}
