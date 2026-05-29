<?php

namespace App\Models;

use App\Support\PerformanceAppraisalTemplate;
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

    public function scopeApplicableToPosition($query, ?int $positionId, ?string $positionTitle = null)
    {
        if (! $positionTitle && $positionId) {
            $positionTitle = \App\Models\Position::query()->whereKey($positionId)->value('title');
        }

        return $this->scopeApplicableToPositionTitle($query, $positionTitle);
    }

    public function scopeApplicableToPositionTitle($query, ?string $positionTitle)
    {
        $template = PerformanceAppraisalTemplate::templateForPositionTitle($positionTitle);

        if (! $template) {
            return $query->whereRaw('0 = 1');
        }

        $templatePositionIds = PerformanceAppraisalTemplate::positionIdsForTemplate($template);

        if ($templatePositionIds === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function ($subquery) use ($templatePositionIds) {
            foreach ($templatePositionIds as $templatePositionId) {
                $subquery->orWhereJsonContains('position_ids', $templatePositionId);
            }
        });
    }
}
