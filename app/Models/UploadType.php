<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'requires_expiry',
        'is_license_or_certification',
        'department_ids',
    ];

    protected $casts = [
        'requires_expiry' => 'boolean',
        'is_license_or_certification' => 'boolean',
        'department_ids' => 'array',
    ];

    public $timestamps = false;

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function positionRequirements()
    {
        return $this->hasMany(PositionUploadTypeRequirement::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_upload_type_requirements')
            ->withPivot(['is_required'])
            ->withTimestamps();
    }
}
