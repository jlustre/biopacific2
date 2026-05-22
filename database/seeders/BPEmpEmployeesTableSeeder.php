<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BPEmpEmployeesTableSeeder extends Seeder
{
    private const FACILITY_14_POSITION_IDS = [11, 18, 16, 12, 26, 29, 2, 34, 6, 4, 22, 13, 36, 27, 15];

    private const FACILITY_17_POSITION_IDS = [18, 11, 16, 12, 36, 29, 26, 34, 22, 6, 4, 13, 14, 25, 30];

    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $employees = [];
        for ($i = 1; $i <= 20; $i++) {
            $empId = $this->employeeNum($i);
            if (DB::table('bp_employees')->where('employee_num', $empId)->exists()) {
                continue;
            }
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $employees[] = [
                'employee_num' => $empId,
                'ssn' => $faker->unique()->numerify('###-##-####'),
                'first_name' => $firstName,
                'middle_name' => null,
                'last_name' => $lastName,
                'email' => $faker->unique()->safeEmail(),
                'gender' => $faker->randomElement(['M', 'F', 'O', 'N']),
                'original_hire_dt' => $this->originalHireDtForIndex($i),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($employees !== []) {
            DB::table('bp_employees')->insert($employees);
        }

        $this->seedFacilityEmployees($faker);
        $this->backfillOriginalHireDates();
    }

    private function seedFacilityEmployees(\Faker\Generator $faker): void
    {
        $positionsById = DB::table('positions')
            ->select('id', 'department_id')
            ->whereIn('id', array_unique(array_merge(self::FACILITY_14_POSITION_IDS, self::FACILITY_17_POSITION_IDS)))
            ->get()
            ->keyBy('id');

        $supervisorPositions = DB::table('positions')
            ->get()
            ->groupBy('department_id');

        $newEmployees = [];
        $newAssignments = [];

        for ($i = 21; $i <= 50; $i++) {
            $empId = $this->employeeNum($i);
            $employeeExists = DB::table('bp_employees')->where('employee_num', $empId)->exists();

            $facilityId = $i <= 35 ? 14 : 17;
            $positionIds = $facilityId === 14
                ? self::FACILITY_14_POSITION_IDS
                : self::FACILITY_17_POSITION_IDS;
            $positionIndex = ($i - 21) % count($positionIds);
            $jobCodeId = $positionIds[$positionIndex];
            $position = $positionsById[$jobCodeId] ?? null;
            $deptId = $position?->department_id;

            if (!$employeeExists) {
                $firstName = $faker->firstName();
                $lastName = $faker->lastName();

                $newEmployees[] = [
                    'employee_num' => $empId,
                    'ssn' => $faker->unique()->numerify('###-##-####'),
                    'first_name' => $firstName,
                    'middle_name' => null,
                    'last_name' => $lastName,
                    'email' => $faker->unique()->safeEmail(),
                    'gender' => $faker->randomElement(['M', 'F', 'O', 'N']),
                    'original_hire_dt' => $this->originalHireDtForIndex($i),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($this->assignmentExists($empId)) {
                continue;
            }

            $supervisor = $deptId && isset($supervisorPositions[$deptId])
                ? $supervisorPositions[$deptId]->firstWhere('id', '!=', $jobCodeId)
                : null;

            $newAssignments[] = [
                'employee_num' => $empId,
                'effdt' => '2022-01-01',
                'effseq' => 0,
                'facility_id' => $facilityId,
                'dept_id' => $deptId,
                'position_id' => $jobCodeId,
                'reports_to' => $supervisor?->id,
                'reg_temp' => 'r',
                'full_part_time' => $i % 3 === 0 ? 'pt' : 'ft',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'bargaining_unit_id' => $i % 2 === 0 ? 1 : null,
                'union_seniority_dt' => $i % 2 === 0 ? '2022-01-01' : null,
            ];
        }

        if ($newEmployees !== []) {
            DB::table('bp_employees')->insert($newEmployees);
        }

        if ($newAssignments !== []) {
            DB::table('bp_emp_job_data')->insert($newAssignments);
        }
    }

    private function assignmentExists(string $employeeNum): bool
    {
        return DB::table('bp_emp_job_data')
            ->where('employee_num', $employeeNum)
            ->where('effdt', '2022-01-01')
            ->where('effseq', 0)
            ->exists();
    }

    private function employeeNum(int $index): string
    {
        return 'EMP' . str_pad((string) $index, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Original hire date for seeded employees (maps to original_hire_dt column).
     */
    private function originalHireDtForIndex(int $index): string
    {
        if ($index >= 21) {
            return '2022-01-01';
        }

        $month = (($index - 1) % 12) + 1;
        $year = 2016 + intdiv($index - 1, 12);

        return sprintf('%04d-%02d-01', $year, $month);
    }

    /**
     * Fill original_hire_dt for any existing employees still missing it.
     */
    private function backfillOriginalHireDates(): void
    {
        $missing = DB::table('bp_employees')
            ->whereNull('original_hire_dt')
            ->get(['id', 'employee_num']);

        foreach ($missing as $employee) {
            $hireDate = DB::table('bp_emp_job_data')
                ->where('employee_num', $employee->employee_num)
                ->orderBy('effdt')
                ->orderBy('effseq')
                ->value('effdt');

            if (!$hireDate && preg_match('/EMP(\d+)/', (string) $employee->employee_num, $matches)) {
                $hireDate = $this->originalHireDtForIndex((int) $matches[1]);
            }

            if (!$hireDate) {
                $hireDate = '2020-01-01';
            }

            DB::table('bp_employees')
                ->where('id', $employee->id)
                ->update([
                    'original_hire_dt' => $hireDate,
                    'updated_at' => now(),
                ]);
        }
    }
}
