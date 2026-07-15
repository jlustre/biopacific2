<?php

namespace Database\Seeders;

use App\Models\ImportMappingPreset;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Auto-generated from Import Preset Management → Update seeder.
 * Last exported: 2026-07-15 09:41:02
 *
 * Do not edit preset data by hand; use the admin UI and re-export.
 */
class ImportMappingPresetsTableSeeder extends Seeder
{
    public function run(): void
    {
        $presets = json_decode(<<<'IMPORT_MAPPING_PRESETS_JSON'
[
    {
        "name": "Employees Import Preset",
        "facility_id": 99,
        "owner_email": "super-admin@biopacific.com",
        "mappings": [
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "employee_num",
                "worksheet_column": "Employee Num"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "last_name",
                "worksheet_column": "Lastname"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "first_name",
                "worksheet_column": "Firstname"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "middle_name",
                "worksheet_column": "Middle"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "ssn",
                "worksheet_column": "SSN"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "gender",
                "worksheet_column": "Gender"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "marital_status_id",
                "worksheet_column": "Marital Status"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "ethnic_group_id",
                "worksheet_column": "Ethnic Group"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "military_status_id",
                "worksheet_column": "Military Service"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "citizenship_status_id",
                "worksheet_column": "Immigration Status"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "action_id",
                "worksheet_column": "Action"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "email",
                "worksheet_column": "Employee Email"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "dob",
                "worksheet_column": "DOB"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "original_hire_dt",
                "worksheet_column": "Original Hire Date"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "badge_eff_dt",
                "worksheet_column": "Badge Eff Date"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "union_code",
                "worksheet_column": "Union Code"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "effdt_of_membership",
                "worksheet_column": "Union Seniority Date"
            },
            {
                "table": "bp_employees",
                "worksheet": "Profile",
                "table_column": "badge_num",
                "worksheet_column": "Badge Number"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "employee_num",
                "worksheet_column": "Employee Num"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "facility_id",
                "worksheet_column": "Facility"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "position_id",
                "worksheet_column": "Positions"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "dept_id",
                "worksheet_column": "Departments"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "reports_to",
                "worksheet_column": "Reports To"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "effdt",
                "worksheet_column": "Effective Date"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "reg_temp",
                "worksheet_column": "Reg_Temp"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "full_part_time",
                "worksheet_column": "Full Part PerDiem"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "hourly_status_id",
                "worksheet_column": "Hourly Status"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "std_hrs_week",
                "worksheet_column": "Std. Hrs./Week"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "amount",
                "worksheet_column": "Amount"
            },
            {
                "table": "bp_emp_job_data",
                "worksheet": "JobData",
                "table_column": "compensation_rate_id",
                "worksheet_column": "Compensation Rate"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "employee_num",
                "worksheet_column": "Employee Num"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "fed_tax_data",
                "worksheet_column": "Federal Tax Data"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "fed_withholding_allowance",
                "worksheet_column": "Fed Withholding Allowance"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "state_tax_data",
                "worksheet_column": "State Tax Data"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "state_withholding_allowance1",
                "worksheet_column": "State Withholding Allowance"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "resident",
                "worksheet_column": "Resident"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "local_withholding_allowance",
                "worksheet_column": "Local Withholding Allowance"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "locality",
                "worksheet_column": "Locality"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "county",
                "worksheet_column": "County"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "addl_withholding_amount1",
                "worksheet_column": "Addl Withholding $"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "addl_withholding_percentage1",
                "worksheet_column": "Addl Withholding %"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "addl_withholding_amount2",
                "worksheet_column": "Addl Withholding2 $"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "addl_withholding_percentage2",
                "worksheet_column": "Addl Withholding2 %"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "resident_state",
                "worksheet_column": "Resident State"
            },
            {
                "table": "bp_emp_tax_data",
                "worksheet": "TaxData",
                "table_column": "effdt",
                "worksheet_column": "EffDt"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "employee_num",
                "worksheet_column": "Employee Num"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "effdt",
                "worksheet_column": "EffDt"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "address_type",
                "worksheet_column": "Address Type"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "address1",
                "worksheet_column": "Address1"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "address2",
                "worksheet_column": "Address2"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "city",
                "worksheet_column": "City"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "state",
                "worksheet_column": "State"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "zip",
                "worksheet_column": "Zipcode"
            },
            {
                "table": "bp_emp_phones",
                "worksheet": "Phones",
                "table_column": "employee_num",
                "worksheet_column": "Employee Num"
            },
            {
                "table": "bp_emp_phones",
                "worksheet": "Phones",
                "table_column": "is_primary",
                "worksheet_column": "Is Primary"
            },
            {
                "table": "bp_emp_phones",
                "worksheet": "Phones",
                "table_column": "phone_number",
                "worksheet_column": "Phone Number"
            },
            {
                "table": "bp_emp_addresses",
                "worksheet": "Addresses",
                "table_column": "is_primary",
                "worksheet_column": "Is Primary"
            },
            {
                "table": "bp_emp_phones",
                "worksheet": "Phones",
                "table_column": "effdt",
                "worksheet_column": "EffDt"
            }
        ]
    }
]
IMPORT_MAPPING_PRESETS_JSON, true) ?? [];

        foreach ($presets as $preset) {
            $userId = User::where('email', $preset['owner_email'])->value('id');

            if (! $userId) {
                $fallbackEmail = config('member-portal.super_admin_email', 'super-admin@biopacific.com');
                $userId = User::where('email', $fallbackEmail)->value('id');
            }

            if (! $userId) {
                $this->command?->warn(
                    'ImportMappingPresetsTableSeeder: skipped preset "' . $preset['name'] . '" — user not found: ' . $preset['owner_email']
                );
                continue;
            }

            ImportMappingPreset::updateOrCreate(
                [
                    'name' => $preset['name'],
                    'facility_id' => $preset['facility_id'],
                ],
                [
                    'user_id' => $userId,
                    'mappings' => $preset['mappings'],
                ]
            );
        }
    }
}
