<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department_id',
        'reports_to_position_id',
        'supervisor_role',
        'legacy_position_id',
        'position_code',
        'is_active',
    ];

    protected $casts = [
        'supervisor_role' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeSupervisorRoles($query)
    {
        return $query->where('supervisor_role', true);
    }

    public function getPositionIdAttribute(): int
    {
        return $this->id;
    }

    public function getPositionTitleAttribute($value): string
    {
        return $this->title;
    }

    public function getIsActiveAttribute($value): bool
    {
        return (bool) ($value ?? true);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reportsToPosition()
    {
        return $this->belongsTo(self::class, 'reports_to_position_id');
    }

    public function portalRoleMapping()
    {
        return $this->hasOne(PositionPortalRoleMapping::class);
    }

    public function directReports()
    {
        return $this->hasMany(self::class, 'reports_to_position_id');
    }

    public function jobDescriptions()
    {
        return $this->hasMany(JobDescription::class);
    }

    public function jobDescriptionTemplates()
    {
        return $this->hasMany(JobDescriptionTemplate::class);
    }

    public function uploadTypeRequirements()
    {
        return $this->hasMany(PositionUploadTypeRequirement::class);
    }

    public function requiredUploadTypes()
    {
        return $this->belongsToMany(UploadType::class, 'position_upload_type_requirements')
            ->withPivot(['is_required'])
            ->withTimestamps();
    }
}
