<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    protected $fillable = [
        'name',
        'report_id',
        'parameters',
        'cron_expression',
        'next_run_at',
        'last_run_at',
        'status',
        'created_by',
        'notify_roles',
        'notify_emails',
        'start_at',
        'end_at',
        'notifications_enabled',
        'report_format',
        'pdf_orientation', // P=Portrait, L=Landscape
    ];

    protected $casts = [
        'parameters' => 'array',
        'notify_roles' => 'array',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'notifications_enabled' => 'boolean',
        'report_format' => 'string',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs()
    {
        return $this->hasMany(ScheduledReportRun::class);
    }
}
