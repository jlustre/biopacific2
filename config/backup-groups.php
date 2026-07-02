<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Structural / master data tables
    |--------------------------------------------------------------------------
    | System configuration, RBAC, lookups, and reference data.
    */
    'structural_tables' => [
        'users',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'settings',
        'states',
        'optionstypes',
        'selectoptions',
        'departments',
        'positions',
        'position_portal_role_mappings',
        'position_upload_type_requirements',
        'upload_types',
        'checklist_items',
        'doc_types',
        'report_categories',
        'reports',
        'email_templates',
        'email_recipients',
        'color_schemes',
        'layout_templates',
        'layout_sections',
        'services',
        'import_mapping_presets',
        'scheduled_report_templates',
        'bp_bargaining_units',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transactional / operational data tables
    |--------------------------------------------------------------------------
    */
    'transactional_tables' => [
        'facilities',
        'facility_values',
        'facility_user',
        'facility_service',
        'facility_faq',
        'facility_news',
        'facility_leadership_assignments',
        'global_shutdowns',
        'bp_employees',
        'bp_emp_job_data',
        'bp_emp_phones',
        'bp_emp_addresses',
        'bp_emp_credentials',
        'bp_emp_compensation',
        'bp_emp_health_screenings',
        'bp_emp_tax_data',
        'bp_emp_documents',
        'bp_emp_checklists',
        'employee_checklist',
        'employee_email_mappings',
        'uploads',
        'employee_documents',
        'job_openings',
        'job_descriptions',
        'job_description_templates',
        'job_applications',
        'pre_employment_applications',
        'reference_checks',
        'registration_codes',
        'hiring_activity_logs',
        'tour_requests',
        'inquiries',
        'webmaster_contacts',
        'webmaster_contact_comments',
        'portal_help_requests',
        'news',
        'blogs',
        'blog_facility',
        'events',
        'testimonials',
        'faqs',
        'gallery_images',
        'web_contents',
        'job_batches',
        'audit_logs',
        'secure_access_logs',
        'import_logs',
        'import_log_changes',
        'scheduled_reports',
        'scheduled_report_runs',
        'employee_assessment_periods',
        'employee_performance_assessments',
        'employee_performance_items',
        'employee_performance_section_comments',
        'employee_competency_assessments',
        'employee_competency_items',
        'employee_assessment_item_entries',
        'skills_competency',
        'ln_competency_skill_responses',
        'ln_competency_skill_summaries',
        'member_emergency_contacts',
        'member_profile_expiring_items',
        'member_profile_recognitions',
        'baa_vendors',
        'incident_contacts',
        'facility_arbitration_documents',
        'applicant_arbitration_documents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extensible backup sections (UI checkboxes)
    |--------------------------------------------------------------------------
    */
    'sections' => [
        'rbac' => [
            'label' => 'Users, Roles & Permissions',
            'description' => 'Authentication, authorization, and role assignments.',
            'tables' => [
                'users',
                'permissions',
                'roles',
                'model_has_permissions',
                'model_has_roles',
                'role_has_permissions',
            ],
        ],
        'system_settings' => [
            'label' => 'System Settings & Lookups',
            'description' => 'Application settings, states, and dropdown reference data.',
            'tables' => [
                'settings',
                'states',
                'optionstypes',
                'selectoptions',
            ],
        ],
        'hr_structure' => [
            'label' => 'HR Structure',
            'description' => 'Departments, positions, upload types, and checklist definitions.',
            'tables' => [
                'departments',
                'positions',
                'position_portal_role_mappings',
                'position_upload_type_requirements',
                'upload_types',
                'checklist_items',
                'doc_types',
                'bp_bargaining_units',
            ],
        ],
        'communications_config' => [
            'label' => 'Communications & Reports Config',
            'description' => 'Email templates, report definitions, and import presets.',
            'tables' => [
                'email_templates',
                'email_recipients',
                'report_categories',
                'reports',
                'import_mapping_presets',
                'scheduled_report_templates',
            ],
        ],
        'cms_config' => [
            'label' => 'CMS & Layout Config',
            'description' => 'Color schemes, layout templates, and service catalog.',
            'tables' => [
                'color_schemes',
                'layout_templates',
                'layout_sections',
                'services',
            ],
        ],
        'facilities' => [
            'label' => 'Facilities & Assignments',
            'description' => 'Facility records, leadership, and facility relationships.',
            'tables' => [
                'facilities',
                'facility_values',
                'facility_user',
                'facility_service',
                'facility_faq',
                'facility_news',
                'facility_leadership_assignments',
                'global_shutdowns',
            ],
        ],
        'hr_data' => [
            'label' => 'HR & Employee Data',
            'description' => 'Employees, assignments, credentials, and HR records.',
            'tables' => [
                'bp_employees',
                'bp_emp_job_data',
                'bp_emp_phones',
                'bp_emp_addresses',
                'bp_emp_credentials',
                'bp_emp_compensation',
                'bp_emp_health_screenings',
                'bp_emp_tax_data',
                'bp_emp_documents',
                'bp_emp_checklists',
                'employee_checklist',
                'employee_email_mappings',
            ],
        ],
        'hiring_crm' => [
            'label' => 'Hiring & CRM',
            'description' => 'Job openings, applications, tours, inquiries, and portal requests.',
            'tables' => [
                'job_openings',
                'job_descriptions',
                'job_description_templates',
                'job_applications',
                'pre_employment_applications',
                'reference_checks',
                'registration_codes',
                'hiring_activity_logs',
                'tour_requests',
                'inquiries',
                'webmaster_contacts',
                'webmaster_contact_comments',
                'portal_help_requests',
            ],
        ],
        'content' => [
            'label' => 'Content & Media Records',
            'description' => 'News, blogs, events, testimonials, FAQs, and galleries.',
            'tables' => [
                'news',
                'blogs',
                'blog_facility',
                'events',
                'testimonials',
                'faqs',
                'gallery_images',
                'web_contents',
            ],
        ],
        'documents' => [
            'label' => 'Uploaded Document References',
            'description' => 'Upload metadata and employee document records.',
            'tables' => [
                'uploads',
                'employee_documents',
                'facility_arbitration_documents',
                'applicant_arbitration_documents',
            ],
        ],
        'performance' => [
            'label' => 'Performance & Competency',
            'description' => 'Assessments, competency checklists, and performance records.',
            'tables' => [
                'employee_assessment_periods',
                'employee_performance_assessments',
                'employee_performance_items',
                'employee_performance_section_comments',
                'employee_competency_assessments',
                'employee_competency_items',
                'employee_assessment_item_entries',
                'skills_competency',
                'ln_competency_skill_responses',
                'ln_competency_skill_summaries',
            ],
        ],
        'logs' => [
            'label' => 'System & Activity Logs',
            'description' => 'Audit logs, import logs, and secure access logs.',
            'tables' => [
                'audit_logs',
                'secure_access_logs',
                'import_logs',
                'import_log_changes',
                'scheduled_reports',
                'scheduled_report_runs',
            ],
        ],
        'member_portal' => [
            'label' => 'Member Portal Data',
            'description' => 'Emergency contacts, recognitions, and profile expiring items.',
            'tables' => [
                'member_emergency_contacts',
                'member_profile_expiring_items',
                'member_profile_recognitions',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage folders for file backups
    |--------------------------------------------------------------------------
    | Paths are relative to each disk root.
    */
    'file_paths' => [
        'public' => [
            'label' => 'Public uploads',
            'disk' => 'public',
            'paths' => [
                'employee_documents',
                'avatars',
                'gallery',
                'resumes',
                'arbitration',
            ],
        ],
        'private' => [
            'label' => 'Private documents',
            'disk' => 'local',
            'paths' => [
                'employee_documents',
                'pre-employment',
            ],
        ],
    ],

];
