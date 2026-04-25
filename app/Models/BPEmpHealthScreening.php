<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmpHealthScreening extends Model
{
    protected $table = 'bp_emp_health_screenings';
    protected $primaryKey = 'screening_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'screening_type',
        'screening_date',
        'expiry_date',
        'result',
        'provider',
        'notes',
    ];
}
