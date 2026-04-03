<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UploadTypesTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $types = [
            ['name' => 'Abuse Reporting Acknowledgment (SOC 341)', 'description' => 'Signed acknowledgment of abuse reporting responsibilities (SOC 341)', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Annual Influenza Vaccination', 'description' => 'Proof of annual flu vaccination', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Annual In-Service Training', 'description' => 'Proof of annual in-service training', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Background Check Clearance', 'description' => 'DOJ/FBI background check clearance', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Certified Nursing Assistant Certificate', 'description' => 'CNA certificate issued by California Department of Public Health', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Confidentiality Agreement', 'description' => 'Signed confidentiality agreement', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'COVID-19 Vaccination Record', 'description' => 'COVID-19 vaccination proof', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CPR Certification', 'description' => 'Cardiopulmonary Resuscitation certificate', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Criminal Record Statement (LIC 508)', 'description' => 'LIC 508 criminal record statement', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dementia Care Training Record', 'description' => 'Proof of dementia care training', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Direct Deposit Authorization', 'description' => 'Bank direct deposit form', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Driver License/ID', 'description' => 'Copy of Driver License or State ID', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Emergency Contact Designation', 'description' => 'Emergency contact information form', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Employee Handbook Acknowledgment', 'description' => 'Signed acknowledgment of employee handbook', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'First Aid Certification', 'description' => 'First Aid certificate', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Health Screening Declaration (Physician Statement)', 'description' => 'Physician statement or health screening declaration', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hepatitis B Vaccination Record or Declination', 'description' => 'Proof of Hepatitis B vaccination or declination', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HIPAA Training Certificate', 'description' => 'Proof of HIPAA training', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'I-9 Form', 'description' => 'Employment Eligibility Verification', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Job Description (Signed)', 'description' => 'Signed job description', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LGBTI Cultural Competency Training Certificate', 'description' => 'Proof of LGBTI cultural competency training', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Licensed Vocational Nurse License', 'description' => 'LVN license issued by California Board of Vocational Nursing', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MMR & Varicella Immunity Records', 'description' => 'Proof of MMR and Varicella immunity', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Physical Exam', 'description' => 'Employee physical examination record', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Proof of Age (18+)', 'description' => 'Proof employee is 18 years or older', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sexual Harassment Training Certificate', 'description' => 'Proof of sexual harassment prevention training', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Registered Nurse License', 'description' => 'RN license issued by California Board of Registered Nursing', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Resume', 'description' => 'Applicant resume', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Social Security Card', 'description' => 'Copy of Social Security Card', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TB Test Result', 'description' => 'Tuberculosis test result', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'W-4 Form', 'description' => 'IRS Employee’s Withholding Certificate', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Work Authorization', 'description' => 'Work permit or permanent resident card', 'requires_expiry' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Workplace Violence Prevention Training Certificate', 'description' => 'Proof of workplace violence prevention training', 'requires_expiry' => true, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('upload_types')->insert($types);
    }
}
