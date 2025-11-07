<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SecureAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_type',
        'record_id',
        'facility_id',
        'access_token',
        'ip_address',
        'user_agent',
        'staff_email',
        'access_status',
        'request_headers',
        'session_id',
        'access_time',
        'notes'
    ];

    protected $casts = [
        'request_headers' => 'array',
        'access_time' => 'datetime',
    ];

    /**
     * Log a secure access attempt
     */
    public static function logAccess(array $data): self
    {
        return self::create(array_merge($data, [
            'access_time' => now(),
            'session_id' => session()->getId()
        ]));
    }

    /**
     * Check for suspicious access patterns
     */
    public static function checkSuspiciousActivity(string $tokenType, int $recordId): array
    {
        $oneHourAgo = Carbon::now()->subHour();
        
        // Get recent access attempts for this record
        $recentAccesses = self::where('token_type', $tokenType)
            ->where('record_id', $recordId)
            ->where('access_time', '>', $oneHourAgo)
            ->orderBy('access_time', 'desc')
            ->get();

        $suspiciousFlags = [];
        
        // Flag 1: Multiple access attempts from different IPs
        $uniqueIPs = $recentAccesses->pluck('ip_address')->unique();
        if ($uniqueIPs->count() > 3) {
            $suspiciousFlags[] = 'multiple_ips';
        }
        
        // Flag 2: Rapid successive access attempts
        if ($recentAccesses->count() > 5) {
            $suspiciousFlags[] = 'rapid_access';
        }
        
        // Flag 3: Failed authorization attempts
        $failedAttempts = $recentAccesses->where('access_status', 'unauthorized')->count();
        if ($failedAttempts > 2) {
            $suspiciousFlags[] = 'repeated_failures';
        }
        
        // Flag 4: Access from unusual locations (basic check)
        $knownGoodIPs = $recentAccesses->where('access_status', 'success')
            ->where('staff_email', '!=', null)
            ->pluck('ip_address')
            ->unique();
            
        $currentIP = request()->ip();
        if ($knownGoodIPs->isNotEmpty() && !$knownGoodIPs->contains($currentIP)) {
            $suspiciousFlags[] = 'unusual_location';
        }

        return [
            'is_suspicious' => !empty($suspiciousFlags),
            'flags' => $suspiciousFlags,
            'recent_accesses' => $recentAccesses,
            'unique_ips' => $uniqueIPs->count(),
            'failed_attempts' => $failedAttempts
        ];
    }

    /**
     * Get facility relationship
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
