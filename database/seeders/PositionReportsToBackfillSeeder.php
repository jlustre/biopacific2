<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionReportsToBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $reportingMap = [
            'Activities Director' => 'Administrator',
            'Activity Assistant' => 'Activities Director',
            'Administrator' => null,
            'Admissions Coordinator' => 'Administrator',
            'Business Office Manager' => 'Administrator',
            'Receptionist' => 'Business Office Manager',
            'Medical Records Clerk' => 'Medical Records Director',
            'Medical Records Director' => 'Administrator',
            'Office Staff' => 'Business Office Manager',
            'Other' => 'Administrator',
            'Certified Nursing Assistant' => 'Charge Nurse',
            'Charge Nurse' => 'Director of Nursing',
            'Director of Nursing' => 'Administrator',
            'IP Nurse' => 'Director of Nursing',
            'Licensed Nurse' => 'Charge Nurse',
            'Licensed Vocational Nurse' => 'Charge Nurse',
            'Nursing Assistant' => 'Charge Nurse',
            'Registered Nurse' => 'Charge Nurse',
            'Staff Development Coordinator' => 'Director of Nursing',
            'Unit Clerk' => 'Charge Nurse',
            'Social Services Director' => 'Administrator',
            'Social Worker' => 'Social Services Director',
            'Resident Liaison' => 'Social Services Director',
            'Case Manager' => 'Social Services Director',
            'Dietary Manager' => 'Food Services Director',
            'Dietary Aide' => 'Dietary Manager',
            'Cook' => 'Dietary Manager',
            'Food Services Director' => 'Administrator',
            'Housekeeper' => 'Housekeeping Supervisor',
            'Housekeeping Supervisor' => 'Administrator',
            'Janitor' => 'Housekeeping Supervisor',
            'Laundry Staff' => 'Housekeeping Supervisor',
            'Maintenance Director' => 'Administrator',
            'Maintenance Technician' => 'Maintenance Director',
            'Marketing Director' => 'Administrator',
            'MDS Coordinator' => 'Director of Nursing',
            'Occupational Therapist' => 'Rehab Manager',
            'OT/PT Assistant' => 'Rehab Manager',
            'Physical Therapist' => 'Rehab Manager',
            'Rehab Manager' => 'Director of Nursing',
            'Director of Staff Development' => 'Administrator',
            'President' => null,
            'IT Director' => 'President',
            'Web Developer' => 'IT Director',
        ];

        $positionsByTitle = Position::query()->pluck('id', 'title');

        foreach ($reportingMap as $title => $reportsToTitle) {
            $positionId = $positionsByTitle[$title] ?? null;

            if (!$positionId) {
                continue;
            }

            Position::query()
                ->whereKey($positionId)
                ->update([
                    'reports_to_position_id' => $reportsToTitle ? ($positionsByTitle[$reportsToTitle] ?? null) : null,
                ]);
        }
    }
}