<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsEphi;

class Inquiry extends Model
{
    use HasFactory, EncryptsEphi;

    protected $fillable = [
        'facility_id',
        'recipient',
        'full_name',
        'phone',
        'email',
        'message',
        'consent',
        'no_phi',
        'access_token',
        'token_expires_at',
        'is_viewed',
        'viewed_at',
        'viewed_by',
        'is_encrypted',
        'encryption_key_hint',
    ];

    protected $casts = [
        'consent' => 'boolean',
        'no_phi' => 'boolean',
        'is_viewed' => 'boolean',
        'is_encrypted' => 'boolean',
        'token_expires_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the facility associated with the inquiry.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Generate secure access URL for this inquiry
     */
    public function getSecureAccessUrl(): string
    {
        return route('secure.inquiry.view', ['token' => $this->access_token]);
    }

    /**
     * Check if this inquiry is accessible (not expired)
     */
    public function isAccessible(): bool
    {
        return $this->token_expires_at === null || $this->token_expires_at->isFuture();
    }
}