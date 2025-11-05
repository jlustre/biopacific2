<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmailMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'category',
        'employee_name',
        'employee_email',
        'position',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    // Scope for active employees
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for primary contacts
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Get effective primary (active primary, or fallback to first active)
    public static function getEffectivePrimary($facilityId, $category)
    {
        // Try to get active primary first
        $activePrimary = self::where('facility_id', $facilityId)
            ->where('category', $category)
            ->primary()
            ->active()
            ->first();

        if ($activePrimary) {
            return $activePrimary;
        }

        // Fallback: Get first active employee for this category
        $fallbackActive = self::where('facility_id', $facilityId)
            ->where('category', $category)
            ->active()
            ->orderBy('created_at')
            ->first();

        return $fallbackActive;
    }

    // Check if there's an inactive primary
    public static function hasInactivePrimary($facilityId, $category)
    {
        return self::where('facility_id', $facilityId)
            ->where('category', $category)
            ->primary()
            ->where('is_active', false)
            ->exists();
    }

    // Get primary status info
    public static function getPrimaryStatus($facilityId, $category)
    {
        $primary = self::where('facility_id', $facilityId)
            ->where('category', $category)
            ->primary()
            ->first();

        if (!$primary) {
            return ['status' => 'no_primary', 'message' => 'No primary contact assigned'];
        }

        if (!$primary->is_active) {
            return [
                'status' => 'primary_inactive',
                'message' => "Primary contact ({$primary->employee_name}) is inactive",
                'inactive_primary' => $primary
            ];
        }

        return [
            'status' => 'active_primary',
            'message' => "Primary contact ({$primary->employee_name}) is active",
            'primary' => $primary
        ];
    }
}
