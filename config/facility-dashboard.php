<?php

/**
 * Facility Dashboard section profiles.
 *
 * - operations: full facility overview (profile, metrics, queues, directory)
 * - hr_hub: HR Management action cards only (/hr-portal)
 */
return [
    'profiles' => [
        'operations' => [
            'facility_profile' => true,
            'web_content_metrics' => true,
            'hr_operations_metrics' => true,
            'staff_action_queue' => true,
            'expiring_documents' => true,
            'assessments_due' => true,
            'staff_directory' => true,
            'facility_leadership' => true,
            'hr_management_cards' => false,
            'hr_quick_actions' => false,
        ],
        'hr_hub' => [
            'facility_profile' => false,
            'web_content_metrics' => false,
            'hr_operations_metrics' => false,
            'staff_action_queue' => false,
            'expiring_documents' => false,
            'assessments_due' => false,
            'staff_directory' => false,
            'facility_leadership' => false,
            'hr_management_cards' => true,
            'hr_quick_actions' => false,
        ],
    ],

    /** @deprecated Use profiles.operations — kept for backward compatibility */
    'sections' => [],

    'default_profile' => 'operations',

    /**
     * Facility leadership roster (highest rank first).
     *
     * source facility: facilities.{key} column, then position title fallback
     * source position: first active supervisor at facility matching position_titles
     *
     * @var list<array{key: string, label: string, abbrev: string, source: string, facility_column?: string, position_titles?: list<string>}>
     */
    'leadership_roles' => [
        ['key' => 'administrator', 'label' => 'Administrator', 'abbrev' => 'Admin', 'source' => 'facility', 'facility_column' => 'administrator', 'position_titles' => ['Administrator']],
        ['key' => 'don', 'label' => 'Director of Nursing', 'abbrev' => 'DON', 'source' => 'facility', 'facility_column' => 'don', 'position_titles' => ['Director of Nursing']],
        ['key' => 'dsd', 'label' => 'Director of Staff Development', 'abbrev' => 'DSD', 'source' => 'facility', 'facility_column' => 'dsd', 'position_titles' => ['Director of Staff Development']],
        ['key' => 'staffer', 'label' => 'Staff Development Coordinator', 'abbrev' => 'Staffer', 'source' => 'facility', 'facility_column' => 'staffer', 'position_titles' => ['Staff Development Coordinator']],
        ['key' => 'ssd', 'label' => 'Social Services Director', 'abbrev' => 'SSD', 'source' => 'position', 'position_titles' => ['Social Services Director']],
        ['key' => 'msd', 'label' => 'Medical Records Director', 'abbrev' => 'MSD', 'source' => 'position', 'position_titles' => ['Medical Records Director']],
        ['key' => 'activities', 'label' => 'Activities Director', 'abbrev' => 'Activities', 'source' => 'position', 'position_titles' => ['Activities Director']],
        ['key' => 'dietary', 'label' => 'Food Services / Dietary', 'abbrev' => 'Dietary', 'source' => 'position', 'position_titles' => ['Food Services Director', 'Dietary Manager']],
        ['key' => 'maintenance', 'label' => 'Maintenance Director', 'abbrev' => 'Maintenance', 'source' => 'position', 'position_titles' => ['Maintenance Director']],
        ['key' => 'business_office', 'label' => 'Business Office Manager', 'abbrev' => 'BOM', 'source' => 'position', 'position_titles' => ['Business Office Manager']],
        ['key' => 'housekeeping', 'label' => 'Housekeeping Supervisor', 'abbrev' => 'Housekeeping', 'source' => 'position', 'position_titles' => ['Housekeeping Supervisor']],
        ['key' => 'rehab', 'label' => 'Rehab Manager', 'abbrev' => 'Rehab', 'source' => 'position', 'position_titles' => ['Rehab Manager']],
        ['key' => 'marketing', 'label' => 'Marketing Director', 'abbrev' => 'Marketing', 'source' => 'position', 'position_titles' => ['Marketing Director']],
        ['key' => 'admissions', 'label' => 'Admissions Coordinator', 'abbrev' => 'Admissions', 'source' => 'position', 'position_titles' => ['Admissions Coordinator']],
    ],

    'expiring_window_days' => 60,
    'assessments_due_window_days' => 30,
    'staff_directory_limit' => 200,
    'expiring_list_limit' => 50,
    'action_queue_limit' => 10,
];
