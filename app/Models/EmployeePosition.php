<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeePosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'position_title',
        'department',
        'job_description_template_id',
        'notes',
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
