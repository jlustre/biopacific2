<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id', 'title', 'description', 'detailed_description', 'department', 'employment_type', 'posted_at', 'expires_at', 'active'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
