<?php

namespace App\Support;

use App\Mail\WelcomeRegistrationMail;
use App\Models\EmailTemplate;
use App\Models\JobApplication;
use App\Models\RegistrationCode;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailTemplatePlaceholderService
{
    public const EMPLOYEE_REGISTRATION_TEMPLATE = 'registration-account';

    public const WELCOME_REGISTRATION_TEMPLATE = 'welcome-account';

    public const PLACEHOLDERS = [
        '{first_name}',
        '{last_name}',
        '{facility_name}',
        '{job_title}',
        '{application_id}',
        '{employee_num}',
        '{applicant_code}',
        '{pre_employment_link}',
        '{registration_code}',
        '{registration_link}',
        '{registration_expiration}',
        '{verification_link}',
        '{dashboard_link}',
    ];

    public function fill(EmailTemplate $template, array $values): array
    {
        $replacementValues = [];

        foreach (self::PLACEHOLDERS as $placeholder) {
            $key = trim($placeholder, '{}');
            $replacementValues[] = $values[$key] ?? '';
        }

        return [
            str_replace(self::PLACEHOLDERS, $replacementValues, $template->subject),
            str_replace(self::PLACEHOLDERS, $replacementValues, $template->body),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function fillAsHtml(EmailTemplate $template, array $values): array
    {
        [$subject, $body] = $this->fill($template, $values);

        return [$subject, $this->formatBodyAsHtml($body)];
    }

    public function formatBodyAsHtml(string $body): string
    {
        $body = trim($body);

        if ($body === '') {
            return $this->wrapHtmlDocument('');
        }

        if (preg_match('/<html[\s>]/i', $body) || preg_match('/<body[\s>]/i', $body)) {
            return $body;
        }

        if (preg_match('/<(p|div|table|ul|ol|h[1-6])\b/i', $body)) {
            return $this->wrapHtmlDocument($body);
        }

        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $paragraphs = preg_split('/\n\s*\n/', $body) ?: [];
        $htmlParagraphs = [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            if ($this->paragraphIsLinkOnly($paragraph)) {
                $paragraph = $this->styleCtaLink($paragraph);
            }

            $paragraph = nl2br($paragraph, false);
            $htmlParagraphs[] = '<p style="margin: 0 0 16px;">' . $paragraph . '</p>';
        }

        return $this->wrapHtmlDocument(implode("\n", $htmlParagraphs));
    }

    private function wrapHtmlDocument(string $content): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bio-Pacific</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5; margin: 0; padding: 16px;">
' . $content . '
</body>
</html>';
    }

    private function paragraphIsLinkOnly(string $paragraph): bool
    {
        $stripped = trim(strip_tags($paragraph));

        return $stripped !== '' && preg_match('/^<a\s+[^>]+>.*<\/a>$/is', trim($paragraph)) === 1;
    }

    private function styleCtaLink(string $anchorHtml): string
    {
        if (preg_match('/\bstyle\s*=/i', $anchorHtml)) {
            return $anchorHtml;
        }

        return preg_replace(
            '/<a\s+/i',
            '<a style="display:inline-block;padding:10px 16px;background:#0d9488;color:#ffffff;text-decoration:none;border-radius:6px;" ',
            $anchorHtml,
            1
        ) ?? $anchorHtml;
    }

    public function fillForJobApplication(EmailTemplate $template, JobApplication $jobApplication): array
    {
        return $this->fill($template, $this->valuesForJobApplication($jobApplication));
    }

    public function fillForRegistrationCode(EmailTemplate $template, RegistrationCode $registrationCode): array
    {
        return $this->fillAsHtml($template, $this->valuesForRegistrationCode($registrationCode));
    }

    public function fillForNewUser(EmailTemplate $template, User $user, ?RegistrationCode $registrationCode = null): array
    {
        return $this->fillAsHtml($template, $this->valuesForNewUser($user, $registrationCode));
    }

    public function valuesForJobApplication(JobApplication $jobApplication): array
    {
        $applicantCode = $jobApplication->applicant_code ?? '';
        $registrationCodeRecord = $jobApplication->activeRegistrationCode;

        return [
            'first_name' => $jobApplication->first_name ?? '',
            'last_name' => $jobApplication->last_name ?? '',
            'facility_name' => $jobApplication->jobOpening?->facility?->name ?? '',
            'job_title' => $jobApplication->jobOpening?->title ?? '',
            'application_id' => (string) ($jobApplication->id ?? ''),
            'employee_num' => '',
            'applicant_code' => $applicantCode,
            'pre_employment_link' => $applicantCode
                ? route('pre-employment.index', ['code' => $applicantCode])
                : url('/pre-employment'),
            'registration_code' => $registrationCodeRecord?->code ?? '',
            'registration_link' => $registrationCodeRecord
                ? app(RegistrationCodeService::class)->registrationUrl($registrationCodeRecord)
                : '',
            'registration_expiration' => $this->formatExpiration($registrationCodeRecord?->expires_at),
            'verification_link' => '',
            'dashboard_link' => route('dashboard.index', absolute: true),
        ];
    }

    public function valuesForRegistrationCode(RegistrationCode $registrationCode): array
    {
        $facilityName = '';
        $employeeNum = (string) ($registrationCode->employee_num ?? '');

        if ($registrationCode->employee_num) {
            $employee = $registrationCode->employee;
            if ($employee) {
                $employee->loadMissing('currentAssignment.facility');
                $facilityName = $employee->current_facility?->name ?? '';
            }
        }

        return [
            'first_name' => $registrationCode->first_name ?? '',
            'last_name' => $registrationCode->last_name ?? '',
            'facility_name' => $facilityName,
            'job_title' => '',
            'application_id' => '',
            'employee_num' => $employeeNum,
            'applicant_code' => '',
            'pre_employment_link' => '',
            'registration_code' => $registrationCode->code,
            'registration_link' => app(RegistrationCodeService::class)->registrationUrl($registrationCode),
            'registration_expiration' => $this->formatExpiration($registrationCode->expires_at),
            'verification_link' => '',
            'dashboard_link' => route('dashboard.index', absolute: true),
        ];
    }

    public function valuesForNewUser(User $user, ?RegistrationCode $registrationCode = null): array
    {
        $nameParts = $this->splitName($user->name);
        $facilityName = '';
        $employeeNum = '';
        $applicantCode = '';
        $preEmploymentLink = '';
        $jobTitle = '';
        $applicationId = '';

        if ($registrationCode) {
            if ($registrationCode->employee_num) {
                $employee = $registrationCode->employee;
                if ($employee) {
                    $employee->loadMissing('currentAssignment.facility');
                    $facilityName = $employee->current_facility?->name ?? '';
                }
                $employeeNum = (string) $registrationCode->employee_num;
            }

            if ($registrationCode->job_application_id) {
                $application = $registrationCode->jobApplication;
                if ($application) {
                    $application->loadMissing('jobOpening.facility');
                    $facilityName = $application->jobOpening?->facility?->name ?? $facilityName;
                    $jobTitle = $application->jobOpening?->title ?? '';
                    $applicationId = (string) $application->id;
                    $applicantCode = $application->applicant_code ?? '';
                    $preEmploymentLink = $applicantCode
                        ? route('pre-employment.index', ['code' => $applicantCode], absolute: true)
                        : url('/pre-employment');
                }
            }

            if (filled($registrationCode->first_name)) {
                $nameParts['first_name'] = $registrationCode->first_name;
            }

            if (filled($registrationCode->last_name)) {
                $nameParts['last_name'] = $registrationCode->last_name;
            }
        }

        return [
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'facility_name' => $facilityName,
            'job_title' => $jobTitle,
            'application_id' => $applicationId,
            'employee_num' => $employeeNum,
            'applicant_code' => $applicantCode,
            'pre_employment_link' => $preEmploymentLink,
            'registration_code' => $registrationCode?->code ?? '',
            'registration_link' => $registrationCode
                ? app(RegistrationCodeService::class)->registrationUrl($registrationCode)
                : '',
            'registration_expiration' => $this->formatExpiration($registrationCode?->expires_at),
            'verification_link' => $this->verificationUrlFor($user),
            'dashboard_link' => route('dashboard.index', absolute: true),
        ];
    }

    public function employeeRegistrationTemplate(): ?EmailTemplate
    {
        return $this->templateByName(self::EMPLOYEE_REGISTRATION_TEMPLATE);
    }

    public function welcomeRegistrationTemplate(): ?EmailTemplate
    {
        return $this->templateByName(self::WELCOME_REGISTRATION_TEMPLATE);
    }

    public function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    private function templateByName(string $name): ?EmailTemplate
    {
        return EmailTemplate::query()
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array{first_name: string, last_name: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
    }

    private function formatExpiration(mixed $expiresAt): string
    {
        if (! $expiresAt) {
            return '';
        }

        return $expiresAt->timezone(config('app.timezone'))->format('F j, Y g:i A T');
    }
}
