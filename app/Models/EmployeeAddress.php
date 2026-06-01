<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeAddress extends Model
{
    use HasFactory;

    protected $table = 'bp_emp_addresses';
    protected $primaryKey = 'address_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'address_type',
        'effdt',
        'effseq',
        'is_primary',
    ];

    protected $casts = [
        'effdt' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_num', 'employee_num');
    }
}
