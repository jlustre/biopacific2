<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpPhone extends Model
{
    protected $table = 'bp_emp_phones';
    protected $primaryKey = 'phone_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'phone_type',
        'phone_number',
        'is_primary',
    ];
}
