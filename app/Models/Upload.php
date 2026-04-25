<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facility_id',
        'employee_num',
        'user_id',
        'upload_type_id',
        'file_path',
        'original_filename',
        'file_size',
        'uploaded_at',
        'expires_at',
        'effective_start_date',
        'comments',
    ];
    /**
     * Get the employee that owns this upload.
     */
    public function employee()
    {
        return $this->belongsTo(BPEmployee::class, 'employee_num', 'employee_num');
    }

    
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploadType()
    {
        return $this->belongsTo(UploadType::class);
    }

    
}
