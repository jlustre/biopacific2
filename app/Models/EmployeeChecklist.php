<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeChecklist extends Model
{
    protected $table = 'employee_checklist';

    protected $fillable = [
        'user_id',
        'item_key',
        'item_label',
        'status',
        'notes',
        'submitted_at',
        'returned_at',
        'returned_by',
        'completed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'returned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
