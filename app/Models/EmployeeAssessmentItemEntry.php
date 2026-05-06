<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAssessmentItemEntry extends Model
{
    protected $table = 'employee_assessment_item_entries';

    protected $fillable = [
        'employee_num',
        'assessment_period_id',
        'assessment_type',
        'item_key',
        'item_label',
        'source_item_id',
        'rating',
        'assessment_date',
        'assessed_by',
        'comments',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'revoked_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }
}