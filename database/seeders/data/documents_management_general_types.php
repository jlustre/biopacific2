<?php

/**
 * General document types for Documents Management (not PART A–D catalog rows).
 *
 * Duplicate concepts that live on PART A–D (I-9, W-4, CPR, etc.) were removed
 * so migrate:fresh --seed does not recreate a second name for the same document.
 *
 * @return list<array{
 *     name: string,
 *     description: string,
 *     requires_expiry: bool,
 *     is_license_or_certification: bool
 * }>
 */
return [
    [
        'name' => 'Abuse Reporting Acknowledgment (SOC 341)',
        'description' => 'Signed acknowledgment of abuse reporting responsibilities (SOC 341)',
        'requires_expiry' => false,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Annual In-Service Training',
        'description' => 'Proof of annual in-service training',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Annual Influenza Vaccination',
        'description' => 'Proof of annual flu vaccination',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'COVID-19 Vaccination Record',
        'description' => 'COVID-19 vaccination proof',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Criminal Record Statement (LIC 508)',
        'description' => 'LIC 508 criminal record statement',
        'requires_expiry' => false,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Dementia Care Training Record',
        'description' => 'Proof of dementia care training',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'First Aid Certification',
        'description' => 'First Aid certificate',
        'requires_expiry' => true,
        'is_license_or_certification' => true,
    ],
    [
        'name' => 'Health Screening Declaration (Physician Statement)',
        'description' => 'Physician statement or health screening declaration',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Hepatitis B Vaccination Record or Declination',
        'description' => 'Proof of Hepatitis B vaccination or declination',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'HIPAA Training Certificate',
        'description' => 'Proof of HIPAA training',
        'requires_expiry' => true,
        'is_license_or_certification' => true,
    ],
    [
        'name' => 'LGBTI Cultural Competency Training Certificate',
        'description' => 'Proof of LGBTI cultural competency training',
        'requires_expiry' => true,
        'is_license_or_certification' => true,
    ],
    [
        'name' => 'MMR & Varicella Immunity Records',
        'description' => 'Proof of MMR and Varicella immunity',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Physical Exam',
        'description' => 'Employee physical examination record',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Proof of Age (18+)',
        'description' => 'Proof employee is 18 years or older',
        'requires_expiry' => false,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Resume',
        'description' => 'Applicant resume',
        'requires_expiry' => false,
        'is_license_or_certification' => false,
    ],
    [
        'name' => 'Sexual Harassment Training Certificate',
        'description' => 'Proof of sexual harassment prevention training',
        'requires_expiry' => true,
        'is_license_or_certification' => true,
    ],
    [
        'name' => 'TB Test Result',
        'description' => 'Tuberculosis test result',
        'requires_expiry' => true,
        'is_license_or_certification' => false,
    ],
];
