<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebmasterContactComment extends Model
{
    public const AUTHOR_MEMBER = 'member';

    public const AUTHOR_ADMIN = 'admin';

    protected $fillable = [
        'webmaster_contact_id',
        'user_id',
        'author_type',
        'author_name',
        'body',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WebmasterContact::class, 'webmaster_contact_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromAdmin(): bool
    {
        return $this->author_type === self::AUTHOR_ADMIN;
    }

    public function displayName(): string
    {
        if ($this->author_name) {
            return $this->author_name;
        }

        return $this->user?->name ?? ($this->isFromAdmin() ? 'Webmaster team' : 'Member');
    }
}
