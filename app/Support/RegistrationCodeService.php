<?php

namespace App\Support;

use App\Models\BPEmployee;
use App\Models\JobApplication;
use App\Models\RegistrationCode;
use App\Models\User;
use App\Support\Rbac\Permissions as RbacPermissions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationCodeService
{
    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const CODE_SUFFIX_LENGTH = 6;

    private const DEFAULT_EXPIRY_DAYS = 14;

    public function canGenerateCodes(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($actor->can(RbacPermissions::CREATE_REGISTRATION_INVITATIONS)) {
            return true;
        }

        return $this->actorHoldsDepartmentHeadPosition($actor);
    }

    /**
     * @return list<string>
     */
    public function departmentHeadPositionTitles(): array
    {
        $keys = config('member-portal.department_head_leadership_keys', []);
        $titles = [];

        foreach (config('facility-dashboard.leadership_roles', []) as $roleDefinition) {
            $key = $roleDefinition['key'] ?? '';
            if ($key === '' || ! in_array($key, $keys, true)) {
                continue;
            }

            foreach ($roleDefinition['position_titles'] ?? [] as $positionTitle) {
                $normalized = Str::lower(trim((string) $positionTitle));
                if ($normalized !== '') {
                    $titles[] = $normalized;
                }
            }
        }

        return array_values(array_unique($titles));
    }

    public function actorHoldsDepartmentHeadPosition(User $actor): bool
    {
        $employee = method_exists($actor, 'resolvedBpEmployee') ? $actor->resolvedBpEmployee() : null;

        if (! $employee) {
            return false;
        }

        $employee->loadMissing('currentAssignment.position');
        $title = Str::lower(trim((string) ($employee->currentAssignment?->position?->title ?? '')));

        if ($title === '') {
            return false;
        }

        return in_array($title, $this->departmentHeadPositionTitles(), true);
    }

    public function employeeHasPortalUser(BPEmployee $employee): bool
    {
        if ($employee->user_id) {
            return true;
        }

        if (filled($employee->email)) {
            return User::query()->where('email', $employee->email)->exists();
        }

        return false;
    }

    public function applicantHasPortalUser(JobApplication $application): bool
    {
        if ($application->user_id) {
            return true;
        }

        if (filled($application->email)) {
            return User::query()->where('email', $application->email)->exists();
        }

        return false;
    }

    public function issueApplicantRegistrationCode(JobApplication $application, User $generator): RegistrationCode
    {
        $code = $this->generateForApplicant($application, $generator);

        \Illuminate\Support\Facades\Mail::to($code->email)->send(
            new \App\Mail\ApplicantRegistrationInviteMail($code, $application)
        );

        return $code;
    }

    public function generateForEmployee(BPEmployee $employee, User $generator): RegistrationCode
    {
        if ($this->employeeHasPortalUser($employee)) {
            throw ValidationException::withMessages([
                'employee' => 'This employee already has a portal account.',
            ]);
        }

        if (! filled($employee->email)) {
            throw ValidationException::withMessages([
                'email' => 'Add a work email to the employee record before generating a registration code.',
            ]);
        }

        $this->revokeActiveCodesForEmployee($employee->employee_num);

        return RegistrationCode::create([
            'code' => $this->generateUniqueCode(RegistrationCode::TYPE_EMPLOYEE),
            'type' => RegistrationCode::TYPE_EMPLOYEE,
            'employee_num' => $employee->employee_num,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => strtolower(trim((string) $employee->email)),
            'ssn_last4' => $this->extractSsnLast4($employee->ssn),
            'generated_by' => $generator->id,
            'expires_at' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);
    }

    public function generateForApplicant(JobApplication $application, ?User $generator = null): RegistrationCode
    {
        if ($application->user_id) {
            throw ValidationException::withMessages([
                'application' => 'This applicant already has a portal account.',
            ]);
        }

        if ($this->applicantHasPortalUser($application)) {
            throw ValidationException::withMessages([
                'application' => 'This applicant already has a portal account.',
            ]);
        }

        if (! filled($application->email)) {
            throw ValidationException::withMessages([
                'email' => 'The applicant record must have an email address.',
            ]);
        }

        RegistrationCode::query()
            ->where('job_application_id', $application->id)
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);

        return RegistrationCode::create([
            'code' => $this->generateUniqueCode(RegistrationCode::TYPE_APPLICANT),
            'type' => RegistrationCode::TYPE_APPLICANT,
            'job_application_id' => $application->id,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'email' => strtolower(trim((string) $application->email)),
            'ssn_last4' => null,
            'generated_by' => $generator?->id,
            'expires_at' => now()->addDays(self::DEFAULT_EXPIRY_DAYS),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function validateForRegistration(
        string $registrationCode,
        string $name,
        string $email,
        string $identityVerification,
    ): RegistrationCode {
        $normalizedCode = strtoupper(trim($registrationCode));

        $record = RegistrationCode::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $record || ! $record->isUsable()) {
            throw ValidationException::withMessages([
                'registrationCode' => 'The registration code is invalid or has already been used.',
            ]);
        }

        if (! $this->nameMatches($record, $name)) {
            throw ValidationException::withMessages([
                'name' => 'The name does not match the registration code on file.',
            ]);
        }

        if (strtolower(trim($email)) !== strtolower(trim($record->email))) {
            throw ValidationException::withMessages([
                'email' => 'Use the work email address that received the registration invitation.',
            ]);
        }

        if ($record->isEmployeeCode() && ! $this->identityMatches($record, $identityVerification)) {
            throw ValidationException::withMessages([
                'identityVerification' => 'Enter your employee number or the last 4 digits of your Social Security number exactly as they appear on file.',
            ]);
        }

        return $record;
    }

    public function markAsUsed(RegistrationCode $code, User $user): void
    {
        $code->forceFill([
            'used_at' => now(),
            'used_by_user_id' => $user->id,
        ])->save();
    }

    public function linkRegisteredUser(RegistrationCode $code, User $user): void
    {
        if ($code->isEmployeeCode() && filled($code->employee_num)) {
            $employee = BPEmployee::query()
                ->where('employee_num', $code->employee_num)
                ->with('currentAssignment.position')
                ->first();

            if ($employee) {
                $payload = [
                    'email' => $user->email,
                ];

                if (User::bpEmployeesTableHasUserId()) {
                    $payload['user_id'] = $user->id;
                }

                $employee->fill($payload)->save();
            }

            if ($employee) {
                app(EmployeePortalRoleService::class)->assignRegistrationRole($user, $employee);
            } elseif (! $user->hasRole('regular-user') && \Spatie\Permission\Models\Role::query()->where('name', 'regular-user')->exists()) {
                $user->assignRole('regular-user');
            }

            if ($employee?->currentAssignment?->facility_id && ! $user->facility_id) {
                $user->facility_id = $employee->currentAssignment->facility_id;
                $user->save();
            }

            return;
        }

        if ($code->job_application_id) {
            $application = JobApplication::query()->find($code->job_application_id);

            if ($application && ! $application->user_id) {
                $application->user_id = $user->id;
                $application->save();
            }
        }
    }

    public function registrationUrl(RegistrationCode $code): string
    {
        return route('register', ['code' => $code->code], absolute: true);
    }

    private function revokeActiveCodesForEmployee(string $employeeNum): void
    {
        RegistrationCode::query()
            ->where('employee_num', $employeeNum)
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);
    }

    private function generateUniqueCode(string $type): string
    {
        $prefix = $type === RegistrationCode::TYPE_EMPLOYEE ? 'E-' : 'T-';

        do {
            $suffix = collect(range(1, self::CODE_SUFFIX_LENGTH))
                ->map(fn () => self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)])
                ->implode('');
            $code = $prefix . $suffix;
        } while (RegistrationCode::query()->where('code', $code)->exists());

        return $code;
    }

    private function extractSsnLast4(?string $ssn): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $ssn);

        if (strlen($digits) < 4) {
            return null;
        }

        return substr($digits, -4);
    }

    private function nameMatches(RegistrationCode $record, string $name): bool
    {
        $normalizedInput = $this->normalizeName($name);
        $normalizedRecord = $this->normalizeName($record->fullName());

        if ($normalizedInput === $normalizedRecord) {
            return true;
        }

        $inputParts = preg_split('/\s+/', $normalizedInput) ?: [];
        $recordParts = preg_split('/\s+/', $normalizedRecord) ?: [];

        if (count($inputParts) < 2 || count($recordParts) < 2) {
            return false;
        }

        $inputFirst = $inputParts[0];
        $inputLast = $inputParts[count($inputParts) - 1];
        $recordFirst = $recordParts[0];
        $recordLast = $recordParts[count($recordParts) - 1];

        return $inputFirst === $recordFirst && $inputLast === $recordLast;
    }

    private function normalizeName(string $name): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($name));

        return Str::lower((string) $collapsed);
    }

    private function identityMatches(RegistrationCode $record, string $identityVerification): bool
    {
        $input = trim($identityVerification);

        if ($input === '') {
            return false;
        }

        if ($record->employee_num && strcasecmp($record->employee_num, $input) === 0) {
            return true;
        }

        $inputDigits = preg_replace('/\D/', '', $input);

        if ($record->ssn_last4 && strlen($inputDigits) >= 4) {
            return substr($inputDigits, -4) === $record->ssn_last4;
        }

        return false;
    }
}
