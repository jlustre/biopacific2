<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceAssessment extends Model
{
    protected $table = 'employee_performance_assessments';

    protected $fillable = [
        'employee_num',
        'assessment_period_id',
        'items',
        'total_score',
        'average_score',
        'overall_rating',
        'assessment_date',
        'review_dt',
        'acknowledge_dt',
        'assessed_by',
        'comments',
        'finalized',
    ];

    protected $casts = [
        'items' => 'array',
        'total_score' => 'integer',
        'average_score' => 'decimal:2',
        'finalized' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $assessment): void {
            if (! $assessment->isDirty('items') || ! is_array($assessment->items)) {
                return;
            }

            $assessment->items = self::itemsForPersistence($assessment->items);
        });
    }

    /**
     * Rating only — audit fields live on employee_assessment_item_entries and the assessment row.
     *
     * @param  array<string, mixed>  $items
     * @return array<string, array{rating: string}>
     */
    public static function itemsForPersistence(array $items): array
    {
        $normalized = [];

        foreach ($items as $key => $item) {
            $rating = self::itemRating($item);

            if ($rating !== null) {
                $normalized[$key] = ['rating' => $rating];
            }
        }

        return $normalized;
    }

    public static function itemRating(mixed $item): ?string
    {
        if (is_string($item) && in_array($item, ['E', 'S', 'U', 'N'], true)) {
            return $item;
        }

        if (is_array($item) && ! empty($item['rating'])) {
            return (string) $item['rating'];
        }

        return null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function itemsArray(): array
    {
        $items = $this->items;

        if (is_array($items)) {
            return $items;
        }

        if (! is_string($items) || $items === '') {
            return [];
        }

        $decoded = json_decode($items, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function period()
    {
        return $this->belongsTo(EmployeeAssessmentPeriod::class, 'assessment_period_id');
    }
}
