<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;

class EmployeeEmailMappingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = Facility::all();

        $data = [];
        foreach ($facilities as $facility) {
            // Book a Tour employees
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'book-a-tour',
                'employee_name' => 'Sarah Johnson',
                'employee_email' => 'sarah.johnson@biopacific.com',
                'title' => 'Admissions Director',
                'is_primary' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'book-a-tour',
                'employee_name' => 'Mike Davis',
                'employee_email' => 'mike.davis@biopacific.com',
                'title' => 'Tour Coordinator',
                'is_primary' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Inquiry employees
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'inquiry',
                'employee_name' => 'Lisa Martinez',
                'employee_email' => 'lisa.martinez@biopacific.com',
                'title' => 'Customer Service Manager',
                'is_primary' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'inquiry',
                'employee_name' => 'Tom Wilson',
                'employee_email' => 'tom.wilson@biopacific.com',
                'title' => 'Support Specialist',
                'is_primary' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Hiring employees
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'hiring',
                'employee_name' => 'Jennifer Brown',
                'employee_email' => 'jennifer.brown@biopacific.com',
                'title' => 'HR Director',
                'is_primary' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'hiring',
                'employee_name' => 'Robert Taylor',
                'employee_email' => 'robert.taylor@biopacific.com',
                'title' => 'Recruiter',
                'is_primary' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('employee_email_mappings')->insert($data);
    }
}
