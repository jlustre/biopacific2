<?php

return [
    'hr_notification_email' => env('PORTAL_HR_NOTIFICATION_EMAIL', env('MAIL_HR_ADDRESS', 'rdhr@biopacific.com')),

    'support_notification_email' => env('PORTAL_SUPPORT_NOTIFICATION_EMAIL', env('MAIL_SUPPORT_ADDRESS', 'support@biopacific.com')),

    'types' => [
        'hr_inquiry' => 'HR inquiry',
        'support' => 'Support request',
    ],

    'hr_categories' => [
        'payroll' => [
            'label' => 'Payroll & compensation',
            'description' => 'Pay checks, deductions, direct deposit, or wage questions.',
            'icon' => 'fa-money-check-dollar',
        ],
        'benefits' => [
            'label' => 'Benefits',
            'description' => 'Health, dental, vision, retirement, or eligibility questions.',
            'icon' => 'fa-heart-pulse',
        ],
        'onboarding' => [
            'label' => 'Onboarding & employment',
            'description' => 'New hire paperwork, job changes, or employment status.',
            'icon' => 'fa-user-plus',
        ],
        'account' => [
            'label' => 'Portal & account access',
            'description' => 'Login issues, profile updates, or portal permissions.',
            'icon' => 'fa-id-badge',
        ],
        'time_off' => [
            'label' => 'Time off & scheduling',
            'description' => 'PTO, leave balances, or schedule-related HR questions.',
            'icon' => 'fa-calendar-check',
        ],
        'other_hr' => [
            'label' => 'Other HR question',
            'description' => 'Anything else for the HR team to review.',
            'icon' => 'fa-comments',
        ],
    ],

    'support_categories' => [
        'portal_access' => [
            'label' => 'Portal login or access',
            'description' => 'Cannot sign in, locked out, or missing menu access.',
            'icon' => 'fa-right-to-bracket',
        ],
        'documents' => [
            'label' => 'Documents & uploads',
            'description' => 'Employee file documents, uploads, or verification issues.',
            'icon' => 'fa-file-circle-check',
        ],
        'training' => [
            'label' => 'Training & certifications',
            'description' => 'Checklists, competencies, licenses, or training records.',
            'icon' => 'fa-graduation-cap',
        ],
        'employee_record' => [
            'label' => 'Employee record correction',
            'description' => 'Name, assignment, department, or profile data corrections.',
            'icon' => 'fa-user-pen',
        ],
        'technical' => [
            'label' => 'Technical issue',
            'description' => 'Errors, broken pages, or unexpected portal behavior.',
            'icon' => 'fa-bug',
        ],
        'other_support' => [
            'label' => 'Other support need',
            'description' => 'General assistance that does not fit another category.',
            'icon' => 'fa-life-ring',
        ],
    ],

    'preferred_contact_options' => [
        'email' => 'Email',
        'phone' => 'Phone call',
        'either' => 'Email or phone — whichever is faster',
    ],

    'best_time_options' => [
        'morning' => 'Morning (8am – 12pm)',
        'afternoon' => 'Afternoon (12pm – 5pm)',
        'anytime' => 'Any time during business hours',
    ],
];
