<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourRequest;
use App\Models\Facility;
use App\Models\SecureAccessLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuspiciousAccessAlert;

class SecureTourRequestController extends Controller
{
    /**
     * Display a secure tour request via token
     */
    public function view(string $token)
    {
        try {
            // Find tour request by access token
            $tourRequest = TourRequest::where('access_token', $token)->first();

            if (!$tourRequest) {
                $this->logAccessAttempt('tour_request', null, $token, 'invalid_token');
                
                Log::warning('Secure tour request access attempt with invalid token', [
                    'token' => substr($token, 0, 8) . '...',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                return view('secure.error', [
                    'title' => 'Request Not Found',
                    'message' => 'The secure request you\'re trying to access does not exist or has been removed.',
                    'type' => 'tour-request'
                ]);
            }

            // Check if access token has expired
            if (!$tourRequest->isAccessible()) {
                $this->logAccessAttempt('tour_request', $tourRequest->id, $token, 'expired', $tourRequest->facility_id);
                
                Log::warning('Secure tour request access attempt with expired token', [
                    'tour_request_id' => $tourRequest->id,
                    'expired_at' => $tourRequest->expires_at,
                    'ip' => request()->ip()
                ]);
                
                return view('secure.error', [
                    'title' => 'Access Expired',
                    'message' => 'This secure link has expired for security reasons. Links are valid for 72 hours after creation.',
                    'type' => 'tour-request'
                ]);
            }

            // Check for suspicious activity
            $suspiciousActivity = SecureAccessLog::checkSuspiciousActivity('tour_request', $tourRequest->id);
            
            if ($suspiciousActivity['is_suspicious']) {
                Log::warning('Suspicious activity detected for tour request access', [
                    'tour_request_id' => $tourRequest->id,
                    'flags' => $suspiciousActivity['flags'],
                    'ip' => request()->ip()
                ]);
                
                // Could add additional verification here if needed
            }

            // Check if staff verification is required
            $sessionKey = "staff_verified_tour_{$tourRequest->id}";
            
            if (!session($sessionKey)) {
                return view('secure.verify-staff', [
                    'tourRequest' => $tourRequest,
                    'token' => $token,
                    'facility' => $tourRequest->facility,
                    'expires_at' => $tourRequest->expires_at->format('M j, Y \a\t g:i A'),
                    'type' => 'tour-request'
                ]);
            }

            // Log successful access
            $this->logAccessAttempt('tour_request', $tourRequest->id, $token, 'success', $tourRequest->facility_id, session('verified_staff_email'));

            // Update tour request audit log
            $auditLog = $tourRequest->audit_log ?? [];
            $auditLog[] = [
                'action' => 'viewed',
                'timestamp' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'staff_email' => session('verified_staff_email'),
                'access_reason' => session('access_reason')
            ];

            $tourRequest->update([
                'viewed_at' => now(),
                'audit_log' => $auditLog
            ]);

            // Get facility information
            $facility = $tourRequest->facility;

            Log::info('Authorized secure tour request access', [
                'tour_request_id' => $tourRequest->id,
                'facility_id' => $facility->id,
                'staff_email' => session('verified_staff_email'),
                'ip_address' => request()->ip()
            ]);

            return view('secure.tour-request', compact('tourRequest', 'facility'));

        } catch (\Exception $e) {
            Log::error('Error accessing secure tour request', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);

            return view('secure.error', [
                'title' => 'Access Error',
                'message' => 'An error occurred while trying to access this secure information.',
                'type' => 'tour-request'
            ]);
        }
    }

    /**
     * Log access attempt for audit trail (AJAX endpoint)
     */
    public function logAccess(Request $request, string $token)
    {
        try {
            $tourRequest = TourRequest::where('access_token', $token)->first();

            if (!$tourRequest) {
                return response()->json(['error' => 'Tour request not found'], 404);
            }

            // Add to audit log
            $auditLog = $tourRequest->audit_log ?? [];
            $auditLog[] = [
                'action' => 'page_loaded',
                'timestamp' => $request->input('access_time', now()->toISOString()),
                'ip_address' => request()->ip(),
                'user_agent' => $request->input('user_agent', request()->userAgent())
            ];

            $tourRequest->update(['audit_log' => $auditLog]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error logging tour request access', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Logging failed'], 500);
        }
    }

    /**
     * Verify staff credentials for secure access
     */
    public function verifyStaff(Request $request, string $token)
    {
        $request->validate([
            'staff_email' => 'required|email',
            'access_reason' => 'required|string|in:follow_up,scheduling,processing,record_keeping,compliance_review',
            'hipaa_agreement' => 'required|accepted'
        ]);

        try {
            // Find tour request by token
            $tourRequest = TourRequest::where('access_token', $token)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            // Verify staff email is authorized for this facility
            if (!$this->isAuthorizedStaffEmail($request->staff_email, $tourRequest->facility)) {
                $this->logAccessAttempt('tour_request', $tourRequest->id, $token, 'unauthorized_email', $tourRequest->facility_id);
                
                return back()->withErrors([
                    'staff_email' => 'This email address is not authorized for access to this facility\'s information.'
                ]);
            }

            // Set session verification
            $sessionKey = "staff_verified_tour_{$tourRequest->id}";
            session([
                $sessionKey => true,
                'verified_staff_email' => $request->staff_email,
                'access_reason' => $request->access_reason,
                'verification_time' => now()
            ]);

            // Log successful verification
            $this->logAccessAttempt('tour_request', $tourRequest->id, $token, 'staff_verified', $tourRequest->facility_id, $request->staff_email);

            Log::info('Staff verification successful for tour request', [
                'tour_request_id' => $tourRequest->id,
                'staff_email' => $request->staff_email,
                'access_reason' => $request->access_reason,
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('secure.tour-request', $token);

        } catch (\Exception $e) {
            Log::error('Staff verification failed', [
                'token' => substr($token, 0, 8) . '...',
                'staff_email' => $request->staff_email,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'staff_email' => 'Verification failed. Please try again or contact your administrator.'
            ]);
        }
    }

    /**
     * Check if staff email is authorized for this facility
     */
    private function isAuthorizedStaffEmail(string $email, Facility $facility): bool
    {
        // Get facility's authorized email domains (could be stored in facility settings)
        $authorizedDomains = $facility->authorized_email_domains ?? [];
        
        // Extract domain from email
        $emailDomain = substr(strrchr($email, "@"), 1);
        
        // Check if email domain is authorized
        if (!empty($authorizedDomains) && !in_array($emailDomain, $authorizedDomains)) {
            return false;
        }

        // Additional checks could include:
        // - Check against employee database
        // - Check against predefined staff email list
        // - Integration with HR systems
        
        // For now, accept facility domain emails and common business domains
        $commonBusinessDomains = ['gmail.com', 'outlook.com', 'yahoo.com'];
        $facilityDomain = parse_url($facility->website ?? '', PHP_URL_HOST);
        
        // Allow if it's the facility domain or a business email (basic check)
        if ($facilityDomain && str_ends_with($email, '@' . $facilityDomain)) {
            return true;
        }

        // For demo purposes, accept any valid email format
        // In production, this should be more restrictive
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Log access attempt to secure access logs
     */
    private function logAccessAttempt(string $tokenType, ?int $recordId, string $token, string $status, ?int $facilityId = null, ?string $staffEmail = null): void
    {
        try {
            SecureAccessLog::logAccess([
                'token_type' => $tokenType,
                'record_id' => $recordId,
                'facility_id' => $facilityId,
                'access_token' => substr($token, 0, 16), // Store partial token for privacy
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'staff_email' => $staffEmail,
                'access_status' => $status,
                'request_headers' => [
                    'referer' => request()->header('referer'),
                    'accept_language' => request()->header('accept-language'),
                    'x_forwarded_for' => request()->header('x-forwarded-for')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log access attempt', [
                'error' => $e->getMessage(),
                'token_type' => $tokenType,
                'record_id' => $recordId
            ]);
        }
    }

    /**
     * Admin dashboard for secure tour requests
     */
    public function adminIndex()
    {
        $tourRequests = TourRequest::with('facility')
            ->whereNotNull('access_token')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.secure-tour-requests.index', compact('tourRequests'));
    }

    /**
     * Regenerate access token for a tour request
     */
    public function regenerateToken(TourRequest $tourRequest)
    {
        try {
            $oldToken = $tourRequest->access_token;
            $newToken = $tourRequest->generateSecureAccessToken();
            
            $tourRequest->update([
                'access_token' => $newToken,
                'expires_at' => now()->addHours(config('app.secure_access_hours', 72)),
                'viewed_at' => null
            ]);

            // Log the token regeneration
            $auditLog = $tourRequest->audit_log ?? [];
            $auditLog[] = [
                'action' => 'token_regenerated',
                'timestamp' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'old_token' => substr($oldToken, 0, 8) . '...',
                'new_token' => substr($newToken, 0, 8) . '...'
            ];

            $tourRequest->update(['audit_log' => $auditLog]);

            Log::info('Tour request access token regenerated', [
                'tour_request_id' => $tourRequest->id,
                'facility_id' => $tourRequest->facility_id
            ]);

            return back()->with('success', 'Access token regenerated successfully.');

        } catch (\Exception $e) {
            Log::error('Error regenerating tour request token', [
                'tour_request_id' => $tourRequest->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to regenerate access token.');
        }
    }
}