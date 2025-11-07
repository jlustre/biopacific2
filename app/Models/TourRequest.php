<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsEphi;

class TourRequest extends Model
{
    use HasFactory, EncryptsEphi;

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
        'access_token',
        'expires_at',
        'viewed_at',
        'audit_log'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'audit_log' => 'array',
        'interests' => 'array',
        'consent' => 'boolean'
    ];

    /**
     * Define which fields contain ePHI and should be encrypted
     */
    protected $ephiFields = [
        'full_name',
        'phone', 
        'email',
        'message'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Generate secure access URL for this tour request
     */
    public function getSecureAccessUrl(): string
    {
        return route('secure.tour-request', ['token' => $this->access_token]);
    }

    /**
     * Check if this tour request is accessible (not expired)
     */
    public function isAccessible(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Generate a secure access token for this tour request.
     * Override the trait method to use correct column names.
     */
    public function generateSecureAccessToken(): string
    {
        $token = \Illuminate\Support\Str::random(64);
        $this->setAttribute('access_token', $token);
        // Note: expires_at is set separately by the caller
        // Don't auto-save here to allow setting expires_at in the same update
        
        return $token;
    }
}