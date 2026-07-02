<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\Backup\RestoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessRestoreJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $backupId,
        public string $restoreType,
    ) {}

    public function handle(RestoreService $restoreService): void
    {
        $backup = Backup::query()->find($this->backupId);
        if (! $backup) {
            return;
        }

        try {
            $restoreService->processRestore($backup, $this->restoreType);
        } catch (\Throwable $e) {
            Log::error('ProcessRestoreJob failed', [
                'backup_id' => $this->backupId,
                'restore_type' => $this->restoreType,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
