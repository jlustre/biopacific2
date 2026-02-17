<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobDescriptionTemplate extends Model
{
    protected $fillable = [
        'name', 'position_id', 'contents', 'job_descriptions', 'created_by'
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
