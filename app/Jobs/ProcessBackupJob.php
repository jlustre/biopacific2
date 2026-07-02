<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBackupJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $backupId) {}

    public function handle(BackupService $backupService): void
    {
        $backup = Backup::query()->find($this->backupId);
        if (! $backup) {
            return;
        }

        try {
            $backupService->process($backup);
        } catch (\Throwable $e) {
            Log::error('ProcessBackupJob failed', [
                'backup_id' => $this->backupId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
