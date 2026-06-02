<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfileRecognition extends Model
{
    protected $fillable = [
        'user_id',
        'icon',
        'label',
        'kind',
        'recognized_on',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'recognized_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
