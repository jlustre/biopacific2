<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebContent extends Model
{
     protected $fillable = [
        'facility_id',
        'layout_template',
        'is_active',
        'sections',
        'variances',
    ];

    protected $casts = [
        'sections' => 'array',
        'variances' => 'array',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
