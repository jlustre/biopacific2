<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'title',
        'reporting_to',
        'description',
        'job_description_template_id',
        'department',
        'employment_type',
        'posted_at',
        'expires_at',
        'salary_range',
        'salary_unit',
        'active',
        'status',
        'created_by'
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function template()
    {
        return $this->belongsTo(JobDescriptionTemplate::class, 'job_description_template_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
