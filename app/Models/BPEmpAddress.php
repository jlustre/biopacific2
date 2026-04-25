<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpAddress extends Model
{
    protected $table = 'bp_emp_addresses';
    protected $primaryKey = 'address_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'address_type',
        'effdt',
        'effseq',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'is_primary',
    ];
}
