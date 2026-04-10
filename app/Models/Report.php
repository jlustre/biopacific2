<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sql_template',
        'parameters',
        'is_active',
        'is_public',
        'visibility',
        'visible_roles',
        'visible_facilities',
        'category_id',
    ];
    public function category()
    {
        return $this->belongsTo(ReportCategory::class, 'category_id');
    }

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'visible_roles' => 'array',
        'visible_facilities' => 'array',
    ];

        /**
     * Always return visible_roles as a collection
     */
    public function getVisibleRolesCollectionAttribute()
    {
        return collect($this->visible_roles ?? []);
    }

    /**
     * Always return visible_facilities as a collection
     */
    public function getVisibleFacilitiesCollectionAttribute()
    {
        return collect($this->visible_facilities ?? []);
    }
}
