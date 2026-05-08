<?php
namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionsSeeder extends Seeder
{
    public function run(): void
    {
        // Get department IDs
        $nursingDept = Department::query()->where('name', 'Nursing')->where('type', 'facility')->first();
        $administrationDept = Department::query()->where('name', 'Administration')->where('type', 'facility')->first();
        $socialServicesDept = Department::query()->where('name', 'Social Services')->where('type', 'facility')->first();
        $activitiesDept = Department::query()->where('name', 'Activities')->where('type', 'facility')->first();
        $dietaryDept = Department::query()->where('name', 'Dietary')->where('type', 'facility')->first();
        $envServicesDept = Department::query()->where('name', 'Environmental Services')->where('type', 'facility')->first();
        $maintenanceDept = Department::query()->where('name', 'Maintenance')->where('type', 'facility')->first();

        if (!$nursingDept || !$administrationDept || !$socialServicesDept || !$activitiesDept || !$dietaryDept || !$envServicesDept || !$maintenanceDept) {
            throw new \Exception('Required departments not found. Please run DepartmentSeeder first.');
        }

        $positions = [
                        
            // Activities/Activities Director
            ['title' => 'Activities Director', 'description' => 'Plans and coordinates resident activities.', 'department_id' => $activitiesDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ['title' => 'Activity Assistant', 'description' => 'Assists with resident activities.', 'department_id' => $activitiesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Activities Director'],
            // Administration
            ['title' => 'Administrator', 'description' => 'Manages overall facility operations.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1, 'reports_to_title' => null],
            ['title' => 'Admissions Coordinator', 'description' => 'Coordinates resident admissions.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Administrator'],
            ['title' => 'Business Office Manager', 'description' => 'Manages business office operations.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ['title' => 'Receptionist', 'description' => 'Manages front desk and resident inquiries.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Business Office Manager'],
            ['title' => 'Medical Records Clerk', 'description' => 'Manages resident medical records.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Medical Records Director'],
            ['title' => 'Medical Records Director', 'description' => 'Oversees medical records department.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ['title' => 'Office Staff', 'description' => 'Provides administrative support.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Business Office Manager'],
            ['title' => 'Other', 'description' => 'Other administrative staff.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Administrator'],
            // Nursing
            ['title' => 'Certified Nursing Assistant', 'description' => 'Provides basic care and assists residents with daily activities.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Charge Nurse'],
            ['title' => 'Charge Nurse', 'description' => 'Supervises nursing staff during shifts.', 'department_id' => $nursingDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Director of Nursing'],
            ['title' => 'Director of Nursing', 'description' => 'Oversees nursing staff and operations.', 'department_id' => $nursingDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            
            ['title' => 'IP Nurse', 'description' => 'Infection prevention nurse.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Director of Nursing'],
            ['title' => 'Licensed Nurse', 'description' => 'Licensed nurse.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Charge Nurse'],
            ['title' => 'Licensed Vocational Nurse', 'description' => 'Assists in nursing care under RN supervision.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Charge Nurse'],
            ['title' => 'Nursing Assistant', 'description' => 'Assists nursing staff.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Charge Nurse'],
            ['title' => 'Registered Nurse', 'description' => 'Provides nursing care to residents.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Charge Nurse'],
            ['title' => 'Staff Development Coordinator', 'description' => 'Coordinates staff training and development.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Director of Nursing'],
            ['title' => 'Unit Clerk', 'description' => 'Provides administrative support to nursing unit.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Charge Nurse'],
            // Social Services
            ['title' => 'Social Services Director', 'description' => 'Oversees social services department.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ['title' => 'Social Worker', 'description' => 'Supports residents and families with social needs.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Social Services Director'],
            ['title' => 'Resident Liaison', 'description' => 'Liaison for resident needs.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Social Services Director'],
            ['title' => 'Case Manager', 'description' => 'Coordinates resident care and services.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Social Services Director'],
            // Dietary
            ['title' => 'Dietary Manager', 'description' => 'Oversees dietary services and staff.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Food Services Director'],
            ['title' => 'Dietary Aide', 'description' => 'Assists in food preparation and service.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Dietary Manager'],
            ['title' => 'Cook', 'description' => 'Prepares meals for residents.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Dietary Manager'],
            ['title' => 'Food Services Director', 'description' => 'Oversees food services.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            // Environmental Services
            ['title' => 'Housekeeper', 'description' => 'Maintains facility cleanliness.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Housekeeping Supervisor'],
            ['title' => 'Housekeeping Supervisor', 'description' => 'Supervises housekeeping staff.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ['title' => 'Janitor', 'description' => 'Performs janitorial duties.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Housekeeping Supervisor'],
            ['title' => 'Laundry Staff', 'description' => 'Handles laundry services.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Housekeeping Supervisor'],
            // Maintenance
            ['title' => 'Maintenance Director', 'description' => 'Oversees facility maintenance.', 'department_id' => $maintenanceDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ['title' => 'Maintenance Technician', 'description' => 'Performs facility repairs and maintenance.', 'department_id' => $maintenanceDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Maintenance Director'],
            // Marketing
            ['title' => 'Marketing Director', 'description' => 'Oversees marketing and outreach.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            // MDS
            ['title' => 'MDS Coordinator', 'description' => 'Manages resident assessment and care plans.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Director of Nursing'],
            // Rehab
            ['title' => 'Occupational Therapist', 'description' => 'Provides occupational therapy.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Rehab Manager'],
            ['title' => 'OT/PT Assistant', 'description' => 'Assists occupational/physical therapists.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Rehab Manager'],
            ['title' => 'Physical Therapist', 'description' => 'Provides physical therapy.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0, 'reports_to_title' => 'Rehab Manager'],
            ['title' => 'Rehab Manager', 'description' => 'Manages rehabilitation services.', 'department_id' => $nursingDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Director of Nursing'],
            ['title' => 'Director of Staff Development', 'description' => 'Leads staff development and education programs.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1, 'reports_to_title' => 'Administrator'],
            ];

        $seededPositions = [];

        foreach ($positions as $position) {
            $seededPositions[$position['title']] = Position::updateOrCreate(
                ['title' => $position['title']],
                [
                    'description' => $position['description'],
                    'department_id' => $position['department_id'],
                    'supervisor_role' => $position['supervisor_role']
                ]
            );
        }

        foreach ($positions as $position) {
            $currentPosition = $seededPositions[$position['title']] ?? Position::where('title', $position['title'])->first();
            $reportsToTitle = $position['reports_to_title'] ?? null;
            $reportsToPosition = $reportsToTitle ? ($seededPositions[$reportsToTitle] ?? Position::where('title', $reportsToTitle)->first()) : null;

            if ($currentPosition) {
                $currentPosition->update([
                    'reports_to_position_id' => $reportsToPosition?->id,
                ]);
            }
        }
    }
}
