<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_opening_id', 'first_name', 'last_name', 'email', 'phone', 'cover_letter', 'resume_path', 'consent', 'status'
    ];

    public function jobOpening()
    {
        return $this->belongsTo(JobOpening::class);
    }
}
