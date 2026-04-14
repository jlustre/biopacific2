<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledReportRun extends Model
{
    protected $fillable = [
        'scheduled_report_id',
        'executed_at',
        'result_path',
        'result_json',
        'status',
        'error_message',
    ];

    protected $casts = [
        'result_json' => 'array',
    ];

    public function scheduledReport()
    {
        return $this->belongsTo(ScheduledReport::class);
    }
}
