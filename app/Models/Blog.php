<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_global',
        'facility_id',
        'author',
        'status',
        'photo1',
        'photo2',
        'is_active',
        'version',
        'published_at',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'blog_facility');
    }
}
