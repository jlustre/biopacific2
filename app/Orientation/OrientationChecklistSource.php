<?php

namespace App\Orientation;

use App\Models\ChecklistItem;

/**
 * PART E orientation rows are created by {@see \Database\Seeders\OrientationChecklistItemsSeeder}.
 * This helper documents that relationship and loads the same data the seeder writes to {@see ChecklistItem}.
 */
final class OrientationChecklistSource
{
    /**
     * Position titles that have a dedicated orientation list in {@see \Database\Seeders\OrientationChecklistItemsSeeder}.
     *
     * @return list<string>
     */
    public static function seededOrientationPositionTitles(): array
    {
        return [
            'Administrator',
            'Director of Nursing',
            'Director of Staff Development',
            'Social Services Director',
        ];
    }

    /**
     * Orientation checklist items for the employee's current position.
     * {@see BPEmpJobData::$job_code_id} references {@see Position::$id} (column name is historical).
     *
     * Excludes CNA-style skills rows (doc_type_id = 5) used elsewhere under PART E.
     *
     * @return \Illuminate\Support\Collection<int, ChecklistItem>
     */
    public static function checklistItemsForPosition(?int $positionId)
    {
        if (! $positionId) {
            return collect();
        }

        return ChecklistItem::query()
            ->where('section', 'PART E')
            ->where(function ($sub) {
                $sub->whereNull('doc_type_id')
                    ->orWhere('doc_type_id', '!=', 5);
            })
            ->applicableToPosition($positionId)
            ->orderBy('order')
            ->get();
    }
}
