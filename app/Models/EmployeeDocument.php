<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $table = 'employee_documents';
    protected $fillable = [
        'employee_num',
        'file_name',
        'original_filename',
        'mime_type',
        'file_size',
        'description',
        'effective_start_date',
        'effective_end_date',
        'expires_at',
        'comments',
        'uploaded_by',
    ];

    public function employee()
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
