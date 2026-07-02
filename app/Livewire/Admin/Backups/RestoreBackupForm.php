<?php

namespace App\Livewire\Admin\Backups;

use App\Models\Backup;
use App\Services\Backup\BackupCustomPathService;
use App\Services\Backup\RestoreService;
use App\Support\Backup\BackupType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class RestoreBackupForm extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?int $backupId = null;

    public $uploadedFile;

    public string $folderPath = '';

    public string $selectedFolderFile = '';

    /** @var list<array{filename: string, path: string, size: int, size_label: string, modified_at: string}> */
    public array $folderArchives = [];

    public string $restoreType = BackupType::STRUCTURAL;

    public bool $confirmed = false;

    public bool $createPreBackup = true;

    public bool $showModal = false;

    public bool $isRestoring = false;

    /** @var array<string, mixed> */
    public array $preview = [];

    public string $errorMessage = '';

    public function mount(BackupCustomPathService $customPaths): void
    {
        $this->authorize('viewAny', Backup::class);
        $this->folderPath = $customPaths->lastUsed() ?? '';
    }

    protected function rules(): array
    {
        return [
            'restoreType' => 'required|in:' . implode(',', BackupType::all()),
            'createPreBackup' => 'boolean',
        ];
    }

    #[On('open-restore-modal')]
    public function openForBackup(int $backupId, RestoreService $restoreService): void
    {
        $backup = Backup::query()->findOrFail($backupId);
        $this->authorize('restore', $backup);

        $this->backupId = $backupId;
        $this->uploadedFile = null;
        $this->selectedFolderFile = '';
        $this->confirmed = false;
        $this->createPreBackup = true;
        $this->errorMessage = '';
        $this->isRestoring = false;
        $this->preview = $restoreService->previewBackup($backup);
        $this->restoreType = $this->suggestedRestoreType($backup);
        $this->showModal = true;
    }

    public function updatedUploadedFile(RestoreService $restoreService): void
    {
        $this->backupId = null;
        $this->selectedFolderFile = '';
        $this->errorMessage = '';

        if (! $this->uploadedFile) {
            $this->preview = [];

            return;
        }

        try {
            $this->preview = $restoreService->previewUploadedFile($this->uploadedFile);
            $this->restoreType = $this->preview['backup_type'] ?? BackupType::FULL;
        } catch (\Throwable $e) {
            $this->errorMessage = $this->friendlyError($e);
            $this->preview = [];
        }
    }

    public function loadFolderBackups(BackupCustomPathService $customPaths): void
    {
        $this->validate([
            'folderPath' => 'required|string|max:500',
        ]);

        $this->backupId = null;
        $this->uploadedFile = null;
        $this->selectedFolderFile = '';
        $this->preview = [];
        $this->errorMessage = '';

        try {
            $this->folderArchives = $customPaths->listZipArchives($this->folderPath);
            $customPaths->remember($this->folderPath);

            if ($this->folderArchives === []) {
                $this->errorMessage = 'No backup .zip files found in that folder.';
            }
        } catch (\Throwable $e) {
            $this->folderArchives = [];
            $this->errorMessage = $this->friendlyError($e);
        }
    }

    public function selectFolderArchive(string $filename, RestoreService $restoreService): void
    {
        $this->backupId = null;
        $this->uploadedFile = null;
        $this->selectedFolderFile = $filename;
        $this->errorMessage = '';

        try {
            $this->preview = $restoreService->previewFolderArchive($this->folderPath, $filename);
            $this->restoreType = $this->preview['backup_type'] ?? BackupType::FULL;
        } catch (\Throwable $e) {
            $this->errorMessage = $this->friendlyError($e);
            $this->preview = [];
        }
    }

    public function restore(RestoreService $restoreService): void
    {
        $maxMb = (int) config('backup.max_upload_size_mb', 512);

        $this->errorMessage = '';

        try {
            $this->validate([
                'restoreType' => 'required|in:' . implode(',', BackupType::all()),
                'createPreBackup' => 'boolean',
            ]);
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first() ?? 'Validation failed.';

            return;
        }

        if (! $this->confirmed) {
            $this->errorMessage = 'Check the confirmation box before restoring.';

            return;
        }

        if (! $this->backupId && ! $this->uploadedFile && $this->selectedFolderFile === '') {
            $this->errorMessage = 'Choose a backup from history, folder, or upload a .zip file first.';

            return;
        }

        $this->isRestoring = true;

        try {
            if ($this->backupId) {
                $backup = Backup::query()->findOrFail($this->backupId);
                $this->authorize('restore', $backup);
                $restoreService->restoreExisting(
                    backup: $backup,
                    restoreType: $this->restoreType,
                    confirmed: $this->confirmed,
                    createPreBackup: $this->createPreBackup,
                );
            } elseif ($this->selectedFolderFile !== '') {
                $restoreService->restoreFromFolderFile(
                    folderPath: $this->folderPath,
                    filename: $this->selectedFolderFile,
                    restoreType: $this->restoreType,
                    confirmed: $this->confirmed,
                    createPreBackup: $this->createPreBackup,
                );
            } else {
                $this->validate([
                    'uploadedFile' => 'required|file|mimes:zip|max:' . ($maxMb * 1024),
                ]);
                $restoreService->restoreUploadedFile(
                    file: $this->uploadedFile,
                    restoreType: $this->restoreType,
                    confirmed: $this->confirmed,
                    createPreBackup: $this->createPreBackup,
                );
            }

            $this->showModal = false;
            $this->reset(['backupId', 'uploadedFile', 'selectedFolderFile', 'confirmed', 'preview', 'folderArchives']);
            $this->dispatch('backup-module-refresh');
            session()->flash('backup_success', 'Restore completed successfully.');
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first() ?? 'Validation failed.';
        } catch (\Throwable $e) {
            $this->errorMessage = $this->friendlyError($e);
        } finally {
            $this->isRestoring = false;
        }
    }

    public function closeModal(): void
    {
        if ($this->isRestoring) {
            return;
        }

        $this->showModal = false;
        $this->errorMessage = '';
    }

    protected function suggestedRestoreType(Backup $backup): string
    {
        return match ($backup->backup_type) {
            BackupType::FILES => BackupType::FILES,
            BackupType::TRANSACTIONAL => BackupType::TRANSACTIONAL,
            BackupType::STRUCTURAL => BackupType::STRUCTURAL,
            default => BackupType::FULL,
        };
    }

    protected function friendlyError(\Throwable $e): string
    {
        $message = trim($e->getMessage());

        if (str_contains($message, 'Integrity constraint violation')) {
            return 'Restore failed because related database records could not be imported safely. Please contact support if this continues.';
        }

        if (strlen($message) > 240) {
            return substr($message, 0, 240) . '…';
        }

        return $message !== '' ? $message : 'Restore failed. Please try again.';
    }

    public function render()
    {
        return view('livewire.admin.backups.restore-backup-form', [
            'restoreTypes' => BackupType::labels(),
        ]);
    }
}
