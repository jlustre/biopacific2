<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpCompensation extends Model
{
    protected $table = 'bp_emp_compensation';
    protected $primaryKey = 'comp_id';
    public $timestamps = true;

    protected $fillable = [
        'emp_id',
        'effdt',
        'effseq',
        'base_rate',
        'rate_type',
        'fte',
        'pay_frequency',
        'reason_code',
    ];
}
