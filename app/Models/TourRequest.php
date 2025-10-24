<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'recipient',
        'full_name',
        'relationship',
        'phone',
        'email',
        'preferred_date',
        'preferred_time',
        'interests',
        'message',
        'consent',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}