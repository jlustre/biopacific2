<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'section',
        'doc_type_id',
        'position_ids',
        'order',
        'isExpiring',
    ];

    protected $casts = [
        'position_ids' => 'array',
    ];

    public function scopeApplicableToPosition($query, ?int $positionId)
    {
        if (!$positionId) {
            return $query;
        }

        return $query->where(function ($subquery) use ($positionId) {
            $subquery->whereNull('position_ids')
                ->orWhereJsonContains('position_ids', $positionId);
        });
    }

    public function docType()
    {
        return $this->belongsTo(DocType::class, 'doc_type_id');
    }
}
