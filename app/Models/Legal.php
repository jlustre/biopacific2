<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legal extends Model
{
    protected $table = 'legals';
    protected $fillable = [
        'type',
        'title',
        'content',
        'version',
        'is_active',
        'is_global',
    ];

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_legals');
    }
}
