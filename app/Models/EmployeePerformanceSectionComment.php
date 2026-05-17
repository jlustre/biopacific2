<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceSectionComment extends Model
{
    protected $table = 'employee_performance_section_comments';

    protected $fillable = [
        'employee_num',
        'assessment_period_id',
        'doc_type_id',
        'comment',
    ];

    /**
     * Persist trimmed comment text, or remove the row when empty.
     *
     * @return array{record: ?self, action: 'created'|'updated'|'deleted'|'noop'}
     */
    public static function syncForSection(
        string $employeeNum,
        int $assessmentPeriodId,
        int $docTypeId,
        ?string $comment
    ): array {
        $lookup = [
            'employee_num' => $employeeNum,
            'assessment_period_id' => $assessmentPeriodId,
            'doc_type_id' => $docTypeId,
        ];

        $trimmed = trim((string) ($comment ?? ''));
        $existing = static::query()->where($lookup)->first();

        if ($trimmed === '') {
            if ($existing) {
                $existing->delete();

                return ['record' => null, 'action' => 'deleted'];
            }

            return ['record' => null, 'action' => 'noop'];
        }

        $record = static::updateOrCreate($lookup, ['comment' => $trimmed]);

        return [
            'record' => $record,
            'action' => $existing ? 'updated' : 'created',
        ];
    }
}
