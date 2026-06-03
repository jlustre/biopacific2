<?php

namespace Database\Seeders;

use App\Models\BPEmployee;
use App\Models\BPEmpChecklist;
use App\Models\BPEmpJobData;
use App\Models\ChecklistItem;
use App\Models\Department;
use App\Models\Facility;
use App\Models\Position;
use Carbon\Carbon;
use Database\Seeders\Support\ResolvesPortalSeedData;
use Illuminate\Database\Seeder;

class MemberPortalDashboardSeeder extends Seeder
{
    use ResolvesPortalSeedData;

    public function run(): void
    {
        $donUser = $this->demoUser('don');
        if (!$donUser) {
            $this->command?->warn('MemberPortalDashboardSeeder: DON demo user not found — skipping team seed.');

            return;
        }

        $donEmployee = $donUser->resolvedBpEmployee(['currentAssignment.position']);
        $facilityId = $donEmployee?->currentAssignment?->facility_id ?? $donUser->facility_id;
        $facility = $facilityId ? Facility::find($facilityId) : $this->facilityByMatcher('pineridge');

        if (!$facility) {
            $this->command?->warn('MemberPortalDashboardSeeder: facility not resolved — skipping team seed.');

            return;
        }

        $nursingDept = $this->nursingDepartment();
        $positions = $this->nursingStaffPositions();

        if ($positions->isEmpty() || !$nursingDept) {
            $this->command?->warn('MemberPortalDashboardSeeder: nursing department/positions not found.');

            return;
        }

        $teamProfiles = [
            ['num' => 'DASHN001', 'first' => 'Rosa', 'last' => 'Mendoza', 'title' => 'Certified Nursing Assistant', 'gap' => 'high'],
            ['num' => 'DASHN002', 'first' => 'Kevin', 'last' => 'Nguyen', 'title' => 'Licensed Vocational Nurse', 'gap' => 'medium'],
            ['num' => 'DASHN003', 'first' => 'Aisha', 'last' => 'Patel', 'title' => 'Registered Nurse', 'gap' => 'high'],
            ['num' => 'DASHN004', 'first' => 'Tom', 'last' => 'Garcia', 'title' => 'Charge Nurse', 'gap' => 'low'],
        ];

        foreach ($teamProfiles as $index => $profile) {
            $position = $positions->firstWhere('title', $profile['title']) ?? $positions[$index % $positions->count()];
            $this->seedNursingTeamMember($facility, $nursingDept, $position, $profile, $donEmployee?->employee_num);
        }
    }

    /**
     * @param  array{num: string, first: string, last: string, title: string, gap: string}  $profile
     */
    protected function seedNursingTeamMember(
        Facility $facility,
        Department $department,
        Position $position,
        array $profile,
        ?string $excludeEmployeeNum
    ): void {
        if ($profile['num'] === $excludeEmployeeNum) {
            return;
        }

        $employee = BPEmployee::query()->firstOrCreate(
            ['employee_num' => $profile['num']],
            [
                'first_name' => $profile['first'],
                'last_name' => $profile['last'],
                'email' => strtolower($profile['num']) . '@demo.biopacific.local',
                'gender' => 'F',
                'dob' => '1985-06-15',
                'original_hire_dt' => '2020-05-01',
                'ssn' => '900-00-' . substr($profile['num'], -4),
            ]
        );

        if (!BPEmpJobData::query()->where('employee_num', $profile['num'])->exists()) {
            BPEmpJobData::create([
                'employee_num' => $profile['num'],
                'effdt' => '2022-01-01',
                'effseq' => 0,
                'facility_id' => $facility->id,
                'dept_id' => $department->id,
                'position_id' => $position->id,
                'reports_to' => $position->id,
                'reg_temp' => 'r',
                'full_part_time' => 'ft',
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        $this->seedChecklistGaps($employee->employee_num, (int) $position->id, $profile['gap']);
    }

    protected function seedChecklistGaps(string $employeeNum, int $positionId, string $gapLevel): void
    {
        $applicable = ChecklistItem::query()
            ->applicableToPosition($positionId)
            ->orderBy('order')
            ->limit(8)
            ->get();

        if ($applicable->isEmpty()) {
            return;
        }

        $verifiedCount = match ($gapLevel) {
            'low' => max(0, $applicable->count() - 1),
            'medium' => (int) floor($applicable->count() / 2),
            default => 1,
        };

        $items = [];
        $now = Carbon::now()->toDateString();

        foreach ($applicable as $index => $item) {
            $key = 'item_' . $item->id;

            if ($index < $verifiedCount) {
                $items[$key] = [
                    'on_file' => true,
                    'verified_dt' => $now,
                    'expiry_dt' => $item->isExpiring ? Carbon::now()->addMonths(6)->toDateString() : null,
                ];
                continue;
            }

            $items[$key] = [
                'on_file' => $index % 2 === 0,
                'verified_dt' => null,
            ];
        }

        BPEmpChecklist::query()->updateOrCreate(
            ['employee_num' => $employeeNum],
            ['items' => $items]
        );
    }
}
