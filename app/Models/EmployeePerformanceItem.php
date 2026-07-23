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
        'order' => 'integer',
    ];

    public function scopeApplicableToPosition($query, ?int $positionId, ?string $positionTitle = null)
    {
        if (! $positionTitle && $positionId) {
            $positionTitle = Position::query()->whereKey($positionId)->value('title');
        }

        return $this->scopeApplicableToPositionTitle($query, $positionTitle, $positionId);
    }

    public function scopeApplicableToPositionTitle($query, ?string $positionTitle, ?int $positionId = null)
    {
        $template = PerformanceAppraisalTemplate::templateForPositionTitle($positionTitle);
        $templatePositionIds = $template
            ? PerformanceAppraisalTemplate::positionIdsForTemplate($template)
            : [];

        return $query->where(function ($subquery) use ($positionId, $templatePositionIds) {
            $subquery->whereNull('position_ids')
                ->orWhereJsonContains('position_ids', 'global');

            if ($positionId) {
                $subquery->orWhereJsonContains('position_ids', $positionId)
                    ->orWhereJsonContains('position_ids', (string) $positionId);
            }

            foreach ($templatePositionIds as $templatePositionId) {
                $subquery->orWhereJsonContains('position_ids', $templatePositionId)
                    ->orWhereJsonContains('position_ids', (string) $templatePositionId);
            }
        });
    }

    public function scopeForSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function appliesToEveryone(): bool
    {
        $ids = array_values(array_filter(
            (array) ($this->position_ids ?? []),
            fn ($id) => $id !== null && $id !== ''
        ));

        return $ids === [] || in_array('global', $ids, true);
    }

    public static function encodeSectionKey(string $section): string
    {
        return rtrim(strtr(base64_encode($section), '+/', '-_'), '=');
    }

    public static function decodeSectionKey(string $key): string
    {
        $b64 = strtr($key, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad > 0) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        $decoded = base64_decode($b64, true);
        if ($decoded === false || $decoded === '') {
            abort(404, 'Performance section not found.');
        }

        return $decoded;
    }

    /**
     * @return list<int|string>
     */
    public static function normalizePositionIds(bool $applyToEveryone, array $positionIds): array
    {
        if ($applyToEveryone || $positionIds === []) {
            return ['global'];
        }

        return array_values(array_unique(array_map('intval', $positionIds)));
    }
}
