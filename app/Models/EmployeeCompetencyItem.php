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
        'order' => 'integer',
    ];

    public function scopeApplicableToPosition($query, ?int $positionId)
    {
        if (! $positionId) {
            return $query;
        }

        return $query->where(function ($subquery) use ($positionId) {
            $subquery->whereNull('position_ids')
                ->orWhereJsonContains('position_ids', 'global')
                ->orWhereJsonContains('position_ids', $positionId)
                ->orWhereJsonContains('position_ids', (string) $positionId);
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
            abort(404, 'Competency not found.');
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
