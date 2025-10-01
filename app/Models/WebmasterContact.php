<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WebmasterContact extends Model
{
    protected $fillable = [
        'name', 'email', 'subject', 'message', 'urgent', 'screenshots', 'is_read', 'status', 'resolved_at', 'facility_id'
    ];

    protected $casts = [
        'screenshots' => 'array',
        'urgent' => 'boolean',
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(\App\Models\Facility::class);
    }
}
