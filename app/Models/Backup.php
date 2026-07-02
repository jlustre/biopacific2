<?php

namespace App\Models;

use App\Support\Backup\BackupStatus;
use App\Support\Backup\BackupType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    protected $fillable = [
        'backup_name',
        'backup_type',
        'file_path',
        'file_size',
        'included_tables',
        'included_sections',
        'metadata',
        'status',
        'error_message',
        'created_by',
        'restored_by',
        'restored_at',
        'pre_restore_backup_id',
        'notes',
    ];

    protected $casts = [
        'included_tables' => 'array',
        'included_sections' => 'array',
        'metadata' => 'array',
        'file_size' => 'integer',
        'restored_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    public function preRestoreBackup(): BelongsTo
    {
        return $this->belongsTo(self::class, 'pre_restore_backup_id');
    }

    public function typeLabel(): string
    {
        return BackupType::labels()[$this->backup_type] ?? ucfirst((string) $this->backup_type);
    }

    public function statusLabel(): string
    {
        return BackupStatus::labels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function formattedFileSize(): string
    {
        return self::formatBytes((int) $this->file_size);
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }

    public function disk(): string
    {
        return (string) ($this->metadata['storage_disk'] ?? config('backup.disk', 'local'));
    }

    public function destinationKey(): string
    {
        return (string) ($this->metadata['destination'] ?? 'local');
    }

    public function destinationLabel(): string
    {
        if (($this->metadata['destination'] ?? '') === 'custom' && filled($this->metadata['custom_path'] ?? null)) {
            return 'Custom folder: ' . $this->metadata['custom_path'];
        }

        return (string) (($this->metadata['manifest']['destination_label'] ?? null)
            ?: app(\App\Services\Backup\BackupDestinationResolver::class)->label($this->destinationKey()));
    }

    public function registerStorageDisk(): void
    {
        if ($customPath = $this->metadata['custom_path'] ?? null) {
            app(\App\Services\Backup\BackupCustomPathService::class)->registerDisk($customPath);
        }
    }

    public function fileExists(): bool
    {
        $this->registerStorageDisk();

        if ($this->file_path && Storage::disk($this->disk())->exists($this->file_path)) {
            return true;
        }

        return $this->remoteFileExists();
    }

    public function remoteDisk(): ?string
    {
        $disk = $this->metadata['remote_disk'] ?? null;

        return is_string($disk) && $disk !== '' ? $disk : null;
    }

    public function remotePath(): ?string
    {
        $path = $this->metadata['remote_path'] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function remoteFileExists(): bool
    {
        $path = $this->remotePath();
        $disk = $this->remoteDisk();

        return $path && $disk && Storage::disk($disk)->exists($path);
    }

    public function absolutePath(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        $this->registerStorageDisk();

        return Storage::disk($this->disk())->path($this->file_path);
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, [BackupStatus::PENDING, BackupStatus::PROCESSING], true);
    }

    public function canDownload(): bool
    {
        return $this->status === BackupStatus::COMPLETED && $this->fileExists();
    }

    public function canRestore(): bool
    {
        return in_array($this->status, [BackupStatus::COMPLETED, BackupStatus::RESTORED], true)
            && $this->fileExists();
    }
}
