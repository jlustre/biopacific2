<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportMappingPreset extends Model
{
    protected $fillable = [
        'user_id',
        'facility_id',
        'name',
        'mappings',
    ];

    protected $casts = [
        'mappings' => 'array',
    ];
}
