<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLogChange extends Model
{
    public const ACTION_INSERTED = 'inserted';
    public const ACTION_UPDATED = 'updated';

    protected $fillable = [
        'import_log_id',
        'table_name',
        'employee_num',
        'action',
        'record_key',
        'before_data',
        'after_data',
    ];

    protected $casts = [
        'record_key' => 'array',
        'before_data' => 'array',
        'after_data' => 'array',
    ];

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(ImportLog::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function displayData(?array $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $hidden = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        return collect($data)
            ->except($hidden)
            ->map(fn ($value) => $this->formatDisplayValue($value))
            ->all();
    }

    /**
     * @return array<string, array{before: mixed, after: mixed}>
     */
    public function fieldDiffs(): array
    {
        $before = $this->displayData($this->before_data);
        $after = $this->displayData($this->after_data);
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        sort($keys);

        $diffs = [];
        foreach ($keys as $key) {
            $beforeVal = $before[$key] ?? null;
            $afterVal = $after[$key] ?? null;
            if ($beforeVal !== $afterVal) {
                $diffs[$key] = ['before' => $beforeVal, 'after' => $afterVal];
            }
        }

        return $diffs;
    }

    protected function formatDisplayValue(mixed $value): mixed
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }
}
