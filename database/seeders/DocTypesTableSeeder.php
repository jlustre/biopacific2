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
        ];
        foreach ($types as $name) {
            DocType::firstOrCreate(['name' => $name]);
        }
    }
}
