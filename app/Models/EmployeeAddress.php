<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'current_address',
        'city',
        'state',
        'zip_code',
        'county',
        'effective_date',
        'effective_sequence',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
