<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Nursing Home Facility Departments (California specific)
        $facilityDepartments = [
            ['name' => 'Nursing', 'type' => 'facility', 'description' => 'Direct patient care, RNs, LVNs, CNAs'],
            ['name' => 'Administration', 'type' => 'facility', 'description' => 'Facility leadership and management'],
            ['name' => 'Admissions', 'type' => 'facility', 'description' => 'Patient intake and census management'],
            ['name' => 'Activities', 'type' => 'facility', 'description' => 'Recreation and social engagement programs'],
            ['name' => 'Social Services', 'type' => 'facility', 'description' => 'Social work, discharge planning, family support'],
            ['name' => 'Dietary', 'type' => 'facility', 'description' => 'Food service and nutrition management'],
            ['name' => 'Environmental Services', 'type' => 'facility', 'description' => 'Housekeeping and sanitation'],
            ['name' => 'Maintenance', 'type' => 'facility', 'description' => 'Building maintenance and repairs'],
            ['name' => 'Laundry', 'type' => 'facility', 'description' => 'Linen and clothing services'],
            ['name' => 'Medical Records', 'type' => 'facility', 'description' => 'Health information management and documentation'],
            ['name' => 'MDS/RAI Coordination', 'type' => 'facility', 'description' => 'Minimum Data Set and Resident Assessment'],
            ['name' => 'Business Office', 'type' => 'facility', 'description' => 'Billing, accounts receivable, financial operations'],
            ['name' => 'Human Resources', 'type' => 'facility', 'description' => 'Recruitment, employee relations, benefits'],
            ['name' => 'Quality Assurance', 'type' => 'facility', 'description' => 'QAPI, infection control, compliance monitoring'],
            ['name' => 'Infection Preventionist', 'type' => 'facility', 'description' => 'Infection control and prevention programs'],
            ['name' => 'Staff Development', 'type' => 'facility', 'description' => 'Training, education, competency assessment'],
            ['name' => 'Rehabilitation Services', 'type' => 'facility', 'description' => 'PT, OT, ST therapy services'],
            ['name' => 'Physical Therapy', 'type' => 'facility', 'description' => 'Physical rehabilitation services'],
            ['name' => 'Occupational Therapy', 'type' => 'facility', 'description' => 'Occupational rehabilitation services'],
            ['name' => 'Speech Therapy', 'type' => 'facility', 'description' => 'Speech-language pathology services'],
            ['name' => 'Respiratory Therapy', 'type' => 'facility', 'description' => 'Respiratory care services'],
            ['name' => 'Pharmacy', 'type' => 'facility', 'description' => 'Medication management and consultant pharmacy'],
            ['name' => 'Reception', 'type' => 'facility', 'description' => 'Front desk, visitor management, switchboard'],
            ['name' => 'Security', 'type' => 'facility', 'description' => 'Safety and security services'],
            ['name' => 'Central Supply', 'type' => 'facility', 'description' => 'Medical supplies and equipment management'],
            ['name' => 'Transportation', 'type' => 'facility', 'description' => 'Patient transport services'],
        ];

        // Bio Pacific Corporate Departments
        $corporateDepartments = [
            ['name' => 'Corporate Executive Leadership', 'type' => 'corporate', 'description' => 'C-suite and senior leadership'],
            ['name' => 'Corporate Operations', 'type' => 'corporate', 'description' => 'Multi-facility operations oversight'],
            ['name' => 'Corporate Nursing', 'type' => 'corporate', 'description' => 'Clinical services oversight and support'],
            ['name' => 'Corporate Human Resources', 'type' => 'corporate', 'description' => 'Enterprise HR strategy and support'],
            ['name' => 'Corporate Finance', 'type' => 'corporate', 'description' => 'Financial planning, accounting, treasury'],
            ['name' => 'Corporate Compliance', 'type' => 'corporate', 'description' => 'Regulatory compliance and audit'],
            ['name' => 'Corporate Quality Assurance', 'type' => 'corporate', 'description' => 'Enterprise quality and clinical outcomes'],
            ['name' => 'Corporate Legal', 'type' => 'corporate', 'description' => 'Legal affairs and contracts'],
            ['name' => 'Corporate Risk Management', 'type' => 'corporate', 'description' => 'Enterprise risk and insurance'],
            ['name' => 'Corporate Information Technology', 'type' => 'corporate', 'description' => 'IT infrastructure and systems'],
            ['name' => 'Corporate Marketing', 'type' => 'corporate', 'description' => 'Marketing and communications'],
            ['name' => 'Corporate Business Development', 'type' => 'corporate', 'description' => 'Growth strategy and M&A'],
            ['name' => 'Corporate Training & Development', 'type' => 'corporate', 'description' => 'Enterprise learning and development'],
            ['name' => 'Corporate Procurement', 'type' => 'corporate', 'description' => 'Purchasing and vendor management'],
            ['name' => 'Corporate Revenue Cycle', 'type' => 'corporate', 'description' => 'Billing and reimbursement operations'],
            ['name' => 'Corporate Payroll', 'type' => 'corporate', 'description' => 'Payroll processing and administration'],
            ['name' => 'Corporate Reimbursement', 'type' => 'corporate', 'description' => 'Medicare/Medi-Cal reimbursement'],
            ['name' => 'Corporate Data Analytics', 'type' => 'corporate', 'description' => 'Business intelligence and reporting'],
            ['name' => 'Corporate Regulatory Affairs', 'type' => 'corporate', 'description' => 'State and federal regulatory liaison'],
            ['name' => 'Corporate Government Relations', 'type' => 'corporate', 'description' => 'Legislative and policy advocacy'],
            ['name' => 'Corporate California Operations', 'type' => 'corporate', 'description' => 'California-specific operations support'],
            ['name' => 'Corporate Managed Care', 'type' => 'corporate', 'description' => 'Managed care contracting and relations'],
            ['name' => 'Corporate Clinical Services', 'type' => 'corporate', 'description' => 'Clinical program development and support'],
            ['name' => 'Corporate Facility Development', 'type' => 'corporate', 'description' => 'New facility development and renovations'],
            ['name' => 'Corporate Environmental Health & Safety', 'type' => 'corporate', 'description' => 'Safety programs and OSHA compliance'],
        ];

        // Insert facility departments
        foreach ($facilityDepartments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                [
                    'type' => $dept['type'],
                    'description' => $dept['description']
                ]
            );
        }

        // Insert corporate departments
        foreach ($corporateDepartments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                [
                    'type' => $dept['type'],
                    'description' => $dept['description']
                ]
            );
        }
    }
}
