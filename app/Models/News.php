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
        'status'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'boolean'
    ];

    public function facilities()
    {

        return $this->belongsToMany(Facility::class, 'facility_news');
    }
}
