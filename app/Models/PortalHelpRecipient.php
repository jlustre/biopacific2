<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PortalHelpRecipient extends Model
{
    public const RESPONSIBILITY_PRIMARY = 'primary';

    public const RESPONSIBILITY_SECONDARY = 'secondary';

    protected $fillable = [
        'channel',
        'responsibility',
        'user_id',
        'name',
        'email',
        'is_active',
        'on_vacation',
        'vacation_starts_at',
        'vacation_ends_at',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'on_vacation' => 'boolean',
        'vacation_starts_at' => 'date',
        'vacation_ends_at' => 'date',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPrimary(): bool
    {
        return $this->responsibility === self::RESPONSIBILITY_PRIMARY;
    }

    public function isSecondary(): bool
    {
        return $this->responsibility === self::RESPONSIBILITY_SECONDARY;
    }

    /**
     * Away when manually marked on vacation, or within a scheduled vacation window.
     */
    public function isAway(?Carbon $at = null): bool
    {
        if ($this->on_vacation) {
            return true;
        }

        $at = $at ?? now();
        $start = $this->vacation_starts_at;
        $end = $this->vacation_ends_at;

        if (! $start && ! $end) {
            return false;
        }

        if ($start && $at->lt($start->copy()->startOfDay())) {
            return false;
        }

        if ($end && $at->gt($end->copy()->endOfDay())) {
            return false;
        }

        return (bool) ($start || $end);
    }

    public function resolvedEmail(): ?string
    {
        $email = $this->user?->email ?: $this->email;

        return filled($email) ? strtolower(trim((string) $email)) : null;
    }

    public function displayName(): string
    {
        return $this->name
            ?: ($this->user?->name ?? null)
            ?: ($this->resolvedEmail() ?? 'Recipient');
    }

    public function channelLabel(): string
    {
        return config('portal-help.types.'.$this->channel, ucfirst(str_replace('_', ' ', (string) $this->channel)));
    }

    public function responsibilityLabel(): string
    {
        return $this->isPrimary() ? 'Primary' : 'Secondary';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw("CASE responsibility WHEN 'primary' THEN 0 ELSE 1 END")
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
