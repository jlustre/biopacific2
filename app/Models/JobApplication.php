<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsEphi;

class JobApplication extends Model
{
    use HasFactory, EncryptsEphi;

    protected $fillable = [
        'user_id', 'job_opening_id', 'desired_position', 'department', 'employment_type', 'first_name', 'last_name', 'email', 'phone', 'cover_letter', 'resume_path', 'consent', 'status', 'applicant_code', 'access_token', 'expires_at', 'audit_log', 'viewed_at'
    ];

    // Define which fields contain ePHI and should be encrypted
    protected $ephiFields = ['first_name', 'last_name', 'email', 'phone', 'cover_letter'];

    protected $casts = [
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'audit_log' => 'array',
        'consent' => 'boolean',
    ];

    public function jobOpening()
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Override the trait's getEncryptedFields method
     */
    protected function getEncryptedFields(): array
    {
        return $this->ephiFields ?? [];
    }

    /**
     * Override the trait's token generation to use 'expires_at' instead of 'token_expires_at'
     */
    public function generateSecureAccessToken(): string
    {
        $token = \Illuminate\Support\Str::random(64);
        $this->setAttribute('access_token', $token);
        $this->setAttribute('expires_at', now()->addHours(24)); // Token expires in 24 hours
        $this->save();
        return $token;
    }

    /**
     * Override the trait's token validation to use 'expires_at'
     */
    public function isAccessTokenValid(): bool
    {
        return $this->getAttribute('access_token') && 
               $this->getAttribute('expires_at') &&
               now()->lt($this->getAttribute('expires_at'));
    }

    /**
     * Get the access token for this job application
     */
    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    /**
     * Log secure access for audit purposes
     */
    public function logSecureAccess(array $accessData): void
    {
        $currentLog = $this->audit_log ?? [];
        $currentLog[] = $accessData;
        
        $this->update(['audit_log' => $currentLog]);
    }

    /**
     * Mark the job application as viewed
     */
    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }
}
