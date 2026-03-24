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
        ];
        foreach ($types as $name) {
            DocType::firstOrCreate(['name' => $name]);
        }
    }
}
