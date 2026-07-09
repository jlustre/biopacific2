<?php

/**
 * Admin UI presets for bulk-assigning general document requirements to positions.
 *
 * Document sets mirror database/seeders/data/position_document_requirements.php.
 * Position groups are shortcuts for common cohorts (all staff, nurses, CNAs, etc.).
 */
return [
    'document_sets' => [
        'all_staff' => [
            'label' => 'All staff (global)',
            'description' => 'Baseline documents required for every employee (I-9, W-4, handbook, TB test, etc.).',
            'icon' => 'fa-users',
        ],
        'annual_compliance' => [
            'label' => 'Annual compliance',
            'description' => 'Yearly training and vaccination records.',
            'icon' => 'fa-calendar-check',
        ],
        'nursing_clinical' => [
            'label' => 'Nursing & clinical',
            'description' => 'CPR, dementia care, and related clinical credentials.',
            'icon' => 'fa-stethoscope',
        ],
        'rn_license' => [
            'label' => 'RN license',
            'description' => 'Registered Nurse license documentation.',
            'icon' => 'fa-id-card',
        ],
        'lvn_license' => [
            'label' => 'LVN license',
            'description' => 'Licensed Vocational Nurse license documentation.',
            'icon' => 'fa-id-card',
        ],
        'leadership' => [
            'label' => 'Leadership',
            'description' => 'Additional requirements for management roles.',
            'icon' => 'fa-user-tie',
        ],
    ],

    'position_groups' => [
        'all_active' => [
            'label' => 'All active positions',
            'description' => 'Every position marked active in the system.',
        ],
        'nursing_all' => [
            'label' => 'All nursing department',
            'description' => 'Every position in the Nursing department.',
            'department_names' => ['Nursing'],
        ],
        'nursing_rn' => [
            'label' => 'Licensed RNs',
            'description' => 'Registered Nurse, Charge Nurse, DON, IP Nurse, MDS, Staff Development Coordinator, etc.',
            'position_titles' => [
                'Registered Nurse',
                'Charge Nurse',
                'Director of Nursing',
                'IP Nurse',
                'Licensed Nurse',
                'MDS Coordinator',
                'Staff Development Coordinator',
            ],
        ],
        'nursing_lvn' => [
            'label' => 'LVNs',
            'description' => 'Licensed Vocational Nurse positions.',
            'position_titles' => ['Licensed Vocational Nurse'],
        ],
        'nursing_cna' => [
            'label' => 'CNAs & nursing assistants',
            'description' => 'Certified Nursing Assistant and Nursing Assistant roles.',
            'position_titles' => [
                'Certified Nursing Assistant',
                'Nursing Assistant',
            ],
        ],
        'rehab_clinical' => [
            'label' => 'Rehab & therapy',
            'description' => 'OT, PT, and rehab management positions.',
            'position_titles' => [
                'Rehab Manager',
                'Occupational Therapist',
                'Physical Therapist',
                'OT/PT Assistant',
            ],
        ],
        'leadership' => [
            'label' => 'Leadership & administration',
            'description' => 'Directors, administrators, and department heads.',
            'position_titles' => [
                'Administrator',
                'Director of Staff Development',
                'Business Office Manager',
                'Medical Records Director',
                'Marketing Director',
                'Food Services Director',
                'Social Services Director',
            ],
        ],
    ],
];
