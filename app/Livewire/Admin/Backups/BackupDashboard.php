<?php

namespace App\Livewire\Admin\Backups;

use App\Services\Backup\BackupService;
use Livewire\Attributes\On;
use Livewire\Component;

class BackupDashboard extends Component
{
    public array $stats = [];

    public function mount(BackupService $backupService): void
    {
        $this->loadStats($backupService);
    }

    #[On('backup-module-refresh')]
    public function refreshDashboard(BackupService $backupService): void
    {
        $this->loadStats($backupService);
    }

    protected function loadStats(BackupService $backupService): void
    {
        $this->stats = $backupService->dashboardStats();
    }

    public function render()
    {
        return view('livewire.admin.backups.backup-dashboard');
    }
}
