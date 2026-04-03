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
    ];

    public $timestamps = false;

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }
}
