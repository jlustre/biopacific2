<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityLeadershipAssignment extends Model
{
    protected $fillable = [
        'facility_id',
        'role_key',
        'role_label',
        'name',
        'sort_order',
        'is_custom',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
