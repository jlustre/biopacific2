<?php

/**
 * Default general document requirements per position title.
 *
 * Document type names must match general upload_types (Documents Management),
 * not checklist-synced PART A–D types.
 *
 * Auto-generated from Documents Management → Position requirements → Update seeder on 2026-07-08 11:18:55.
 *
 * @return array{
 *     sets: array<string, list<string>>,
 *     position_sets: array<string, list<string>>
 * }
 */
return [
    'sets' => [
            'all_staff' => [
                0 => 'Abuse Reporting Acknowledgment (SOC 341)',
                1 => 'Background Check Clearance',
                2 => 'Confidentiality Agreement',
                3 => 'Criminal Record Statement (LIC 508)',
                4 => 'Direct Deposit Authorization',
                5 => 'Emergency Contact Designation',
                6 => 'Employee Handbook Acknowledgment',
                7 => 'Health Screening Declaration (Physician Statement)',
                8 => 'Hepatitis B Vaccination Record or Declination',
                9 => 'HIPAA Training Certificate',
                10 => 'I-9 Form',
                11 => 'Job Description (Signed)',
                12 => 'Physical Exam',
                13 => 'Proof of Age (18+)',
                14 => 'Sexual Harassment Training Certificate',
                15 => 'Social Security Card',
                16 => 'TB Test Result',
                17 => 'W-4 Form',
                18 => 'Workplace Violence Prevention Training Certificate',
            ],
            'annual_compliance' => [
                0 => 'Annual In-Service Training',
                1 => 'Annual Influenza Vaccination',
                2 => 'COVID-19 Vaccination Record',
                3 => 'MMR & Varicella Immunity Records',
                4 => 'LGBTI Cultural Competency Training Certificate',
            ],
            'custom_e77baa0c' => [
                0 => 'Abuse Reporting Acknowledgment (SOC 341)',
                1 => 'Annual In-Service Training',
                2 => 'Annual Influenza Vaccination',
                3 => 'Background Check Clearance',
                4 => 'COVID-19 Vaccination Record',
                5 => 'Confidentiality Agreement',
                6 => 'Criminal Record Statement (LIC 508)',
                7 => 'Direct Deposit Authorization',
                8 => 'Emergency Contact Designation',
                9 => 'Employee Handbook Acknowledgment',
                10 => 'HIPAA Training Certificate',
                11 => 'Health Screening Declaration (Physician Statement)',
                12 => 'Hepatitis B Vaccination Record or Declination',
                13 => 'I-9 Form',
                14 => 'Job Description (Signed)',
                15 => 'LGBTI Cultural Competency Training Certificate',
                16 => 'MMR & Varicella Immunity Records',
                17 => 'Physical Exam',
                18 => 'Proof of Age (18+)',
                19 => 'Sexual Harassment Training Certificate',
                20 => 'Social Security Card',
                21 => 'TB Test Result',
                22 => 'W-4 Form',
                23 => 'Workplace Violence Prevention Training Certificate',
            ],
            'custom_f1b93a93' => [
                0 => 'Abuse Reporting Acknowledgment (SOC 341)',
                1 => 'Annual In-Service Training',
                2 => 'Annual Influenza Vaccination',
                3 => 'Background Check Clearance',
                4 => 'Confidentiality Agreement',
                5 => 'Criminal Record Statement (LIC 508)',
                6 => 'Direct Deposit Authorization',
                7 => 'Emergency Contact Designation',
                8 => 'Employee Handbook Acknowledgment',
                9 => 'HIPAA Training Certificate',
                10 => 'Health Screening Declaration (Physician Statement)',
                11 => 'Hepatitis B Vaccination Record or Declination',
                12 => 'I-9 Form',
                13 => 'Job Description (Signed)',
                14 => 'Physical Exam',
                15 => 'Proof of Age (18+)',
                16 => 'Sexual Harassment Training Certificate',
                17 => 'Social Security Card',
                18 => 'TB Test Result',
                19 => 'W-4 Form',
                20 => 'Workplace Violence Prevention Training Certificate',
            ],
            'leadership' => [
                0 => 'Annual In-Service Training',
                1 => 'Annual Influenza Vaccination',
                2 => 'HIPAA Training Certificate',
            ],
            'lvn_license' => [
                0 => 'Licensed Vocational Nurse License',
            ],
            'nursing_clinical' => [
                0 => 'CPR Certification',
                1 => 'Dementia Care Training Record',
            ],
            'rn_license' => [
                0 => 'Registered Nurse License',
            ],
        ],

    /*
     * Map each position title to requirement sets.
     * Positions not listed receive only `all_staff` when the seeder runs with defaults.
     */
    'position_sets' => [
            'Activities Director' => [
                0 => 'all_staff',
                1 => 'annual_compliance',
            ],
            'Activity Assistant' => [
                0 => 'all_staff',
                1 => 'annual_compliance',
            ],
            'Administrator' => [
                0 => 'custom_f1b93a93',
            ],
            'Admissions Coordinator' => [
                0 => 'custom_e77baa0c',
            ],
            'Business Office Manager' => [
                0 => 'custom_f1b93a93',
            ],
            'Case Manager' => [
                0 => 'custom_e77baa0c',
            ],
            'Certified Nursing Assistant' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
            ],
            'Charge Nurse' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Cook' => [
                0 => 'custom_e77baa0c',
            ],
            'Dietary Aide' => [
                0 => 'custom_e77baa0c',
            ],
            'Dietary Manager' => [
                0 => 'custom_e77baa0c',
            ],
            'Director of Nursing' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Director of Staff Development' => [
                0 => 'custom_e77baa0c',
            ],
            'Food Services Director' => [
                0 => 'custom_f1b93a93',
            ],
            'Housekeeper' => [
                0 => 'custom_e77baa0c',
            ],
            'Housekeeping Supervisor' => [
                0 => 'custom_e77baa0c',
            ],
            'IP Nurse' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Janitor' => [
                0 => 'custom_e77baa0c',
            ],
            'Laundry Staff' => [
                0 => 'custom_e77baa0c',
            ],
            'Licensed Nurse' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Licensed Vocational Nurse' => [
                0 => 'custom_e77baa0c',
                1 => 'lvn_license',
                2 => 'nursing_clinical',
            ],
            'MDS Coordinator' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Maintenance Director' => [
                0 => 'custom_e77baa0c',
            ],
            'Maintenance Technician' => [
                0 => 'custom_e77baa0c',
            ],
            'Marketing Director' => [
                0 => 'custom_f1b93a93',
            ],
            'Medical Records Clerk' => [
                0 => 'custom_e77baa0c',
            ],
            'Medical Records Director' => [
                0 => 'custom_f1b93a93',
            ],
            'Nursing Assistant' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
            ],
            'OT/PT Assistant' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
            ],
            'Occupational Therapist' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
            ],
            'Office Staff' => [
                0 => 'custom_e77baa0c',
            ],
            'Other' => [
                0 => 'all_staff',
            ],
            'Physical Therapist' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
            ],
            'Receptionist' => [
                0 => 'custom_e77baa0c',
            ],
            'Registered Nurse' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Rehab Manager' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
            ],
            'Resident Liaison' => [
                0 => 'custom_e77baa0c',
            ],
            'Social Services Director' => [
                0 => 'custom_f1b93a93',
            ],
            'Social Worker' => [
                0 => 'custom_e77baa0c',
            ],
            'Staff Development Coordinator' => [
                0 => 'custom_e77baa0c',
                1 => 'nursing_clinical',
                2 => 'rn_license',
            ],
            'Unit Clerk' => [
                0 => 'custom_e77baa0c',
            ],
        ],
];
