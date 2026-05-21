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
}
