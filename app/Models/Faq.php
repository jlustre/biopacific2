<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'facility_id',
        'question',
        'answer',
        'category',
        'icon',
        'is_active',
        'is_featured',
        'is_default',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    // Scope for default FAQs (can be used by multiple facilities)
    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->whereNull('facility_id');
    }

    // Scope for facility-specific FAQs
    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    // Scope for FAQs available to a facility (both default and facility-specific)
    public function scopeAvailableForFacility($query, $facilityId)
    {
        return $query->where(function($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)
              ->orWhere('is_default', true);
        });
    }

    // Check if FAQ is a default/shared FAQ
    public function isDefault()
    {
        return $this->is_default && $this->facility_id === null;
    }

    // Check if FAQ is facility-specific
    public function isFacilitySpecific()
    {
        return !$this->is_default && $this->facility_id !== null;
    }
}
