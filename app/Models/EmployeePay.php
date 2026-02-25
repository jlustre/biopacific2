<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeePay extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'pay_type',
        'amount',
        'pay_frequency',
        'notes',
        'effective_date',
        'effective_sequence',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
