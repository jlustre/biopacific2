<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('current_facility')) {
                $builder->where('facility_id', app('current_facility')->id);
            }
        });

        static::creating(function ($model) {
            if (app()->bound('current_facility')) {
                $model->facility_id = app('current_facility')->id;
            }
        });
    }

    public function facility()
    {
        return $this->belongsTo(\App\Models\Facility::class);
    }

    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }
}
