<?php

namespace Database\Seeders\Support;

use App\Models\BPEmployee;
use App\Models\BPEmpAddress;
use App\Models\BPEmpJobData;
use App\Models\BPEmpPhone;
use App\Models\BPEmpTaxData;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedsUserEmployeeRecords
{
    private const DEFAULT_EFFDT = '2022-01-01';

    /**
     * Ensure a user has a linked bp_employees row plus address, phone, job data, and tax data.
     *
     * @param  array{
     *     employee_num?: string,
     *     facility_id?: int|null,
     *     position_id?: int|null,
    *     position_title?: string,
     *     position_index?: int,
     *     dept_id?: int|null,
     *     original_hire_dt?: string,
     *     gender?: string,
    *     dob?: string,
    *     middle_name?: string|null,
     *     is_union?: bool,
     *     first_name?: string,
     *     last_name?: string,
     *     ssn?: string,
     * }  $options
     */
    public static function seed(User $user, array $options = []): BPEmployee
    {
        $employee = $user->resolvedBpEmployee();
        $employeeNum = $options['employee_num']
            ?? $employee?->employee_num
            ?? ('USR' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT));

        if (!$employee) {
            $employee = BPEmployee::query()->where('employee_num', $employeeNum)->first();
        }

        [$firstName, $lastName] = static::parseName(
            (string) $user->name,
            $options['first_name'] ?? null,
            $options['last_name'] ?? null,
        );

        $payload = [
            'employee_num' => $employeeNum,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $options['middle_name'] ?? null,
            'email' => $user->email,
            'gender' => $options['gender'] ?? 'N',
            'dob' => $options['dob'] ?? '1990-01-01',
            'original_hire_dt' => $options['original_hire_dt'] ?? self::DEFAULT_EFFDT,
        ];

        if (User::bpEmployeesTableHasUserId()) {
            $payload['user_id'] = $user->id;
        }

        if ($employee) {
            $employee->fill($payload);

            if (!$employee->ssn) {
                $employee->ssn = static::uniqueSsn($options['ssn'] ?? null, $user->id, $employeeNum);
            }

            $employee->save();
        } else {
            $payload['ssn'] = static::uniqueSsn($options['ssn'] ?? null, $user->id, $employeeNum);
            $employee = BPEmployee::create($payload);
        }

        static::seedAddress($employeeNum, $options);
        static::seedPhone($employeeNum, $options);
        static::seedJobData($user, $employeeNum, $options);
        static::seedTaxData($employeeNum, $options);

        return $employee->fresh();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function parseName(string $name, ?string $firstName, ?string $lastName): array
    {
        if ($firstName && $lastName) {
            return [$firstName, $lastName];
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) === 0) {
            return ['User', 'Account'];
        }

        if (count($parts) === 1) {
            return [$parts[0], 'User'];
        }

        return [$parts[0], implode(' ', array_slice($parts, 1))];
    }

    private static function uniqueSsn(?string $preferred, int $userId, string $employeeNum): string
    {
        if ($preferred && !BPEmployee::query()->where('ssn', $preferred)->exists()) {
            return $preferred;
        }

        $candidates = [
            sprintf('%03d-%02d-%04d', $userId % 1000, intdiv($userId, 1000) % 100, 1000 + ($userId * 37 % 9000)),
            sprintf('%03d-%02d-%04d', ($userId * 3) % 1000, ($userId * 11) % 100, 2000 + (crc32($employeeNum) % 8000)),
        ];

        foreach ($candidates as $candidate) {
            if (!BPEmployee::query()->where('ssn', $candidate)->exists()) {
                return $candidate;
            }
        }

        do {
            $candidate = sprintf(
                '%03d-%02d-%04d',
                random_int(100, 999),
                random_int(10, 99),
                random_int(1000, 9999)
            );
        } while (BPEmployee::query()->where('ssn', $candidate)->exists());

        return $candidate;
    }

    private static function seedAddress(string $employeeNum, array $options): void
    {
        $exists = BPEmpAddress::query()
            ->where('employee_num', $employeeNum)
            ->where('effdt', self::DEFAULT_EFFDT)
            ->where('effseq', 0)
            ->exists();

        if ($exists) {
            return;
        }

        BPEmpAddress::create([
            'employee_num' => $employeeNum,
            'address_type' => 'H',
            'effdt' => self::DEFAULT_EFFDT,
            'effseq' => 0,
            'address1' => $options['address1'] ?? '123 Main St',
            'address2' => null,
            'city' => $options['city'] ?? 'Los Angeles',
            'state' => $options['state'] ?? 'CA',
            'zip' => $options['zip'] ?? '90001',
            'country' => 'USA',
            'is_primary' => BPEmpAddress::PRIMARY_YES,
        ]);
    }

    private static function seedPhone(string $employeeNum, array $options): void
    {
        $exists = BPEmpPhone::query()
            ->where('employee_num', $employeeNum)
            ->where('is_primary', BPEmpPhone::PRIMARY_YES)
            ->exists();

        if ($exists) {
            return;
        }

        $suffix = str_pad((string) (abs(crc32($employeeNum)) % 10000), 4, '0', STR_PAD_LEFT);

        BPEmpPhone::create([
            'employee_num' => $employeeNum,
            'phone_type' => 'M',
            'effdt' => self::DEFAULT_EFFDT,
            'effseq' => 0,
            'phone_number' => $options['phone_number'] ?? ('555-010-' . $suffix),
            'is_primary' => BPEmpPhone::PRIMARY_YES,
        ]);
    }

    private static function seedJobData(User $user, string $employeeNum, array $options): void
    {
        $exists = BPEmpJobData::query()
            ->where('employee_num', $employeeNum)
            ->where('effdt', self::DEFAULT_EFFDT)
            ->where('effseq', 0)
            ->exists();

        if ($exists) {
            return;
        }

        $position = static::resolvePosition($options);
        $facilityId = $options['facility_id'] ?? $user->facility_id;
        $deptId = $options['dept_id'] ?? $position?->department_id;
        $isUnion = (bool) ($options['is_union'] ?? false);

        BPEmpJobData::create([
            'employee_num' => $employeeNum,
            'effdt' => self::DEFAULT_EFFDT,
            'effseq' => 0,
            'facility_id' => $facilityId,
            'dept_id' => $deptId,
            'position_id' => $position?->id,
            'reports_to' => static::resolveReportsTo($position),
            'reg_temp' => 'r',
            'full_part_time' => $options['full_part_time'] ?? 'ft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'bargaining_unit_id' => $isUnion ? static::defaultBargainingUnitId() : null,
            'union_seniority_dt' => $isUnion ? self::DEFAULT_EFFDT : null,
        ]);
    }

    private static function seedTaxData(string $employeeNum, array $options): void
    {
        $exists = BPEmpTaxData::query()
            ->where('employee_num', $employeeNum)
            ->where('effdt', self::DEFAULT_EFFDT)
            ->where('effseq', 0)
            ->exists();

        if ($exists) {
            return;
        }

        BPEmpTaxData::create([
            'employee_num' => $employeeNum,
            'effdt' => self::DEFAULT_EFFDT,
            'effseq' => 0,
            'fed_tax_data' => $options['fed_tax_data'] ?? '1',
            'fed_withholding_allowance' => $options['fed_withholding_allowance'] ?? 0,
            'state_tax_data' => $options['state_tax_data'] ?? '1',
            'state_withholding_allowance1' => $options['state_withholding_allowance1'] ?? 0,
            'resident' => $options['resident'] ?? 'Y',
            'local_withholding_allowance' => 0,
            'resident_state' => $options['resident_state'] ?? 'CA',
        ]);
    }

    private static function resolvePosition(array $options): ?Position
    {
        if (!Schema::hasTable('positions')) {
            return null;
        }

        if (!empty($options['position_title'])) {
            $position = Position::query()->where('title', $options['position_title'])->first();
            if ($position) {
                return $position;
            }
        }

        if (!empty($options['position_id']) && Schema::hasTable('positions')) {
            return Position::query()->find($options['position_id']);
        }

        $titles = [
            'Certified Nursing Assistant',
            'Licensed Vocational Nurse',
            'Registered Nurse',
            'Administrator',
            'Director of Nursing',
            'Receptionist',
        ];

        $index = (int) ($options['position_index'] ?? 0);
        $title = $titles[$index % count($titles)];

        $position = Position::query()->where('title', $title)->first();

        return $position ?? Position::query()->orderBy('id')->first();
    }

    private static function resolveReportsTo(?Position $position): ?int
    {
        if (!$position?->department_id || !Schema::hasTable('positions')) {
            return null;
        }

        $supervisor = Position::query()
            ->where('department_id', $position->department_id)
            ->where('id', '!=', $position->id)
            ->orderBy('id')
            ->value('id');

        return $supervisor ? (int) $supervisor : null;
    }

    private static function defaultBargainingUnitId(): ?int
    {
        if (!Schema::hasTable('bp_bargaining_units')) {
            return null;
        }

        return DB::table('bp_bargaining_units')->orderBy('unit_id')->value('unit_id');
    }
}
