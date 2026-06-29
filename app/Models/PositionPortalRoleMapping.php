<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionPortalRoleMapping extends Model
{
    protected $fillable = [
        'position_id',
        'role_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
