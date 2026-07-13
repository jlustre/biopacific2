<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalHelpRequest extends Model
{
    public const TYPE_HR = 'hr_inquiry';

    public const TYPE_SUPPORT = 'support';

    protected $fillable = [
        'user_id',
        'facility_id',
        'type',
        'category',
        'priority',
        'name',
        'email',
        'phone',
        'employee_num',
        'subject',
        'message',
        'preferred_contact',
        'best_time_to_reach',
        'steps_to_reproduce',
        'attachments',
        'no_phi_confirmed',
        'status',
        'is_read',
        'resolved_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'no_phi_confirmed' => 'boolean',
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function isHrInquiry(): bool
    {
        return $this->type === self::TYPE_HR;
    }

    public function isSupportRequest(): bool
    {
        return $this->type === self::TYPE_SUPPORT;
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function typeLabel(): string
    {
        return config('portal-help.types.' . $this->type, ucfirst(str_replace('_', ' ', $this->type)));
    }

    public function categoryLabel(): string
    {
        $hr = config('portal-help.hr_categories', []);
        $support = config('portal-help.support_categories', []);

        return $hr[$this->category]['label']
            ?? $support[$this->category]['label']
            ?? ucfirst(str_replace('_', ' ', (string) $this->category));
    }

    public function categoryIcon(): string
    {
        $hr = config('portal-help.hr_categories', []);
        $support = config('portal-help.support_categories', []);

        return $hr[$this->category]['icon']
            ?? $support[$this->category]['icon']
            ?? 'fa-circle-question';
    }

    public function referenceCode(): string
    {
        return 'PHR-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function markUnreadForAdmin(): void
    {
        if ($this->is_read) {
            $this->forceFill(['is_read' => false])->save();
        }
    }
}
