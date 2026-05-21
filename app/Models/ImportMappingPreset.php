<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportMappingPreset extends Model
{
    protected $fillable = [
        'user_id',
        'facility_id',
        'name',
        'mappings',
    ];

    protected $casts = [
        'mappings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function isGlobal(): bool
    {
        return (int) $this->facility_id === (int) config('import-mapping.global_facility_id', 99);
    }

    public function mappingsCount(): int
    {
        return is_array($this->mappings) ? count($this->mappings) : 0;
    }
}
