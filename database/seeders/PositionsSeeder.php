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
        $nursingDept = Department::where('name', 'Nursing')->where('type', 'facility')->first();
        $administrationDept = Department::where('name', 'Administration')->where('type', 'facility')->first();
        $socialServicesDept = Department::where('name', 'Social Services')->where('type', 'facility')->first();
        $activitiesDept = Department::where('name', 'Activities')->where('type', 'facility')->first();
        $dietaryDept = Department::where('name', 'Dietary')->where('type', 'facility')->first();
        $envServicesDept = Department::where('name', 'Environmental Services')->where('type', 'facility')->first();
        $maintenanceDept = Department::where('name', 'Maintenance')->where('type', 'facility')->first();

        if (!$nursingDept || !$administrationDept || !$socialServicesDept || !$activitiesDept || !$dietaryDept || !$envServicesDept || !$maintenanceDept) {
            throw new \Exception('Required departments not found. Please run DepartmentSeeder first.');
        }

        $positions = [
                        
            // Activities/Activities Director
            ['title' => 'Activities Director', 'description' => 'Plans and coordinates resident activities.', 'department_id' => $activitiesDept->id, 'supervisor_role' => 1],
            ['title' => 'Activity Assistant', 'description' => 'Assists with resident activities.', 'department_id' => $activitiesDept->id, 'supervisor_role' => 0],
            // Administration
            ['title' => 'Administrator', 'description' => 'Manages overall facility operations.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1],
            ['title' => 'Admissions Coordinator', 'description' => 'Coordinates resident admissions.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0],
            ['title' => 'Business Office Manager', 'description' => 'Manages business office operations.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1],
            ['title' => 'Receptionist', 'description' => 'Manages front desk and resident inquiries.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0],
            ['title' => 'Medical Records Clerk', 'description' => 'Manages resident medical records.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0],
            ['title' => 'Medical Records Director', 'description' => 'Oversees medical records department.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1],
            ['title' => 'Office Staff', 'description' => 'Provides administrative support.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0],
            ['title' => 'Other', 'description' => 'Other administrative staff.', 'department_id' => $administrationDept->id, 'supervisor_role' => 0],
            // Nursing
            ['title' => 'Certified Nursing Assistant', 'description' => 'Provides basic care and assists residents with daily activities.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Charge Nurse', 'description' => 'Supervises nursing staff during shifts.', 'department_id' => $nursingDept->id, 'supervisor_role' => 1],
            ['title' => 'Director of Nursing', 'description' => 'Oversees nursing staff and operations.', 'department_id' => $nursingDept->id, 'supervisor_role' => 1],
            ['title' => 'IP Nurse', 'description' => 'Infection prevention nurse.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Licensed Nurse', 'description' => 'Licensed nurse.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Licensed Vocational Nurse', 'description' => 'Assists in nursing care under RN supervision.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Nursing Assistant', 'description' => 'Assists nursing staff.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Registered Nurse', 'description' => 'Provides nursing care to residents.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Staff Development Coordinator', 'description' => 'Coordinates staff training and development.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Unit Clerk', 'description' => 'Provides administrative support to nursing unit.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            // Social Services
            ['title' => 'Social Services Director', 'description' => 'Oversees social services department.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 1],
            ['title' => 'Social Worker', 'description' => 'Supports residents and families with social needs.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 0],
            ['title' => 'Resident Liaison', 'description' => 'Liaison for resident needs.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 0],
            ['title' => 'Case Manager', 'description' => 'Coordinates resident care and services.', 'department_id' => $socialServicesDept->id, 'supervisor_role' => 0],
            // Dietary
            ['title' => 'Dietary Manager', 'description' => 'Oversees dietary services and staff.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 1],
            ['title' => 'Dietary Aide', 'description' => 'Assists in food preparation and service.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 0],
            ['title' => 'Cook', 'description' => 'Prepares meals for residents.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 0],
            ['title' => 'Food Services Director', 'description' => 'Oversees food services.', 'department_id' => $dietaryDept->id, 'supervisor_role' => 1],
            // Environmental Services
            ['title' => 'Housekeeper', 'description' => 'Maintains facility cleanliness.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 0],
            ['title' => 'Housekeeping Supervisor', 'description' => 'Supervises housekeeping staff.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 1],
            ['title' => 'Janitor', 'description' => 'Performs janitorial duties.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 0],
            ['title' => 'Laundry Staff', 'description' => 'Handles laundry services.', 'department_id' => $envServicesDept->id, 'supervisor_role' => 0],
            // Maintenance
            ['title' => 'Maintenance Director', 'description' => 'Oversees facility maintenance.', 'department_id' => $maintenanceDept->id, 'supervisor_role' => 1],
            ['title' => 'Maintenance Technician', 'description' => 'Performs facility repairs and maintenance.', 'department_id' => $maintenanceDept->id, 'supervisor_role' => 0],
            // Marketing
            ['title' => 'Marketing Director', 'description' => 'Oversees marketing and outreach.', 'department_id' => $administrationDept->id, 'supervisor_role' => 1],
            // MDS
            ['title' => 'MDS Coordinator', 'description' => 'Manages resident assessment and care plans.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            // Rehab
            ['title' => 'Occupational Therapist', 'description' => 'Provides occupational therapy.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'OT/PT Assistant', 'description' => 'Assists occupational/physical therapists.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Physical Therapist', 'description' => 'Provides physical therapy.', 'department_id' => $nursingDept->id, 'supervisor_role' => 0],
            ['title' => 'Rehab Manager', 'description' => 'Manages rehabilitation services.', 'department_id' => $nursingDept->id, 'supervisor_role' => 1],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(
                ['title' => $position['title']],
                [
                    'description' => $position['description'],
                    'department_id' => $position['department_id'],
                    'supervisor_role' => $position['supervisor_role']
                ]
            );
        }
    }
}
