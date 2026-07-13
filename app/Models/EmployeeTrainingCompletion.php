<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTrainingCompletion extends Model
{
    public const PERIOD_KEY_HIRE = 'hire';

    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NA = 'na';

    /** @deprecated Use STATUS_NOT_STARTED */
    public const STATUS_PENDING = self::STATUS_NOT_STARTED;

    protected $table = 'employee_training_completions';

    protected $fillable = [
        'employee_num',
        'employee_training_item_id',
        'period_key',
        'assessment_period_id',
        'status',
        'completed_at',
        'completed_by',
        'notes',
        'started_at',
        'started_by',
        'submitted_at',
        'submitted_by',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'review_task_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'assessment_period_id' => 'integer',
    ];

    public function trainingItem(): BelongsTo
    {
        return $this->belongsTo(EmployeeTrainingItem::class, 'employee_training_item_id');
    }

    public function assessmentPeriod(): BelongsTo
    {
        return $this->belongsTo(EmployeeAssessmentPeriod::class, 'assessment_period_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function startedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function periodKeyFor(?int $assessmentPeriodId, bool $isHiring): string
    {
        if ($isHiring) {
            return self::PERIOD_KEY_HIRE;
        }

        return (string) (int) $assessmentPeriodId;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NOT_STARTED => 'Not started',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_SUBMITTED => 'Submitted for review',
            self::STATUS_REJECTED => 'Rejected — revise',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_NA => 'N/A',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_NA], true);
    }

    public function employeeCanStart(): bool
    {
        return in_array($this->status, [self::STATUS_NOT_STARTED, self::STATUS_REJECTED], true)
            || $this->status === null;
    }

    public function employeeCanSubmit(): bool
    {
        return in_array($this->status, [self::STATUS_IN_PROGRESS, self::STATUS_REJECTED], true);
    }

    public function reviewerCanDecide(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}
