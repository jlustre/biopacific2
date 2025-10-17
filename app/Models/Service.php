<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Service extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'short_description',
        'is_global',
        'detailed_description',
        'icon',
        'image',
        'order',
        'is_featured',
        'is_active',
        'features', // new
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array', // new
    ];

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_service');
    }
}
