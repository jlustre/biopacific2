<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportCategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Employee Roster',
            'Facility',
            'Attendance',
            'Payroll',
            'Overtime',
            'Licensure & Certification',
            'Training & Inservice',
            'Performance Evaluation',
            'Disciplinary Action',
            'Incident Reports',
            'Infection Control',
            'Staffing',
            'Scheduling',
            'Recruitment',
            'Onboarding',
            'Separation/Termination',
            'Benefits',
            'Compliance',
            'Union',
            'Workplace Safety',
            'Leave of Absence',
            'COVID-19',
            'Wage & Hour',
            'Grievances',
            'State/Federal Reporting',
            'Other',
        ];
        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);
        $now = Carbon::now();
        foreach ($categories as $cat) {
            DB::table('report_categories')->updateOrInsert(
                ['name' => $cat],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
