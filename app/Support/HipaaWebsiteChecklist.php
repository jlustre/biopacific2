<?php

namespace App\Support;

class HipaaWebsiteChecklist
{
    /**
     * Map of checklist items -> default help text.
     * Keys are stable IDs you can store as booleans in DB.
     */
    public static function items(): array
    {
        return [
            'npp_page'        => ['label' => 'NPP page published & linked in footer', 'help' => 'Add a printable NPP page and footer link.'],
            'tls_hsts'        => ['label' => 'HTTPS + HSTS (no mixed content)', 'help' => 'Force HTTPS; set HSTS; fix mixed content.'],
            'forms_secure'     => ['label' => 'Forms store ePHI securely (no PHI in email)', 'help' => 'Encrypt at rest; notify by secure link.'],
            'baa_vendors'     => ['label' => 'BAA on file for vendors touching ePHI', 'help' => 'Host/DB/mail/chat/logs/backup all covered.'],
            'tracking_controls'=> ['label' => '3rd-party tracking reviewed/limited', 'help' => 'Avoid PHI leakage; prefer self-hosted analytics.'],
            'security_headers' => ['label' => 'Security headers & CSP set', 'help' => 'CSP, Referrer-Policy, X-Frame-Options, etc.'],
            'auth_admin'       => ['label' => 'Admin protected (MFA, RBAC, sessions)', 'help' => 'MFA required; least privilege; timeouts.'],
            'incident_plan'    => ['label' => 'Incident response/breach notification plan', 'help' => 'Written playbook & contacts.'],
            'content_rights'   => ['label' => 'Patient stories/photos authorized', 'help' => 'Signed HIPAA authorizations on file.'],
        ];
    }

    /**
     * Build a view model for a given facility’s flags + dynamic suggestions.
     */
    public static function forFacility(array $facility, array $flags = []): array
    {
        $items = self::items();
        $rows  = [];

        foreach ($items as $key => $meta) {
            $passed = (bool) ($flags[$key] ?? false);

            // optional dynamic default checks (example: TLS check)
            if ($key === 'tls_hsts') {
                // Check if HTTPS is properly configured
                $httpsConfigured = str_starts_with(config('app.url'), 'https://') || 
                                 config('app.force_https') || 
                                 app()->environment('production');
                
                // Check if middleware is loaded
                $middlewareConfigured = class_exists(\App\Http\Middleware\ForceHttps::class) &&
                                      class_exists(\App\Http\Middleware\SecurityHeaders::class);
                
                $passed = $passed || ($httpsConfigured && $middlewareConfigured);
            }

            if ($key === 'npp_page') {
                $passed = $passed || !empty($facility['npp_url'] ?? null);
            }

            if ($key === 'forms_secure') {
                // Check if secure ePHI handling is implemented for Inquiries
                $inquiryModelUsesTrait = in_array('App\Traits\EncryptsEphi', class_uses(\App\Models\Inquiry::class));
                $secureMailExists = class_exists(\App\Mail\SecureContactMail::class);
                $secureControllerExists = class_exists(\App\Http\Controllers\SecureInquiryController::class);
                
                // Check if secure ePHI handling is implemented for Tour Requests
                $tourRequestModelUsesTrait = in_array('App\Traits\EncryptsEphi', class_uses(\App\Models\TourRequest::class));
                $secureTourMailExists = class_exists(\App\Mail\SecureBookATourMail::class);
                $secureTourControllerExists = class_exists(\App\Http\Controllers\SecureTourRequestController::class);
                
                // Check if secure ePHI handling is implemented for Job Applications
                $jobApplicationModelUsesTrait = in_array('App\Traits\EncryptsEphi', class_uses(\App\Models\JobApplication::class));
                $secureJobApplicationMailExists = class_exists(\App\Mail\SecureJobApplicationMail::class);
                $secureJobApplicationControllerExists = class_exists(\App\Http\Controllers\SecureJobApplicationController::class);
                
                // Check if encryption is configured properly
                $encryptionConfigured = config('app.force_ephi_encryption') !== null &&
                                       !empty(config('app.key'));
                
                // Check if database has secure fields for all models
                $hasInquirySecureFields = \Illuminate\Support\Facades\Schema::hasColumn('inquiries', 'access_token') &&
                                         \Illuminate\Support\Facades\Schema::hasColumn('inquiries', 'is_encrypted');
                
                $hasTourRequestSecureFields = \Illuminate\Support\Facades\Schema::hasColumn('tour_requests', 'access_token') &&
                                             \Illuminate\Support\Facades\Schema::hasColumn('tour_requests', 'expires_at');
                
                $hasJobApplicationSecureFields = \Illuminate\Support\Facades\Schema::hasColumn('job_applications', 'access_token') &&
                                               \Illuminate\Support\Facades\Schema::hasColumn('job_applications', 'expires_at');
                
                // All forms must be secure: Contact Forms, Book a Tour, and Job Applications
                $contactFormsSecure = $inquiryModelUsesTrait && $secureMailExists && 
                                     $secureControllerExists && $hasInquirySecureFields;
                
                $bookATourSecure = $tourRequestModelUsesTrait && $secureTourMailExists && 
                                  $secureTourControllerExists && $hasTourRequestSecureFields;
                
                $jobApplicationsSecure = $jobApplicationModelUsesTrait && $secureJobApplicationMailExists && 
                                       $secureJobApplicationControllerExists && $hasJobApplicationSecureFields;
                
                $passed = $passed || ($contactFormsSecure && $bookATourSecure && $jobApplicationsSecure && $encryptionConfigured);
            }

            $rows[] = [
                'key'    => $key,
                'label'  => $meta['label'],
                'help'   => $meta['help'],
                'passed' => $passed,
            ];
        }

        return $rows;
    }
}
