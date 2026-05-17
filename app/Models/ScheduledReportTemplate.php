<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReportTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'report_id',
        'facility_id',
        'parameters',
        'notify_roles',
        'notify_emails',
        'start_at',
        'end_at',
        'notifications_enabled',
        'report_format',
        'pdf_orientation',
        'cron_expression',
        'status',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'notify_roles' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'notifications_enabled' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
