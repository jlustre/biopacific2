<?php

namespace App\Models;

use App\Services\WebmasterContactService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebmasterContact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'urgent',
        'screenshots',
        'is_read',
        'status',
        'resolved_at',
        'facility_id',
        'category',
        'source',
        'user_id',
    ];

    protected $casts = [
        'screenshots' => 'array',
        'urgent' => 'boolean',
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WebmasterContactComment::class)->orderBy('created_at');
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isOpenForMemberUpdates(): bool
    {
        return ! $this->isResolved();
    }

    public function ownedByUser(?User $user): bool
    {
        return $user !== null
            && $this->user_id !== null
            && (int) $this->user_id === (int) $user->id;
    }

    public function markUnreadForAdmin(): void
    {
        if ($this->is_read) {
            $this->forceFill(['is_read' => false])->save();
        }
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            WebmasterContactService::CATEGORY_ENHANCEMENT => 'Wish list / enhancement',
            default => 'Issue or error',
        };
    }

    public function sourceLabel(): string
    {
        return match ($this->source) {
            WebmasterContactService::SOURCE_MEMBER_PORTAL => 'Member portal',
            default => 'Facility website',
        };
    }

    public function isEnhancement(): bool
    {
        return $this->category === WebmasterContactService::CATEGORY_ENHANCEMENT;
    }
}
