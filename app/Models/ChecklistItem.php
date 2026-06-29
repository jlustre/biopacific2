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
        'is_required',
        'is_license_or_certification',
    ];

    protected $casts = [
        'position_ids' => 'array',
        'isExpiring' => 'boolean',
        'is_required' => 'boolean',
        'is_license_or_certification' => 'boolean',
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

    public function uploadType()
    {
        return $this->hasOne(UploadType::class);
    }
}
