<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['title', 'description', 'department_id', 'supervisor_role'];

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
