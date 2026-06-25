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

    public function fillForJobApplication(EmailTemplate $template, JobApplication $jobApplication): array
    {
        return $this->fill($template, $this->valuesForJobApplication($jobApplication));
    }

    public function fillForRegistrationCode(EmailTemplate $template, RegistrationCode $registrationCode): array
    {
        return $this->fill($template, $this->valuesForRegistrationCode($registrationCode));
    }

    public function fillForNewUser(EmailTemplate $template, User $user, ?RegistrationCode $registrationCode = null): array
    {
        return $this->fill($template, $this->valuesForNewUser($user, $registrationCode));
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
