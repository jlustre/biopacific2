<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BPEmpJobDataTableSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = [];
        $positionIdsByCode = DB::table('positions')
            ->whereNotNull('position_code')
            ->pluck('id', 'position_code');
        $seedPositionCodes = ['ADMIN', 'DON', 'RN', 'LPN', 'CNA'];
        $supervisorColumn = Schema::hasColumn('positions', 'has_supervisor_role')
            ? 'has_supervisor_role'
            : 'supervisor_role';

        // Get all supervisor positions indexed by dept_code
        $supervisorPositions = DB::table('positions')
            ->get()
            ->groupBy('dept_code');

        $departmentIds = DB::table('departments')->pluck('id')->toArray();
                $positionsByTitle = DB::table('positions')
                    ->select('id', 'title', 'department_id')
                    ->orderBy('id')
                    ->get()
                    ->keyBy('title');
                $seedPositionTitles = [
                    'Certified Nursing Assistant',
                    'Certified Nursing Assistant',
                    'Certified Nursing Assistant',
                    'Certified Nursing Assistant',
                    'Licensed Vocational Nurse',
                    'Registered Nurse',
                    'Licensed Nurse',
                    'Charge Nurse',
                    'Housekeeper',
                    'Dietary Aide',
                    'Activity Assistant',
                    'Maintenance Technician',
                    'Receptionist',
                    'Admissions Coordinator',
                    'Social Worker',
                    'Administrator',
                    'Director of Nursing',
                ];
                $fallbackPosition = DB::table('positions')
                    ->select('id', 'title', 'department_id')
                    ->orderBy('id')
                    ->first();
        $deptCount = count($departmentIds);
        for ($i = 1; $i <= 20; $i++) {
            $isUnion = $i % 2 === 0;
            $deptId = $departmentIds[($i - 1) % $deptCount];
            $jobCode = $seedPositionCodes[$i % count($seedPositionCodes)];
                    $jobTitle = $seedPositionTitles[($i - 1) % count($seedPositionTitles)];
                    $position = $positionsByTitle[$jobTitle] ?? $fallbackPosition;
                    $jobCodeId = $position?->id ?? Position::query()->value('id');
                    $deptId = $position?->department_id ?? ($deptCount > 0 ? $departmentIds[($i - 1) % $deptCount] : null);
                    // Find a supervisor in the same department, but avoid self-reporting for supervisor roles.
                    $supervisor = $deptId && isset($supervisorPositions[$deptId])
                        ? $supervisorPositions[$deptId]->firstWhere('id', '!=', $jobCodeId)
                : null;
            $reportsTo = $supervisor ? $supervisor->id : null;

            $assignments[] = [
                'employee_num' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'effdt' => '2022-01-01',
                'effseq' => 0,
                'facility_id' => ($i % 2) + 1, // alternate between 1 and 2
                'dept_id' => $deptId, // use real department id
                'position_id' => $jobCodeId,
                'reports_to' => $reportsTo,
                'reg_temp' => 'r',
                'full_part_time' => $i % 3 === 0 ? 'pt' : 'ft',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'bargaining_unit_id' => $isUnion ? 1 : null,
                'union_seniority_dt' => $isUnion ? '2022-01-01' : null,
            ];
        }
        DB::table('bp_emp_job_data')->insert($assignments);
    }
}
