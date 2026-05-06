<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceItem extends Model
{
    protected $table = 'employee_performance_items';

    protected $attributes = [
        'position_ids' => '["global"]',
    ];

    protected $fillable = [
        'section',
        'item',
        'position_ids',
        'order',
    ];

    protected $casts = [
        'position_ids' => 'array',
    ];
}
