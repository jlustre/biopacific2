<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class GalleryImage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'facility_id',
        'title',
        'description',
        'image_url',
        'category',
        'order',
        'is_featured',
        'is_active'
    ];

    protected $casts = [
        'order' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];
}
