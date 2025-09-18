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
            if ($key === 'tls_hsts' && config('app.url')) {
                $passed = $passed || str_starts_with(config('app.url'), 'https://');
            }

            if ($key === 'npp_page') {
                $passed = $passed || !empty($facility['npp_url'] ?? null);
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
