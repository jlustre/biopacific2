<?php

return [
    /**
     * Env fallbacks used only when no active Employee Email Mappings exist
     * for Contact HR / Technical Support roles.
     */
    'hr_notification_email' => env('PORTAL_HR_NOTIFICATION_EMAIL', env('MAIL_HR_ADDRESS', 'rdhr@biopacific.com')),

    'support_notification_email' => env('PORTAL_SUPPORT_NOTIFICATION_EMAIL', env('MAIL_SUPPORT_ADDRESS', 'support@biopacific.com')),

    'types' => [
        'hr_inquiry' => 'Contact HR',
        'support' => 'Technical Support',
    ],

    /**
     * Portal help contact roles. Map a person to a role in Employee Email Mappings.
     * responsibility: primary (most responsible) | secondary (backup / CC).
     */
    'contact_roles' => [
        'hr_inquiry' => [
            'hr_primary' => [
                'label' => 'Primary HR Contact',
                'description' => 'Most responsible for Contact HR messages.',
                'responsibility' => 'primary',
            ],
            'hr_secondary' => [
                'label' => 'Secondary HR Contact',
                'description' => 'Backup when the primary HR contact is on vacation.',
                'responsibility' => 'secondary',
            ],
        ],
        'support' => [
            'tech_primary' => [
                'label' => 'Primary Technical Support',
                'description' => 'Most responsible for portal and website support.',
                'responsibility' => 'primary',
            ],
            'tech_secondary' => [
                'label' => 'Secondary Technical Support',
                'description' => 'Backup when the primary technical contact is on vacation.',
                'responsibility' => 'secondary',
            ],
        ],
    ],

    'hr_categories' => [
        'payroll' => [
            'label' => 'Payroll & compensation',
            'description' => 'Paychecks, deductions, direct deposit, overtime, or wage questions.',
            'icon' => 'fa-money-check-dollar',
        ],
        'benefits' => [
            'label' => 'Benefits & insurance',
            'description' => 'Health, dental, vision, retirement, COBRA, or eligibility questions.',
            'icon' => 'fa-heart-pulse',
        ],
        'time_off' => [
            'label' => 'Time off & leave',
            'description' => 'PTO balances, leave requests, LOA, or attendance questions.',
            'icon' => 'fa-calendar-check',
        ],
        'onboarding' => [
            'label' => 'Onboarding & employment',
            'description' => 'New hire paperwork, job changes, transfers, or employment status.',
            'icon' => 'fa-user-plus',
        ],
        'employee_record' => [
            'label' => 'Employee record updates',
            'description' => 'Name, address, emergency contacts, department, or assignment corrections.',
            'icon' => 'fa-user-pen',
        ],
        'policies' => [
            'label' => 'Policies & handbook',
            'description' => 'Company policies, handbook questions, or workplace guidelines.',
            'icon' => 'fa-book',
        ],
        'other_hr' => [
            'label' => 'Other HR question',
            'description' => 'Anything else for the HR team — not technical or website issues.',
            'icon' => 'fa-comments',
        ],
    ],

    'support_categories' => [
        'portal_access' => [
            'label' => 'Portal login or access',
            'description' => 'Cannot sign in, locked out, password reset, or missing menu access.',
            'icon' => 'fa-right-to-bracket',
        ],
        'portal_how_to' => [
            'label' => 'How to use the portal',
            'description' => 'Need help finding a feature, completing a task, or navigating the portal.',
            'icon' => 'fa-compass',
        ],
        'website' => [
            'label' => 'Facility website issue',
            'description' => 'Public Bio-Pacific facility website content, careers pages, or display problems.',
            'icon' => 'fa-globe',
        ],
        'documents_tech' => [
            'label' => 'Documents & uploads',
            'description' => 'Cannot upload, download, or view documents in the portal.',
            'icon' => 'fa-file-circle-check',
        ],
        'technical' => [
            'label' => 'Bug or error',
            'description' => 'Broken pages, error messages, or unexpected portal behavior.',
            'icon' => 'fa-bug',
        ],
        'other_support' => [
            'label' => 'Other technical issue',
            'description' => 'Another website or portal problem that does not fit above.',
            'icon' => 'fa-life-ring',
        ],
    ],

    /**
     * Placeholder entries for future user guides / manuals on Technical Support.
     * Set `url` when a guide is ready; leave null to show as “Coming soon”.
     *
     * @var list<array{title: string, description: string, icon: string, url: ?string}>
     */
    'user_guides' => [
        [
            'title' => 'Getting started with the portal',
            'description' => 'Sign in, navigate menus, and update your profile.',
            'icon' => 'fa-rocket',
            'url' => null,
        ],
        [
            'title' => 'Documents & certifications',
            'description' => 'Upload files, track requirements, and manage licenses.',
            'icon' => 'fa-folder-open',
            'url' => null,
        ],
        [
            'title' => 'Tasks, messages & help',
            'description' => 'Work your queue and contact the right support team.',
            'icon' => 'fa-list-check',
            'url' => null,
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
