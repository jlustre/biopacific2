<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HiringActivityLog extends Model
{
    protected $fillable = [
        'facility_id',
        'pre_employment_application_id',
        'performed_by',
        'recipient_id',
        'activity_type',
        'form_type',
        'description',
        'notes',
        'status_from',
        'status_to',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the facility associated with this activity
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the pre-employment application
     */
    public function preEmploymentApplication(): BelongsTo
    {
        return $this->belongsTo(PreEmploymentApplication::class);
    }

    /**
     * Get the user who performed the action
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get the recipient (applicant)
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}

