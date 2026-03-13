<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPDepartment extends Model
{
    protected $table = 'bp_departments';
    protected $primaryKey = 'dept_id';
    public $timestamps = true;

    protected $fillable = [
        'dept_code',
        'dept_name',
        'description',
    ];
}
