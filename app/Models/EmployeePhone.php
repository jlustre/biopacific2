<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeePhone extends Model
{
    use HasFactory;

    protected $table = 'bp_emp_phones';
    protected $primaryKey = 'phone_id';
    public $timestamps = true;

    protected $fillable = [
        'employee_num',
        'phone_number',
        'phone_type',
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
