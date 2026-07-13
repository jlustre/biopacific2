<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationCode extends Model
{
    public const TYPE_EMPLOYEE = 'employee';

    public const TYPE_APPLICANT = 'applicant';

    protected $fillable = [
        'code',
        'type',
        'employee_num',
        'job_application_id',
        'first_name',
        'last_name',
        'email',
        'ssn_last4',
        'generated_by',
        'sponsor_user_id',
        'sponsor_name',
        'used_at',
        'used_by_user_id',
        'expires_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_user_id');
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function sponsorDisplayName(): ?string
    {
        $name = trim((string) ($this->sponsor_name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return $this->sponsor?->name ? trim((string) $this->sponsor->name) : null;
    }

    public function hasSponsor(): bool
    {
        return filled($this->sponsorDisplayName());
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function isUsable(): bool
    {
        if ($this->used_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isEmployeeCode(): bool
    {
        return $this->type === self::TYPE_EMPLOYEE;
    }

    public function statusKey(): string
    {
        if ($this->used_at !== null) {
            return 'used';
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return 'expired';
        }

        return 'pending';
    }

    public function statusLabel(): string
    {
        return match ($this->statusKey()) {
            'used' => 'Used',
            'expired' => 'Expired',
            default => 'Pending',
        };
    }
}
