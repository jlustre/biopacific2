<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'facility_id',
        'user_id',
        'pre_employment_application_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'created_by',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preEmploymentApplication(): BelongsTo
    {
        return $this->belongsTo(PreEmploymentApplication::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
