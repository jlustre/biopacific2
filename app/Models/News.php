<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'content',
        'image',
        'published_at',
        'status',
        'is_global',
        'facility_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'boolean',
        'is_global' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_news');
    }
}
