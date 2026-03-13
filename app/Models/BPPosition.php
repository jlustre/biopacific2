<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPPosition extends Model
{
    protected $table = 'bp_positions';
    protected $primaryKey = 'position_id';
    public $timestamps = true;

    protected $fillable = [
        'position_code',
        'position_title',
        'dept_code',
        'description',
        'is_active',
    ];
}
