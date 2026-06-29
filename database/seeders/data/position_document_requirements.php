<?php

/**
 * Default general document requirements per position title.
 *
 * Document type names must match general upload_types (Documents Management),
 * not checklist-synced PART A–D types.
 *
 * @return array{
 *     sets: array<string, list<string>>,
 *     position_sets: array<string, list<string>>
 * }
 */
return [
    'sets' => [
        'all_staff' => [
            'Abuse Reporting Acknowledgment (SOC 341)',
            'Background Check Clearance',
            'Confidentiality Agreement',
            'Criminal Record Statement (LIC 508)',
            'Direct Deposit Authorization',
            'Driver License/ID',
            'Emergency Contact Designation',
            'Employee Handbook Acknowledgment',
            'Health Screening Declaration (Physician Statement)',
            'Hepatitis B Vaccination Record or Declination',
            'HIPAA Training Certificate',
            'I-9 Form',
            'Job Description (Signed)',
            'Physical Exam',
            'Proof of Age (18+)',
            'Sexual Harassment Training Certificate',
            'Social Security Card',
            'TB Test Result',
            'W-4 Form',
            'Workplace Violence Prevention Training Certificate',
        ],

        'annual_compliance' => [
            'Annual In-Service Training',
            'Annual Influenza Vaccination',
            'COVID-19 Vaccination Record',
            'MMR & Varicella Immunity Records',
            'LGBTI Cultural Competency Training Certificate',
        ],

        'nursing_clinical' => [
            'CPR Certification',
            'Dementia Care Training Record',
        ],

        'rn_license' => [
            'Registered Nurse License',
        ],

        'lvn_license' => [
            'Licensed Vocational Nurse License',
        ],

        'leadership' => [
            'Annual In-Service Training',
            'Annual Influenza Vaccination',
            'HIPAA Training Certificate',
        ],
    ],

  /*
   * Map each position title (from PositionsSeeder) to requirement sets.
   * Positions not listed receive only `all_staff` when the seeder runs with defaults.
   */
    'position_sets' => [
        // Nursing — licensed
        'Registered Nurse' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],
        'Charge Nurse' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],
        'Director of Nursing' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],
        'IP Nurse' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],
        'Licensed Nurse' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],
        'MDS Coordinator' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],
        'Staff Development Coordinator' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'rn_license'],

        // Nursing — LVN
        'Licensed Vocational Nurse' => ['all_staff', 'annual_compliance', 'nursing_clinical', 'lvn_license'],

        // Nursing — unlicensed direct care
        'Certified Nursing Assistant' => ['all_staff', 'annual_compliance', 'nursing_clinical'],
        'Nursing Assistant' => ['all_staff', 'annual_compliance', 'nursing_clinical'],
        'Unit Clerk' => ['all_staff', 'annual_compliance'],

        // Rehab (clinical-adjacent)
        'Rehab Manager' => ['all_staff', 'annual_compliance', 'nursing_clinical'],
        'Occupational Therapist' => ['all_staff', 'annual_compliance', 'nursing_clinical'],
        'Physical Therapist' => ['all_staff', 'annual_compliance', 'nursing_clinical'],
        'OT/PT Assistant' => ['all_staff', 'annual_compliance', 'nursing_clinical'],

        // Administration & leadership
        'Administrator' => ['all_staff', 'leadership'],
        'Director of Staff Development' => ['all_staff', 'leadership', 'annual_compliance'],
        'Business Office Manager' => ['all_staff', 'leadership'],
        'Medical Records Director' => ['all_staff', 'leadership'],
        'Marketing Director' => ['all_staff', 'leadership'],
        'Food Services Director' => ['all_staff', 'leadership'],
        'Admissions Coordinator' => ['all_staff', 'annual_compliance'],
        'Receptionist' => ['all_staff', 'annual_compliance'],
        'Medical Records Clerk' => ['all_staff', 'annual_compliance'],
        'Office Staff' => ['all_staff', 'annual_compliance'],
        'Other' => ['all_staff'],

        // Social services
        'Social Services Director' => ['all_staff', 'leadership'],
        'Social Worker' => ['all_staff', 'annual_compliance'],
        'Resident Liaison' => ['all_staff', 'annual_compliance'],
        'Case Manager' => ['all_staff', 'annual_compliance'],

        // Activities
        'Activities Director' => ['all_staff', 'annual_compliance'],
        'Activity Assistant' => ['all_staff', 'annual_compliance'],

        // Dietary
        'Dietary Manager' => ['all_staff', 'annual_compliance'],
        'Dietary Aide' => ['all_staff', 'annual_compliance'],
        'Cook' => ['all_staff', 'annual_compliance'],

        // Environmental services
        'Housekeeping Supervisor' => ['all_staff', 'annual_compliance'],
        'Housekeeper' => ['all_staff', 'annual_compliance'],
        'Janitor' => ['all_staff', 'annual_compliance'],
        'Laundry Staff' => ['all_staff', 'annual_compliance'],

        // Maintenance
        'Maintenance Director' => ['all_staff', 'annual_compliance'],
        'Maintenance Technician' => ['all_staff', 'annual_compliance'],
    ],
];
