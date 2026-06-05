<?php

namespace App\Services;

use App\Models\BPEmpAddress;
use App\Models\BPEmpChecklist;
use App\Models\BPEmpCredential;
use App\Models\BPEmpDocument;
use App\Models\BPEmpJobData;
use App\Models\BPEmployee;
use App\Models\EmployeePhone;
use App\Models\Position;
use App\Models\PreEmploymentApplication;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PreEmploymentHireService
{
    /**
     * Copy pre-employment application data into Bio-Pacific employee tables.
     *
     * @param  array{hire_date: string, position_id: int}  $hireData
     */
    public function hire(PreEmploymentApplication $application, array $hireData): BPEmployee
    {
        return DB::transaction(function () use ($application, $hireData) {
            $hireDate = $hireData['hire_date'] ?? now()->toDateString();
            $positionId = (int) ($hireData['position_id'] ?? $application->position_id ?? 0);
            $position = $positionId > 0 ? Position::query()->find($positionId) : null;

            $application->loadMissing('user');
            $user = $application->user;
            $jobApplication = $user?->jobApplications()->with('jobOpening')->latest()->first();
            $facilityId = $jobApplication?->jobOpening?->facility_id;

            $employee = $this->resolveOrCreateEmployee($application, $user, $hireDate);

            $this->copyAddress($employee, $application, $hireDate);
            $this->copyPhone($employee, $application, $hireDate);
            $this->copyJobAssignment($employee, $hireDate, $positionId, $position?->department_id, $facilityId);
            $this->copyCredentials($employee, $application);
            $this->copyUploads($employee, $application->user_id, $hireDate);
            $this->ensureChecklistRecord($employee);

            $application->update([
                'status' => 'hired',
                'hired_at' => now(),
                'hired_date' => $hireDate,
                'position_id' => $positionId > 0 ? $positionId : $application->position_id,
            ]);

            return $employee->fresh();
        });
    }

    protected function resolveOrCreateEmployee(
        PreEmploymentApplication $application,
        $user,
        string $hireDate
    ): BPEmployee {
        $lookup = User::bpEmployeesTableHasUserId()
            ? ['user_id' => $application->user_id]
            : ['email' => $application->email ?? $user?->email];

        $existing = BPEmployee::query()->where($lookup)->first();
        $employeeNum = $existing?->employee_num ?? $this->generateEmployeeNum();

        $attributes = [
            'employee_num' => $employeeNum,
            'first_name' => $application->first_name,
            'middle_name' => $application->middle_name,
            'last_name' => $application->last_name,
            'email' => $application->email ?? $user?->email,
            'original_hire_dt' => $hireDate,
        ];

        if (User::bpEmployeesTableHasUserId()) {
            $attributes['user_id'] = $application->user_id;
        }

        return BPEmployee::query()->updateOrCreate($lookup, $attributes);
    }

    protected function generateEmployeeNum(): string
    {
        $numbers = BPEmployee::query()
            ->whereNotNull('employee_num')
            ->pluck('employee_num')
            ->map(function (string $num) {
                if (preg_match('/(\d+)/', $num, $matches) === 1) {
                    return (int) $matches[1];
                }

                return 0;
            });

        $next = ($numbers->max() ?? 0) + 1;

        return 'EMP' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    protected function copyAddress(BPEmployee $employee, PreEmploymentApplication $application, string $hireDate): void
    {
        if (!filled($application->current_address) && !filled($application->city)) {
            return;
        }

        $exists = BPEmpAddress::query()
            ->where('employee_num', $employee->employee_num)
            ->where('effdt', $hireDate)
            ->where('effseq', 1)
            ->exists();

        if ($exists) {
            return;
        }

        BPEmpAddress::query()->create([
            'employee_num' => $employee->employee_num,
            'address1' => $application->current_address,
            'address2' => null,
            'city' => $application->city,
            'state' => $application->state,
            'zip' => $application->zip_code,
            'country' => 'usa',
            'address_type' => 'H',
            'effdt' => $hireDate,
            'effseq' => 1,
            'is_primary' => BPEmpAddress::PRIMARY_YES,
        ]);
    }

    protected function copyPhone(BPEmployee $employee, PreEmploymentApplication $application, string $hireDate): void
    {
        if (!filled($application->phone_number)) {
            return;
        }

        $exists = EmployeePhone::query()
            ->where('employee_num', $employee->employee_num)
            ->where('effdt', $hireDate)
            ->where('effseq', 1)
            ->exists();

        if ($exists) {
            return;
        }

        EmployeePhone::query()->create([
            'employee_num' => $employee->employee_num,
            'phone_number' => $application->phone_number,
            'phone_type' => 'H',
            'effdt' => $hireDate,
            'effseq' => 1,
            'is_primary' => 'Y',
        ]);
    }

    protected function copyJobAssignment(
        BPEmployee $employee,
        string $hireDate,
        int $positionId,
        ?int $departmentId,
        ?int $facilityId
    ): void {
        if ($positionId <= 0 && !$facilityId) {
            return;
        }

        $exists = BPEmpJobData::query()
            ->where('employee_num', $employee->employee_num)
            ->where('effdt', $hireDate)
            ->where('effseq', 1)
            ->exists();

        if ($exists) {
            return;
        }

        BPEmpJobData::query()->create([
            'employee_num' => $employee->employee_num,
            'effdt' => $hireDate,
            'effseq' => 1,
            'facility_id' => $facilityId,
            'dept_id' => $departmentId,
            'position_id' => $positionId > 0 ? $positionId : null,
            'start_date' => $hireDate,
        ]);
    }

    protected function copyCredentials(BPEmployee $employee, PreEmploymentApplication $application): void
    {
        if (!$application->has_drivers_license || !filled($application->drivers_license_number)) {
            return;
        }

        $exists = BPEmpCredential::query()
            ->where('employee_num', $employee->employee_num)
            ->where('credential_type', "Driver's License")
            ->exists();

        if ($exists) {
            return;
        }

        BPEmpCredential::query()->create([
            'employee_num' => $employee->employee_num,
            'credential_type' => "Driver's License",
            'credential_number' => $application->drivers_license_number,
            'issue_date' => null,
            'expiry_date' => $application->drivers_license_expiration,
            'issuing_authority' => $application->drivers_license_state,
            'status' => 'active',
        ]);
    }

    protected function copyUploads(BPEmployee $employee, ?int $userId, string $hireDate): void
    {
        if (!$userId) {
            return;
        }

        $uploads = Upload::query()
            ->with('uploadType')
            ->where('user_id', $userId)
            ->where(function ($query) use ($employee) {
                $query->whereNull('employee_num')
                    ->orWhere('employee_num', '!=', $employee->employee_num);
            })
            ->get();

        foreach ($uploads as $upload) {
            $upload->employee_num = $employee->employee_num;
            $upload->save();

            $documentType = $upload->uploadType?->name ?? 'Pre-employment document';
            $exists = BPEmpDocument::query()
                ->where('employee_num', $employee->employee_num)
                ->where('file_path', $upload->file_path)
                ->exists();

            if ($exists || !filled($upload->file_path)) {
                continue;
            }

            BPEmpDocument::query()->create([
                'employee_num' => $employee->employee_num,
                'document_type' => $documentType,
                'file_name' => $upload->original_filename ?? basename($upload->file_path),
                'file_path' => $upload->file_path,
                'mime_type' => null,
                'file_size' => $upload->file_size,
                'effdt' => $hireDate,
                'effseq' => 0,
                'comments' => $upload->comments,
            ]);
        }
    }

    protected function ensureChecklistRecord(BPEmployee $employee): void
    {
        BPEmpChecklist::query()->firstOrCreate(
            ['employee_num' => $employee->employee_num],
            ['items' => []]
        );
    }
}
