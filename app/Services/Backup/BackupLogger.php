<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Services\AuditService;

class BackupLogger
{
    public function logCreated(Backup $backup): void
    {
        AuditService::logSecurityEvent(
            'backup_created',
            sprintf('Backup "%s" (%s) created.', $backup->backup_name, $backup->backup_type),
            'medium'
        );
    }

    public function logCompleted(Backup $backup): void
    {
        AuditService::logSecurityEvent(
            'backup_completed',
            sprintf('Backup "%s" completed (%s).', $backup->backup_name, $backup->formattedFileSize()),
            'medium'
        );
    }

    public function logFailed(Backup $backup, string $message): void
    {
        AuditService::logSecurityEvent(
            'backup_failed',
            sprintf('Backup "%s" failed: %s', $backup->backup_name, $message),
            'high'
        );
    }

    public function logDownloaded(Backup $backup): void
    {
        AuditService::logSecurityEvent(
            'backup_downloaded',
            sprintf('Backup "%s" downloaded.', $backup->backup_name),
            'medium'
        );
    }

    public function logDeleted(Backup $backup): void
    {
        AuditService::logSecurityEvent(
            'backup_deleted',
            sprintf('Backup "%s" deleted.', $backup->backup_name),
            'high'
        );
    }

    public function logRestoreStarted(Backup $backup, string $restoreType, ?Backup $preBackup = null): void
    {
        $message = sprintf('Restore started for "%s" as %s.', $backup->backup_name, $restoreType);
        if ($preBackup) {
            $message .= sprintf(' Pre-restore backup #%d created.', $preBackup->id);
        }

        AuditService::logSecurityEvent('backup_restore_started', $message, 'high');
    }

    public function logRestoreCompleted(Backup $backup, string $restoreType): void
    {
        AuditService::logSecurityEvent(
            'backup_restore_completed',
            sprintf('Restore completed for "%s" as %s.', $backup->backup_name, $restoreType),
            'high'
        );
    }

    public function logRestoreFailed(Backup $backup, string $message): void
    {
        AuditService::logSecurityEvent(
            'backup_restore_failed',
            sprintf('Restore failed for "%s": %s', $backup->backup_name, $message),
            'high'
        );
    }
}
