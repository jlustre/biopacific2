<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Facility;
use App\Models\SecureAccessLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecureJobApplicationController extends Controller
{
    /**
     * Display the secure job application details using access token
     */
    public function show(Request $request, $token)
    {
        try {
            // Find job application by access token
            $jobApplication = JobApplication::where('access_token', $token)->first();

            if (!$jobApplication) {
                $this->logAccessAttempt('job_application', null, $token, 'invalid_token');
                
                return view('secure.error', [
                    'title' => 'Job Application Not Found',
                    'message' => 'The secure job application you\'re trying to access does not exist or has been removed.',
                    'type' => 'job-application'
                ]);
            }

            // Check if token is expired
            if ($jobApplication->expires_at && Carbon::now()->isAfter($jobApplication->expires_at)) {
                $this->logAccessAttempt('job_application', $jobApplication->id, $token, 'token_expired', $jobApplication->jobOpening->facility_id ?? null);
                
                return view('secure.error', [
                    'title' => 'Access Expired',
                    'message' => 'This secure link has expired for security reasons. Links are valid for 72 hours after creation.',
                    'type' => 'job-application'
                ]);
            }

            // Check for suspicious activity
            $suspiciousActivity = SecureAccessLog::checkSuspiciousActivity('job_application', $jobApplication->id);
            
            if ($suspiciousActivity['is_suspicious']) {
                Log::warning('Suspicious activity detected for job application access', [
                    'job_application_id' => $jobApplication->id,
                    'flags' => $suspiciousActivity['flags'],
                    'ip' => request()->ip()
                ]);
            }

            // Check if staff verification is required
            $sessionKey = "staff_verified_job_application_{$jobApplication->id}";
            
            if (!session($sessionKey)) {
                return view('secure.verify-staff', [
                    'jobApplication' => $jobApplication,
                    'token' => $token,
                    'facility' => $jobApplication->jobOpening->facility ?? null,
                    'expires_at' => $jobApplication->expires_at->format('M j, Y \a\t g:i A'),
                    'type' => 'job-application'
                ]);
            }

            // Log successful access
            $this->logAccessAttempt('job_application', $jobApplication->id, $token, 'successful', $jobApplication->jobOpening->facility_id ?? null, session('verified_staff_email'));

            // Log the access for HIPAA compliance
            $jobApplication->logSecureAccess([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accessed_at' => Carbon::now(),
                'access_type' => 'secure_view',
                'staff_email' => session('verified_staff_email'),
                'access_reason' => session('access_reason')
            ]);

            // Update viewed_at timestamp
            $jobApplication->markAsViewed();

            // Load the job opening relationship
            $jobApplication->load('jobOpening');

            Log::info('Authorized secure job application access', [
                'job_application_id' => $jobApplication->id,
                'facility_id' => $jobApplication->jobOpening->facility_id ?? null,
                'staff_email' => session('verified_staff_email'),
                'ip_address' => request()->ip()
            ]);

            return view('secure.job-application', [
                'jobApplication' => $jobApplication,
                'jobOpening' => $jobApplication->jobOpening,
                'facility' => $jobApplication->jobOpening->facility ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error accessing secure job application', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);

            return view('secure.error', [
                'title' => 'Access Error',
                'message' => 'An error occurred while trying to access this secure information.',
                'type' => 'job-application'
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
            'access_reason' => 'required|string|in:review,hiring_decision,background_check,scheduling,processing',
            'hipaa_agreement' => 'required|accepted'
        ]);

        try {
            // Find job application by token
            $jobApplication = JobApplication::where('access_token', $token)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            // Verify staff email is authorized for this facility
            $facility = $jobApplication->jobOpening->facility ?? null;
            if ($facility && !$this->isAuthorizedStaffEmail($request->staff_email, $facility)) {
                $this->logAccessAttempt('job_application', $jobApplication->id, $token, 'staff_verification_failed', $facility->id);
                
                return back()->withErrors([
                    'staff_email' => 'This email address is not authorized for access to this facility\'s information.'
                ]);
            }

            // Set session verification
            $sessionKey = "staff_verified_job_application_{$jobApplication->id}";
            session([
                $sessionKey => true,
                'verified_staff_email' => $request->staff_email,
                'access_reason' => $request->access_reason,
                'verification_time' => now()
            ]);

            // Log successful verification
            $this->logAccessAttempt('job_application', $jobApplication->id, $token, 'staff_verified', $facility->id ?? null, $request->staff_email);

            Log::info('Staff verification successful for job application', [
                'job_application_id' => $jobApplication->id,
                'staff_email' => $request->staff_email,
                'access_reason' => $request->access_reason,
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('secure.job-application', $token);

        } catch (\Exception $e) {
            Log::error('Staff verification failed for job application', [
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

    /**
     * Download resume file securely
     */
    public function downloadResume(Request $request, string $token)
    {
        try {
            // Find job application by access token
            $jobApplication = JobApplication::where('access_token', $token)->first();

            if (!$jobApplication) {
                $this->logAccessAttempt('job_application', null, $token, 'invalid_token');
                abort(404, 'Job application not found');
            }

            // Check if token is expired
            if ($jobApplication->expires_at && Carbon::now()->isAfter($jobApplication->expires_at)) {
                $this->logAccessAttempt('job_application', $jobApplication->id, $token, 'token_expired', $jobApplication->jobOpening->facility_id ?? null);
                abort(403, 'Access token has expired');
            }

            // Check if staff verification is required and has been completed
            $sessionKey = "staff_verified_job_application_{$jobApplication->id}";
            if (!session($sessionKey)) {
                abort(403, 'Staff verification required');
            }

            // Check if resume file exists
            if (!$jobApplication->resume_path) {
                abort(404, 'Resume file not found');
            }

            $filePath = storage_path('app/public/' . $jobApplication->resume_path);
            
            if (!file_exists($filePath)) {
                Log::error('Resume file not found on disk', [
                    'job_application_id' => $jobApplication->id,
                    'resume_path' => $jobApplication->resume_path,
                    'full_path' => $filePath
                ]);
                abort(404, 'Resume file not found on disk');
            }

            // Log the file access
            $this->logAccessAttempt('job_application', $jobApplication->id, $token, 'resume_downloaded', $jobApplication->jobOpening->facility_id ?? null, session('verified_staff_email'));

            Log::info('Secure resume download', [
                'job_application_id' => $jobApplication->id,
                'staff_email' => session('verified_staff_email'),
                'file_path' => $jobApplication->resume_path,
                'ip_address' => $request->ip()
            ]);

            // Get file info and sanitize filename
            $originalFileName = basename($jobApplication->resume_path);
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);
            $fileSize = filesize($filePath);
            
            // Determine proper MIME type based on file extension
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mimeType = match($extension) {
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                default => 'application/octet-stream'
            };

            // Return file response with comprehensive security headers
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'Referrer-Policy' => 'strict-origin-when-cross-origin',
                'X-Download-Options' => 'noopen',
                'X-Permitted-Cross-Domain-Policies' => 'none'
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading secure resume', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
                'ip_address' => $request->ip()
            ]);
            
                        abort(500, 'Error downloading file');
        }
    }
}

