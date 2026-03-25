<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityArbitrationDocument extends Model
{
    protected $fillable = [
        'facility_id',
        'template_path',
        'template_type',
        'original_name',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
