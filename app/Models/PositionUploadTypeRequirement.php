<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionUploadTypeRequirement extends Model
{
    protected $fillable = [
        'position_id',
        'upload_type_id',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function uploadType()
    {
        return $this->belongsTo(UploadType::class);
    }
}
