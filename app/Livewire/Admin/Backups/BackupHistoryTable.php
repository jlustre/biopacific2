<?php

namespace App\Livewire\Admin\Backups;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class BackupHistoryTable extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    #[On('backup-module-refresh')]
    public function refreshTable(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function showDetails(int $backupId): void
    {
        $this->dispatch('open-backup-details', backupId: $backupId);
    }

    public function confirmRestore(int $backupId): void
    {
        $this->dispatch('open-restore-modal', backupId: $backupId)->to(RestoreBackupForm::class);
    }

    public function deleteBackup(int $backupId, BackupService $backupService): void
    {
        $backup = Backup::query()->findOrFail($backupId);
        $this->authorize('delete', $backup);

        $backupService->delete($backup);
        $this->dispatch('backup-module-refresh');
        session()->flash('backup_success', 'Backup deleted successfully.');
    }

    public function render()
    {
        $this->authorize('viewAny', Backup::class);

        $backups = Backup::query()
            ->with(['creator', 'restorer'])
            ->when($this->search !== '', function ($query) {
                $query->where('backup_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->typeFilter !== '', fn ($query) => $query->where('backup_type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        $hasProcessing = Backup::query()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        return view('livewire.admin.backups.backup-history-table', [
            'backups' => $backups,
            'hasProcessing' => $hasProcessing,
            'backupTypes' => \App\Support\Backup\BackupType::labels(),
            'backupStatuses' => \App\Support\Backup\BackupStatus::labels(),
        ]);
    }
}
