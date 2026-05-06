<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCompetencyItem extends Model
{
    protected $table = 'employee_competency_items';

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

    public function scopeApplicableToPosition($query, ?int $positionId)
    {
        if (!$positionId) {
            return $query;
        }

        return $query->where(function ($subquery) use ($positionId) {
            $subquery->whereNull('position_ids')
                ->orWhereJsonContains('position_ids', 'global')
                ->orWhereJsonContains('position_ids', $positionId);
        });
    }
}