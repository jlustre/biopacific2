<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'user_id',
        'upload_type_id',
        'file_path',
        'original_filename',
        'file_size',
        'uploaded_at',
        'expires_at',
        'effective_start_date',
        'effective_end_date',
        'comments',
    ];

    
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
