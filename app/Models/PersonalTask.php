<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalTask extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    /** @var list<string> */
    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'created_by',
        'assigned_to',
        'title',
        'description',
        'action_url',
        'action_label',
        'priority',
        'status',
        'due_at',
        'completed_at',
        'completed_by',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_COMPLETED], true);
    }

    public function awaitsCreatorConfirmation(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && (int) $this->created_by !== (int) $this->assigned_to;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Open',
            self::STATUS_COMPLETED => $this->awaitsCreatorConfirmation() ? 'Awaiting confirmation' : 'Completed',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function priorityLabel(): string
    {
        return ucfirst((string) $this->priority);
    }

    /**
     * @param  Builder<PersonalTask>  $query
     * @return Builder<PersonalTask>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $nested) use ($user) {
            $nested->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        });
    }

    /**
     * Tasks that belong on My Tasks for this user.
     *
     * Action-routed handoffs (training open/review, document verify/correct, etc.)
     * only appear for the assignee. Creators still see manual tasks they own and
     * tasks awaiting their confirmation.
     *
     * @param  Builder<PersonalTask>  $query
     * @return Builder<PersonalTask>
     */
    public function scopeListedForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $nested) use ($user) {
            $nested->where('assigned_to', $user->id)
                ->orWhere(function (Builder $created) use ($user) {
                    $created->where('created_by', $user->id)
                        ->where(function (Builder $owned) {
                            $owned->where(function (Builder $manual) {
                                $manual->whereNull('action_url')
                                    ->orWhere('action_url', '');
                            })->orWhere('status', self::STATUS_COMPLETED);
                        });
                });
        });
    }

    /**
     * Open tasks currently assigned to the user (pending action).
     */
    public static function assignedOpenCountForUser(User $user): int
    {
        return static::query()
            ->where('assigned_to', $user->id)
            ->where('status', self::STATUS_PENDING)
            ->count();
    }
}
