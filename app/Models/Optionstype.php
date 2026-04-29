<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Optionstype extends Model
{
    protected $table = 'optionstypes';
    protected $fillable = [
        'name',
        'isActive',
    ];
}
