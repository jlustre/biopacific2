<?php

namespace App\Services\Backup;

use App\Jobs\ProcessBackupJob;
use App\Models\Backup;
use App\Models\User;
use App\Support\Backup\BackupStatus;
use App\Support\Backup\BackupType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupService
{
    public function __construct(
        protected BackupTableResolver $tableResolver,
        protected BackupFileManager $fileManager,
        protected BackupDatabaseExporter $databaseExporter,
        protected BackupLogger $logger,
        protected BackupRemoteStorage $remoteStorage,
        protected BackupDestinationResolver $destinationResolver,
        protected BackupCustomPathService $customPaths,
    ) {}

    /**
     * @param  list<string>  $selectedSections
     * @param  list<string>  $selectedFileGroups
     * @param  list<string>  $selectedFolders
     */
    public function create(
        string $backupType,
        array $selectedSections = [],
        array $selectedFileGroups = [],
        array $selectedFolders = [],
        ?string $notes = null,
        ?string $backupName = null,
        string $destination = 'local',
        ?string $customPath = null,
        bool $dispatch = true,
        ?int $createdBy = null,
    ): Backup {
        if ($destination === 'custom') {
            if (! filled($customPath)) {
                throw new \InvalidArgumentException('Choose a folder path for this backup.');
            }
            $destinationConfig = $this->customPaths->destinationForPath($customPath);
            $this->customPaths->remember($destinationConfig['custom_path']);
        } else {
            $destinationConfig = $this->destinationResolver->resolve($destination);
            $this->destinationResolver->ensureDestinationReady($destination);
        }

        $tables = $this->tableResolver->tablesForBackup($backupType, $selectedSections, $selectedFileGroups);

        $backup = Backup::query()->create([
            'backup_name' => $backupName ?: $this->defaultBackupName($backupType),
            'backup_type' => $backupType,
            'included_tables' => $tables,
            'included_sections' => $selectedSections,
            'status' => BackupStatus::PENDING,
            'notes' => $notes,
            'created_by' => $createdBy ?? Auth::id(),
            'metadata' => [
                'destination' => $destination,
                'storage_disk' => $destinationConfig['disk'],
                'storage_directory' => $destinationConfig['directory'],
                'custom_path' => $destinationConfig['custom_path'] ?? null,
                'file_groups' => $selectedFileGroups,
                'selected_folders' => $selectedFolders,
                'queued_at' => now()->toIso8601String(),
            ],
        ]);

        $this->logger->logCreated($backup);

        if (! config('backup.process_immediately', true) && $dispatch) {
            ProcessBackupJob::dispatch($backup->id);

            return $backup->fresh();
        }

        return $this->process($backup->fresh());
    }

    /**
     * @return array{key: string, label: string, disk: string, directory: string}
     */
    protected function destinationConfigForBackup(Backup $backup): array
    {
        $destinationKey = (string) ($backup->metadata['destination'] ?? 'local');

        if ($destinationKey === 'custom') {
            $customPath = (string) ($backup->metadata['custom_path'] ?? '');

            return [
                'key' => 'custom',
                'label' => $customPath !== '' ? 'Custom folder: ' . $customPath : 'Custom folder',
                'disk' => (string) ($backup->metadata['storage_disk'] ?? BackupCustomPathService::DISK_NAME),
                'directory' => (string) ($backup->metadata['storage_directory'] ?? ''),
            ];
        }

        return $this->destinationResolver->resolve($destinationKey);
    }

    public function process(Backup $backup): Backup
    {
        $backup->update([
            'status' => BackupStatus::PROCESSING,
            'error_message' => null,
        ]);

        $workingDir = $this->fileManager->createWorkingDirectory($backup);

        try {
            if ($customPath = $backup->metadata['custom_path'] ?? null) {
                $this->customPaths->registerDisk($customPath);
            }

            $this->fileManager->ensureDirectory(
                $backup->metadata['storage_disk'] ?? null,
                $backup->metadata['storage_directory'] ?? null,
            );

            $tables = $backup->included_tables ?? [];
            $export = ['record_counts' => [], 'exported_tables' => []];

            if ($tables !== []) {
                $export = $this->databaseExporter->exportTables($workingDir, $tables);
            }

            $selectedFolders = $backup->metadata['selected_folders'] ?? [];
            $fileGroups = $backup->metadata['file_groups'] ?? [];
            $filePaths = $this->tableResolver->resolveFilePaths($selectedFolders, $fileGroups);
            $includedFiles = [];

            if ($backup->backup_type === BackupType::FILES || $backup->backup_type === BackupType::FULL) {
                $includedFiles = $this->fileManager->copyFilesToWorkingDirectory(
                    $workingDir,
                    $filePaths
                );
            }

            $creator = $backup->creator;
            $destinationKey = (string) ($backup->metadata['destination'] ?? 'local');
            $destination = $this->destinationConfigForBackup($backup);
            $manifest = [
                'backup_name' => $backup->backup_name,
                'backup_type' => $backup->backup_type,
                'created_at' => now()->toIso8601String(),
                'app_name' => config('app.name'),
                'app_version' => config('backup.app_version'),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'destination' => $destinationKey,
                'destination_label' => $destination['label'],
                'created_by' => [
                    'id' => $creator?->id,
                    'name' => $creator?->name,
                    'email' => $creator?->email,
                ],
                'included_tables' => $export['exported_tables'],
                'record_counts' => $export['record_counts'],
                'included_file_paths' => $includedFiles,
                'included_sections' => $backup->included_sections ?? [],
                'selected_folders' => $selectedFolders,
                'checksum' => null,
            ];

            File::put(
                $workingDir . '/manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $stored = $this->fileManager->storeArchiveFromWorkingDirectory(
                $workingDir,
                $this->fileManager->buildArchiveFilename($backup),
                $destination['disk'],
                $destination['directory'],
            );

            $remotePath = null;
            if ($destinationKey === 'local' && $this->remoteStorage->isEnabled()) {
                $remotePath = $this->remoteStorage->mirrorFromLocal($stored['path'], $stored['disk']);
            }

            $backup->update([
                'file_path' => $stored['path'],
                'file_size' => $stored['size'],
                'included_tables' => $export['exported_tables'],
                'metadata' => array_merge($backup->metadata ?? [], [
                    'manifest' => $manifest,
                    'storage_disk' => $stored['disk'],
                    'completed_at' => now()->toIso8601String(),
                    'remote_path' => $remotePath,
                    'remote_disk' => $remotePath ? $this->remoteStorage->disk() : null,
                ]),
                'status' => BackupStatus::COMPLETED,
            ]);

            $this->logger->logCompleted($backup->fresh());
        } catch (Throwable $e) {
            $backup->update([
                'status' => BackupStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            $this->logger->logFailed($backup, $e->getMessage());
        } finally {
            $this->fileManager->cleanupWorkingDirectory($backup);
        }

        return $backup->fresh();
    }

    public function delete(Backup $backup): void
    {
        $backup->registerStorageDisk();
        $this->fileManager->deleteArchive($backup->file_path, $backup->disk());
        $this->remoteStorage->delete($backup->metadata['remote_path'] ?? null);
        $this->logger->logDeleted($backup);
        $backup->delete();
    }

    public function createScheduledBackup(): Backup
    {
        $schedule = config('backup.schedule', []);

        return $this->create(
            backupType: (string) ($schedule['type'] ?? BackupType::FULL),
            notes: (string) ($schedule['notes'] ?? 'Scheduled nightly backup'),
            backupName: 'Scheduled — ' . now()->format('M j, Y g:i A'),
            dispatch: true,
            createdBy: null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(): array
    {
        $latest = Backup::query()->where('status', BackupStatus::COMPLETED)->latest()->first();
        $lastRestore = Backup::query()->whereNotNull('restored_at')->latest('restored_at')->first();

        return [
            'total_backups' => Backup::query()->count(),
            'completed_backups' => Backup::query()->where('status', BackupStatus::COMPLETED)->count(),
            'failed_backups' => Backup::query()->where('status', BackupStatus::FAILED)->count(),
            'processing_backups' => Backup::query()->whereIn('status', [BackupStatus::PENDING, BackupStatus::PROCESSING])->count(),
            'latest_backup' => $latest,
            'last_restore' => $lastRestore,
            'storage_used' => $this->fileManager->totalStorageUsed(),
            'storage_used_label' => Backup::formatBytes($this->fileManager->totalStorageUsed()),
        ];
    }

    protected function defaultBackupName(string $backupType): string
    {
        $label = BackupType::labels()[$backupType] ?? ucfirst($backupType);

        return $label . ' — ' . now()->format('M j, Y g:i A');
    }

    public function createPreRestoreBackup(User $user): Backup
    {
        return $this->create(
            backupType: BackupType::FULL,
            notes: 'Automatic pre-restore safety backup',
            backupName: 'Pre-restore — ' . now()->format('M j, Y g:i A'),
            dispatch: false,
        );
    }
}
