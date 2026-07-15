<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportLog extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_AWAITING_CONFIRMATION = 'awaiting_confirmation';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REVERTED = 'reverted';

    protected $fillable = [
        'user_id',
        'facility_id',
        'import_mapping_preset_id',
        'source',
        'source_filename',
        'import_file_path',
        'status',
        'total_rows',
        'processed_rows',
        'imported_rows',
        'skipped_rows',
        'failed_rows',
        'tables_affected',
        'summary',
        'error_message',
        'started_at',
        'completed_at',
        'cancel_requested_at',
        'cancelled_at',
        'can_revert',
        'reverted_at',
        'reverted_by',
    ];

    protected $casts = [
        'tables_affected' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancel_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'can_revert' => 'boolean',
        'reverted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revertedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(ImportMappingPreset::class, 'import_mapping_preset_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(ImportLogChange::class);
    }

    public function isReverted(): bool
    {
        return $this->status === self::STATUS_REVERTED || $this->reverted_at !== null;
    }

    public function canBeReverted(): bool
    {
        if ($this->isReverted()) {
            return false;
        }

        if (!in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_PARTIAL, self::STATUS_CANCELLED], true)) {
            return false;
        }

        $changeCount = $this->changes_count ?? $this->changes()->count();

        return $this->can_revert || $changeCount > 0;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_AWAITING_CONFIRMATION => 'Awaiting confirmation',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REVERTED => 'Reverted',
            self::STATUS_RUNNING => 'Running',
            default => ucfirst($this->status),
        };
    }
}
