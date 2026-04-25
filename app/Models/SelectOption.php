<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectOption extends Model
{
    protected $table = 'selectoptions';
    protected $fillable = [
        'type_id',
        'sort_order',
        'name',
        'value',
        'isActive',
    ];
}
