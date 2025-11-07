<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Facility;
use App\Models\SecureAccessLog;
use Illuminate\Support\Facades\Log;

class SecureInquiryController extends Controller
{
    /**
     * Display a secure inquiry via token
     */
    public function view(string $token)
    {
        try {
            // Find inquiry by access token
            $inquiry = Inquiry::where('access_token', $token)->first();

            if (!$inquiry) {
                $this->logAccessAttempt('inquiry', null, $token, 'invalid_token');
                
                return view('secure.error', [
                    'title' => 'Inquiry Not Found',
                    'message' => 'The secure inquiry you\'re trying to access does not exist or has been removed.',
                    'type' => 'inquiry'
                ]);
            }

            // Check if access token has expired
            if (!$inquiry->isValidAccessToken($token)) {
                $this->logAccessAttempt('inquiry', $inquiry->id, $token, 'token_expired', $inquiry->facility_id);
                
                return view('secure.error', [
                    'title' => 'Access Expired',
                    'message' => 'This secure link has expired for security reasons. Links are valid for 72 hours after creation.',
                    'type' => 'inquiry'
                ]);
            }

            // Check if staff verification is required
            $sessionKey = "staff_verified_inquiry_{$inquiry->id}";
            
            if (!session($sessionKey)) {
                return view('secure.verify-staff', [
                    'inquiry' => $inquiry,
                    'token' => $token,
                    'facility' => $inquiry->facility,
                    'expires_at' => $inquiry->token_expires_at->format('M j, Y \a\t g:i A'),
                    'type' => 'inquiry'
                ]);
            }

            // Log successful access
            $this->logAccessAttempt('inquiry', $inquiry->id, $token, 'successful', $inquiry->facility_id, session('verified_staff_email'));

            return view('secure.inquiry-view', [
                'inquiry' => $inquiry,
                'facility' => $inquiry->facility,
                'viewedBy' => session('verified_staff_email'),
                'accessedAt' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Error accessing secure inquiry', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);

            return view('secure.error', [
                'title' => 'Access Error',
                'message' => 'An error occurred while trying to access this secure information.',
                'type' => 'inquiry'
            ]);
        }
    }

    /**
     * Verify staff credentials for secure access
     */
    public function verifyStaff(Request $request, string $token)
    {
        $request->validate([
            'staff_email' => 'required|email',
            'access_reason' => 'required|string|in:follow_up,processing,record_keeping,compliance_review,response_preparation',
            'hipaa_agreement' => 'required|accepted'
        ]);

        try {
            // Find inquiry by token
            $inquiry = Inquiry::where('access_token', $token)
                ->where('token_expires_at', '>', now())
                ->firstOrFail();

            // Verify staff email is authorized for this facility
            if (!$this->isAuthorizedStaffEmail($request->staff_email, $inquiry->facility)) {
                $this->logAccessAttempt('inquiry', $inquiry->id, $token, 'staff_verification_failed', $inquiry->facility_id);
                
                return back()->withErrors([
                    'staff_email' => 'This email address is not authorized for access to this facility\'s information.'
                ]);
            }

            // Set session verification
            $sessionKey = "staff_verified_inquiry_{$inquiry->id}";
            session([
                $sessionKey => true,
                'verified_staff_email' => $request->staff_email,
                'access_reason' => $request->access_reason,
                'verification_time' => now()
            ]);

            // Log successful verification
            $this->logAccessAttempt('inquiry', $inquiry->id, $token, 'staff_verified', $inquiry->facility_id, $request->staff_email);

            return redirect()->route('secure.inquiry.view', $token);

        } catch (\Exception $e) {
            Log::error('Staff verification failed for inquiry', [
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
                'access_token' => substr($token, 0, 16),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'staff_email' => $staffEmail,
                'access_status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log access attempt', [
                'error' => $e->getMessage(),
                'token_type' => $tokenType,
                'record_id' => $recordId
            ]);
        }
    }
}
