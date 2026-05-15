<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocType;

class DocTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Applicant Information',
            'Identifications',
            'Verifications',
            'Acknowledgement of Receipts',
            'Skills Assessment',
            'Job Skills and Knowledge',
            'Dependability',
            'Interpersonal Skills',
            'Organizational Skills',
            'Communication Skills',
            'Problem-Solving Skills',
            'Safety & Health',
            'Areas Requiring Further Development',
            'Development Plans',
            'Employee Comments',
            'Administrator Orientation',
            'Blood Administration LN Competency Skills',
            'Blood Glucose Skills checklist',
            'CNA Competency Perineal Care Checklist',
            'CNA skills checklist',
            'DON Orientation Checklist',
            'Dietary Orientation Checklist',
            'Dietary_Department-Skills Checklist',
            'Housekeeper\'s Skills Checklist',
            'Licensed Nurse Competency Checklist',
            'NA skills checklist',
            'New DSD Orientation Checklist',
            'SSD Orientation Checklist',
            'Tracheostomy Care Skills Check',
            'Treatment Nurse Care Skills Checklist',
        ];
        foreach ($types as $name) {
            DocType::firstOrCreate(['name' => $name]);
        }
    }
}
