<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'recipient',
        'full_name',
        'phone',
        'email',
        'message',
        'consent',
        'no_phi',
    ];

    /**
     * Get the facility associated with the inquiry.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}