<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Position extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department_id',
        'supervisor_role',
        'legacy_position_id',
        'position_code',
        'position_title',
        'dept_code',
        'has_supervisor_role',
        'is_active',
    ];

    protected $casts = [
        'supervisor_role' => 'boolean',
        'has_supervisor_role' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeSupervisorRoles($query)
    {
        $column = Schema::hasColumn($this->getTable(), 'has_supervisor_role')
            ? 'has_supervisor_role'
            : 'supervisor_role';

        return $query->where($column, true);
    }

    public function getPositionIdAttribute(): int
    {
        return $this->id;
    }

    public function getPositionTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getHasSupervisorRoleAttribute($value): bool
    {
        return (bool) ($value ?? $this->supervisor_role);
    }

    public function getIsActiveAttribute($value): bool
    {
        return (bool) ($value ?? true);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobDescriptions()
    {
        return $this->hasMany(JobDescription::class);
    }

    public function jobDescriptionTemplates()
    {
        return $this->hasMany(JobDescriptionTemplate::class);
    }
}
