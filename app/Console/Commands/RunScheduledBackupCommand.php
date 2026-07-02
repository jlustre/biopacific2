<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class RunScheduledBackupCommand extends Command
{
    protected $signature = 'backup:run-scheduled';

    protected $description = 'Create the configured scheduled application backup';

    public function handle(BackupService $backupService): int
    {
        if (! config('backup.schedule.enabled', false)) {
            $this->warn('Scheduled backups are disabled. Set BACKUP_SCHEDULE_ENABLED=true to enable.');

            return self::SUCCESS;
        }

        $this->info('Starting scheduled backup...');

        $backup = $backupService->createScheduledBackup();

        $this->info(sprintf(
            'Scheduled backup queued (ID: %d, type: %s, status: %s).',
            $backup->id,
            $backup->backup_type,
            $backup->status,
        ));

        return self::SUCCESS;
    }
}
