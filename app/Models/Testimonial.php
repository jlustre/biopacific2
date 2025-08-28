<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Testimonial extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'facility_id',
        'name',
        'relationship',
        'content',
        'rating',
        'image_url',
        'is_featured',
        'is_active'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];
}
