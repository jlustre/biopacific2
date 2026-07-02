<?php

namespace App\Livewire\Admin\Backups;

use App\Models\Backup;
use App\Services\Backup\RestoreService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class BackupDetailsModal extends Component
{
    use AuthorizesRequests;

    public bool $showModal = false;

    public ?Backup $backup = null;

    /** @var array<string, mixed> */
    public array $preview = [];

    #[On('open-backup-details')]
    public function open(int $backupId, RestoreService $restoreService): void
    {
        $backup = Backup::query()->with(['creator', 'restorer', 'preRestoreBackup'])->findOrFail($backupId);
        $this->authorize('view', $backup);

        $this->backup = $backup;
        $this->preview = $backup->canRestore()
            ? $restoreService->previewBackup($backup)
            : ['manifest' => $backup->metadata['manifest'] ?? []];
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->backup = null;
        $this->preview = [];
    }

    public function render()
    {
        return view('livewire.admin.backups.backup-details-modal');
    }
}
