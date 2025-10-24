<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'facility_id',
        'category',
        'email',
    ];

    /**
     * Get the facility that owns the email recipient.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}