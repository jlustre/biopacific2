<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebmasterContact extends Model
{
    protected $fillable = [
        'name', 'email', 'subject', 'message', 'urgent', 'screenshots', 'is_read'
    ];

    protected $casts = [
        'screenshots' => 'array',
        'urgent' => 'boolean',
        'is_read' => 'boolean',
    ];
}
