<?php

return [
    'corporate_facility_slug' => env('CORPORATE_FACILITY_SLUG', 'bio-pacific-corporate'),

    'corporate_public_domain' => env('CORPORATE_PUBLIC_DOMAIN', 'biopacificoperational.com'),

    /*
    |--------------------------------------------------------------------------
    | Operational public site (facility website links)
    |--------------------------------------------------------------------------
    |
    | Production facility rows use short marketing domains (e.g. almadenhrc.com) that
    | resolve directly on this application (A record + cPanel alias + SSL). Staging and
    | local dev use slug URLs on staging.biopacificoperational.com or the current host.
    |
    */
    'operational_site_base' => env('OPERATIONAL_SITE_BASE'),

    'operational_site_staging_base' => env(
        'OPERATIONAL_SITE_STAGING_BASE',
        'https://staging.biopacificoperational.com'
    ),

    'operational_site_production_base' => env(
        'OPERATIONAL_SITE_PRODUCTION_BASE',
        'https://www.biopacificoperational.com'
    ),

    /*
    |--------------------------------------------------------------------------
    | Facility custom domain routes
    |--------------------------------------------------------------------------
    |
    | When a visitor uses a facility marketing domain (e.g. almadenhrc.com),
    | named routes resolve to these paths without the operational slug prefix.
    |
    */
    'facility_custom_domain_routes' => [
        'facility.public' => '/',
        'privacy.policy' => '/privacy-policy',
        'notice.privacy.practices' => '/notice-of-privacy-practices',
        'terms.service' => '/terms-of-service',
        'accessibility' => '/accessibility',
        'webmaster.contact.show' => '/webmaster/contact',
    ],

    'demo_user_emails' => [
        'rdhr' => 'rdhr@biopacific.com',
        'facility_admin' => 'facilityadmin@biopacific.com',
        'facility_dsd' => 'facilitydsd@biopacific.com',
        'don' => 'don@biopacific.com',
    ],

    'demo_facility_matchers' => [
        'pineridge' => ['facility_number' => '35494'],
        'santa_monica' => ['facility_number' => '35479'],
    ],

    'super_admin_role' => 'super-admin',

    'system_admin_roles' => ['admin', 'super-admin'],

    /** Roles that can manage public-site content and communications menus/routes */
    'web_contents_manager_roles' => 'admin|super-admin|rdhr|facility-admin|facility-dsd',

    'corporate_roles' => ['rdhr', 'regional-director'],

    'facility_manager_roles' => ['facility-admin', 'facility-dsd', 'facility-ssd', 'don'],

    /**
     * Portal roles assigned to department leadership (not facility-wide admin/dsd).
     *
     * @var list<string>
     */
    'department_head_portal_roles' => ['don', 'ssd', 'activities-director'],

    /**
     * Leadership roster keys treated as department heads for registration invites.
     * Excludes facility-wide administrator, dsd, and staffer roles.
     *
     * @var list<string>
     */
    'department_head_leadership_keys' => [
        'don',
        'ssd',
        'msd',
        'activities',
        'dietary',
        'maintenance',
        'business_office',
        'housekeeping',
        'rehab',
        'marketing',
        'admissions',
    ],

    /**
     * Optional position title => portal role overrides applied on employee self-registration.
     * Most mappings are derived from facility-dashboard.leadership_roles automatically.
     *
     * @var array<string, string>
     */
    'position_registration_roles' => [
        // 'Receptionist' => 'facility-editor',
    ],

    /**
     * Portal roles available when mapping positions for employee self-registration.
     *
     * @var list<string>
     */
    'position_registration_assignable_roles' => [
        'facility-admin',
        'facility-dsd',
        'don',
        'ssd',
        'activities-director',
        'facility-editor',
        'regular-user',
    ],

    'facility_manager_global_route_patterns' => [
        'admin.dashboard.index',
        'member.facility.dashboard',
        'admin.facility.dashboard',
        'hr-portal.*',
        'user.hr-portal',
        'admin.hr-portal.*',
        'settings.*',
        'member.*',
        'pre-employment.*',
        'employment.*',
    ],

    'admin_route_patterns' => [
        'admin.dashboard.*',
        'admin.facilities.*',
        'admin.facility.leadership*',
        'admin.facilities.leadership*',
        'admin.users.*',
        'admin.settings.*',
        'admin.roles.*',
        'admin.permissions.*',
        'admin.role-assignments.*',
        'admin.position-portal-roles.*',
        'admin.positions.*',
        'admin.departments.*',
        'admin.arbitration-templates.*',
        'admin.scheduled-report-runs.*',
        'admin.checklist-items.*',
        'admin.import-mapping-presets.*',
        'admin.import-logs.*',
        'admin.upload-types.*',
        'admin.baa-registry.*',
        'admin.hipaa-checklist.*',
        'admin.security.*',
        'admin.events.*',
        'admin.reports.*',
        'admin.scheduled-reports.*',
        'admin.blogs.*',
        'hr-portal.*',
        'admin.hr-portal.*',
    ],

    'admin_sidebar_nav' => [
        ['id' => 'admin-dashboard', 'route' => 'admin.dashboard.index', 'icon' => '⚙️', 'label' => 'Dashboard'],
        [
            'id' => 'facilities',
            'route' => 'admin.facilities.index',
            'route_is' => [
                'admin.facilities.index',
                'admin.facilities.create',
                'admin.facilities.edit',
                'admin.facilities.update',
                'admin.facilities.show',
                'admin.facilities.store',
                'admin.facilities.destroy',
            ],
            'icon' => '🏢',
            'label' => 'Facilities',
        ],
        [
            'id' => 'facility-leadership',
            'route' => 'admin.facilities.leadership.index',
            'route_is' => ['admin.facilities.leadership*', 'admin.facility.leadership*'],
            'icon' => '👔',
            'label' => 'Facility Leadership',
        ],
        ['id' => 'users', 'route' => 'admin.users.index', 'route_is' => 'admin.users.*', 'icon' => '👥', 'label' => 'Users'],
        ['id' => 'settings', 'route' => 'admin.settings.index', 'route_is' => 'admin.settings.*', 'icon' => '🔧', 'label' => 'System Settings'],
        ['id' => 'hr-portal', 'route' => 'user.hr-portal', 'route_is' => ['hr-portal.*', 'user.hr-portal', 'admin.hr-portal.*', 'admin.facility.employees*', 'admin.facility.hiring*', 'admin.facility.job_openings*', 'admin.facility.documents*', 'admin.facility.reports*'], 'icon' => '👥', 'label' => 'Employee Management'],
        ['id' => 'reports', 'route' => 'admin.reports.index', 'route_is' => ['admin.reports.*', 'admin.scheduled-reports.*'], 'icon' => '📊', 'label' => 'Reports'],
        ['id' => 'training', 'route' => 'admin.training-management.index', 'route_is' => 'admin.training-management.*', 'icon' => '🎓', 'label' => 'Training Management'],
        ['id' => 'employee-portal', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'Employee Portal'],
    ],

    'personal_portal_route_patterns' => [
        'settings.profile',
        'member.trainings',
        'member.trainings.*',
        'member.documents',
        'member.documents.*',
        'member.certifications',
        'member.certifications.*',
        'member.feedback',
        'member.feedback.*',
        'member.help',
        'member.help.*',
    ],

    'personal_portal_documents_route_patterns' => [
        'member.documents',
        'member.documents.*',
    ],

    'personal_portal_nav' => [
        [
            'id' => 'feedback',
            'route' => 'member.feedback.index',
            'route_is' => ['member.feedback', 'member.feedback.*'],
            'label' => 'Report Issue or Idea',
        ],
        [
            'id' => 'profile',
            'route' => 'settings.profile',
            'route_is' => 'settings.profile',
            'label' => 'My Profile',
        ],
        [
            'id' => 'trainings',
            'route' => 'member.trainings',
            'route_is' => ['member.trainings', 'member.trainings.*'],
            'label' => 'My Trainings',
        ],
        [
            'id' => 'documents',
            'route' => 'member.documents',
            'route_is' => ['member.documents', 'member.documents.*'],
            'label' => 'My Documents',
            'badge_class' => 'bg-amber-400 text-slate-900',
            'children' => [
                [
                    'id' => 'certifications',
                    'route' => 'member.certifications',
                    'route_is' => ['member.certifications', 'member.certifications.*'],
                    'label' => 'Licenses and Certifications',
                ],
            ],
        ],
    ],

    'corporate_dashboard_nav' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'My Dashboard'],
        ['id' => 'facility-dashboard', 'route' => 'member.facility.dashboard', 'route_is' => ['member.facility.dashboard', 'admin.facility.dashboard'], 'icon' => '🏢', 'label' => 'Facility Dashboard'],
        ['id' => 'facilities-websites', 'route' => 'member.facilities.websites', 'route_is' => 'member.facilities.websites', 'icon' => '🌐', 'label' => 'Bio-Pacific Websites'],
    ],

    'facility_dashboard_nav' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '📊', 'label' => 'My Dashboard'],
        ['id' => 'facility-dashboard', 'route' => 'member.facility.dashboard', 'route_is' => ['member.facility.dashboard', 'admin.facility.dashboard'], 'icon' => '🏢', 'label' => 'Facility Dashboard'],
        ['id' => 'facilities-websites', 'route' => 'member.facilities.websites', 'route_is' => 'member.facilities.websites', 'icon' => '🌐', 'label' => 'Bio-Pacific Websites'],
    ],

    'employee_dashboard_nav' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'My Dashboard'],
        ['id' => 'facilities-websites', 'route' => 'member.facilities.websites', 'route_is' => 'member.facilities.websites', 'icon' => '🌐', 'label' => 'Bio-Pacific Websites'],
        ['id' => 'news', 'route' => 'member.news-events.index', 'icon' => '📰', 'label' => 'News & Events'],
        ['id' => 'pre-employment', 'route' => 'pre-employment.portal', 'route_is' => 'pre-employment.*', 'icon' => '📋', 'label' => 'Pre-Employment'],
        ['id' => 'employment', 'route' => 'employment.portal', 'route_is' => 'employment.*', 'icon' => '💼', 'label' => 'My Employment'],
        ['id' => 'messages', 'route' => 'dashboard.index', 'fragment' => 'messages', 'icon' => '💬', 'label' => 'My Messages'],
    ],

    /** @deprecated Use corporate_dashboard_nav + personal_portal_nav + management groups */
    'corporate_sidebar_nav' => [],

    /** @deprecated Use facility_dashboard_nav + personal_portal_nav + management groups */
    'facility_sidebar_nav' => [],

    'admin_titles' => [
        'admin.dashboard.*' => 'Admin Dashboard',
        'hr-portal.*' => 'HR Management',
        'admin.hr-portal.*' => 'HR Management',
        'admin.users.*' => 'Users',
        'admin.settings.*' => 'System Settings',
        'admin.roles.*' => 'Roles',
        'admin.permissions.*' => 'Permissions',
        'admin.role-assignments.*' => 'Role Assignments',
        'admin.position-portal-roles.*' => 'Position Portal Roles',
        'admin.positions.*' => 'Positions',
        'admin.departments.*' => 'Departments',
        'admin.arbitration-templates.*' => 'Arbitration Templates',
        'admin.checklist-items.*' => 'Documents Management',
        'admin.baa-registry.*' => 'BAA Vendor Registry',
        'admin.hipaa-checklist.*' => 'HIPAA Checklist',
        'admin.security.*' => 'Security Monitoring',
        'admin.events.*' => 'Events',
        'admin.reports.*' => 'Reports',
        'admin.scheduled-reports.*' => 'Scheduled Reports',
        'admin.upload-types.*' => 'Documents Management',
    ],

    'corporate_titles' => [
        'admin.positions.*' => 'Positions',
        'admin.reports.*' => 'Reports',
        'admin.scheduled-reports.*' => 'Reports',
        'user.hr-portal' => 'HR Management',
        'hr-portal.*' => 'HR Management',
    ],

    'admin_active_map' => [
        'admin.dashboard.*' => 'admin-dashboard',
        'admin.facilities.leadership*' => 'facility-leadership',
        'admin.facility.leadership*' => 'facility-leadership',
        'admin.facilities.index' => 'facilities',
        'admin.facilities.create' => 'facilities',
        'admin.facilities.edit' => 'facilities',
        'admin.facilities.show' => 'facilities',
        'admin.users.*' => 'users',
        'admin.settings.*' => 'settings',
        'hr-portal.*' => 'hr-portal',
        'user.hr-portal' => 'hr-portal',
        'admin.hr-portal.*' => 'hr-portal',
        'admin.reports.*' => 'reports',
        'admin.scheduled-reports.*' => 'reports',
        'admin.training-management.*' => 'training',
        'admin.roles.*' => 'roles',
        'admin.permissions.*' => 'roles',
        'admin.role-assignments.*' => 'roles',
        'admin.position-portal-roles.*' => 'roles',
        'admin.positions.*' => 'positions',
        'admin.departments.*' => 'roles',
        'admin.arbitration-templates.*' => 'admin-arbitration-templates',
        'admin.checklist-items.*' => 'admin-upload-types',
        'admin.import-mapping-presets.*' => 'admin-import-mapping-presets',
        'admin.import-logs.*' => 'admin-import-logs',
        'admin.upload-types.*' => 'admin-upload-types',
        'admin.baa-registry.*' => 'admin-baa',
        'admin.hipaa-checklist.*' => 'admin-hipaa',
        'admin.security.*' => 'admin-security',
        'admin.events.*' => 'admin-events',
        'admin.reports.*' => 'admin-reports',
        'admin.scheduled-reports.*' => 'admin-scheduled-reports',
    ],

    'corporate_active_map' => [
        'dashboard.index' => 'dashboard',
        'member.facilities.websites' => 'facilities-websites',
        'settings.profile' => 'profile',
        'member.trainings' => 'trainings',
        'member.trainings.*' => 'trainings',
        'member.documents' => 'documents',
        'member.documents.*' => 'documents',
        'member.certifications' => 'certifications',
        'member.certifications.*' => 'certifications',
        'member.feedback' => 'feedback',
        'member.feedback.*' => 'feedback',
        'member.help' => 'help',
        'member.help.*' => 'help',
        'member.help.hr' => 'help-hr',
        'member.help.hr.*' => 'help-hr',
        'member.help.support' => 'help-support',
        'member.help.support.*' => 'help-support',
        'admin.positions.*' => 'facility-positions-management',
        'admin.reports.*' => 'reports',
        'admin.scheduled-reports.*' => 'reports',
        'user.hr-portal' => 'facility-hr-portal',
        'hr-portal.*' => 'facility-hr-portal',
    ],

    'employee_active_map' => [
        'dashboard.index' => 'dashboard',
        'member.facilities.websites' => 'facilities-websites',
        'member.news-events.index' => 'news',
        'member.news-events.*' => 'news',
        'pre-employment.*' => 'pre-employment',
        'employment.*' => 'employment',
        'settings.profile' => 'profile',
        'member.trainings' => 'trainings',
        'member.trainings.*' => 'trainings',
        'member.documents' => 'documents',
        'member.documents.*' => 'documents',
        'member.certifications' => 'certifications',
        'member.certifications.*' => 'certifications',
        'member.feedback' => 'feedback',
        'member.feedback.*' => 'feedback',
        'member.help' => 'help',
        'member.help.*' => 'help',
        'member.help.hr' => 'help-hr',
        'member.help.hr.*' => 'help-hr',
        'member.help.support' => 'help-support',
        'member.help.support.*' => 'help-support',
    ],

    'facility_active_map' => [
        'member.facility.dashboard' => 'facility-dashboard',
        'admin.facility.dashboard' => 'facility-dashboard',
        'admin.facility.leadership*' => 'facility-dashboard',
        'admin.facilities.leadership*' => 'facility-dashboard',
        'member.facilities.websites' => 'facilities-websites',
        'dashboard.index' => 'dashboard',
        'settings.profile' => 'profile',
        'member.documents' => 'documents',
        'user.hr-portal' => 'hr-portal',
        'hr-portal.*' => 'hr-portal',
        'member.trainings' => 'trainings',
        'member.certifications' => 'certifications',
    ],

    /** @deprecated Use employee_dashboard_nav + personal_portal_nav */
    'sidebar_nav' => [],

    'mobile_nav' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'Home'],
        ['id' => 'news', 'route' => 'member.news-events.index', 'icon' => '📰', 'label' => 'News'],
        ['id' => 'documents', 'route' => 'member.documents', 'icon' => '📄', 'label' => 'Docs'],
            ['id' => 'certifications', 'route' => 'member.certifications', 'icon' => '🏅', 'label' => 'Licenses'],
        ['id' => 'trainings', 'route' => 'member.trainings', 'icon' => '🎓', 'label' => 'Training'],
        ['id' => 'profile', 'route' => 'settings.profile', 'icon' => '👤', 'label' => 'Profile'],
    ],

    'facility_management_route_patterns' => [
        'user.hr-portal',
        'admin.facility.dashboard',
        'admin.facility.job_openings*',
        'admin.facility.hiring',
        'admin.facility.employees*',
        'admin.employees.*',
        'admin.facility.documents*',
        'admin.facility.reports*',
        'admin.facility.leadership*',
        'admin.facilities.leadership*',
        'admin.reports.*',
        'admin.scheduled-reports.*',
        'admin.scheduled-report-runs.*',
        'admin.hr-portal.reports',
        'admin.facility.uploads*',
        'admin.facility.pre-employment*',
        'admin.facility.document.*',
        'admin.facilities.webcontents.*',
        'facilities.webcontents.*',
        'facilities.news-events.*',
        'admin.galleries.*',
        'admin.news.*',
        'admin.services.*',
        'admin.blogs.*',
        'admin.tour-requests.*',
        'admin.inquiries.*',
        'admin.webmaster.contacts.*',
        'admin.portal-help-requests.*',
        'admin.job-applications.*',
        'admin.email-recipients.*',
        'admin.email-templates.*',
        'admin.communications.employee-email-mappings',
        'admin.scheduled-reports.*',
        'admin.upload-types.*',
        'admin.positions.*',
        'admin.training-management.*',
    ],

    'facility_management_web_route_patterns' => [
        'admin.facilities.webcontents.testimonials',
        'admin.facilities.webcontents.faqs',
        'admin.facilities.webcontents.blogs',
        'admin.facilities.webcontents.careers*',
        'facilities.webcontents.testimonials',
        'facilities.webcontents.faqs',
        'facilities.webcontents.blogs',
        'facilities.webcontents.news-events',
        'facilities.news-events.*',
        'admin.galleries.*',
        'admin.news.*',
        'admin.services.*',
        'admin.blogs.*',
    ],

    'facility_management_comm_route_patterns' => [
        'admin.tour-requests.*',
        'admin.inquiries.*',
        'admin.webmaster.contacts.*',
        'admin.portal-help-requests.*',
        'admin.job-applications.*',
        'admin.email-recipients.*',
        'admin.email-templates.*',
        'admin.scheduled-reports.*',
        'admin.communications.employee-email-mappings',
    ],

    'facility_management_titles' => [
        'admin.dashboard.index' => 'HR Management',
        'hr-portal.*' => 'HR Management',
        'user.hr-portal' => 'HR Management',
        'admin.facility.dashboard' => 'Facility Dashboard',
        'admin.facility.job_openings*' => 'Job Listings',
        'admin.facility.hiring' => 'Hiring',
        'admin.facility.employees*' => 'Employees',
        'admin.employees.*' => 'Employee',
        'admin.facility.documents*' => 'Documents',
        'admin.facility.uploads*' => 'Documents',
        'admin.facility.reports*' => 'Reports',
        'admin.reports.*' => 'Reports Management',
        'admin.scheduled-reports.*' => 'Scheduled Reports',
        'admin.scheduled-report-runs.*' => 'Report Runs',
        'admin.hr-portal.reports' => 'Reports',
        'admin.facilities.webcontents.testimonials' => 'Testimonials',
        'admin.facilities.webcontents.faqs' => 'FAQs',
        'admin.facilities.webcontents.blogs' => 'Blogs',
        'admin.facilities.webcontents.careers*' => 'Careers',
        'admin.galleries.*' => 'Galleries',
        'admin.news.*' => 'News',
        'admin.services.*' => 'Services',
        'admin.tour-requests.*' => 'Tour Requests',
        'admin.inquiries.*' => 'General Inquiries',
        'admin.webmaster.contacts.*' => 'Webmaster Issues',
        'admin.portal-help-requests.*' => 'Portal Help Requests',
        'admin.job-applications.*' => 'Job Applications',
        'admin.email-recipients.*' => 'Email Recipients',
        'admin.email-templates.*' => 'Email Templates',
        'admin.communications.employee-email-mappings' => 'Employee Email Mappings',
        'admin.upload-types.*' => 'Documents Management',
        'admin.positions.*' => 'Positions Management',
        'admin.training-management.*' => 'Training Management',
    ],

    'facility_management_active_map' => [
        'admin.dashboard.index' => 'facility-hr-portal',
        'hr-portal.*' => 'facility-hr-portal',
        'user.hr-portal' => 'facility-hr-portal',
        'admin.facility.dashboard' => 'facility-dashboard',
        'member.facility.dashboard' => 'facility-dashboard',
        'admin.facility.employees*' => 'facility-hr-portal',
        'admin.employees.*' => 'facility-hr-portal',
        'admin.facility.reports*' => 'facility-hr-portal',
        'admin.reports.*' => 'facility-hr-portal',
        'admin.scheduled-reports.*' => 'facility-hr-portal',
        'admin.scheduled-report-runs.*' => 'facility-hr-portal',
        'admin.hr-portal.reports' => 'facility-hr-portal',
        'admin.facilities.webcontents.testimonials' => 'facility-testimonials',
        'admin.facilities.webcontents.faqs' => 'facility-faqs',
        'admin.facilities.webcontents.blogs' => 'facility-blogs',
        'admin.facilities.webcontents.careers*' => 'facility-careers',
        'admin.galleries.*' => 'facility-galleries',
        'admin.news.*' => 'facility-news',
        'admin.services.*' => 'facility-services',
        'admin.tour-requests.*' => 'facility-tour-requests',
        'admin.inquiries.*' => 'facility-inquiries',
        'admin.webmaster.contacts.*' => 'facility-webmaster-issues',
        'admin.portal-help-requests.*' => 'facility-portal-help-requests',
        'admin.job-applications.*' => 'facility-job-applications',
        'admin.email-recipients.*' => 'facility-email-recipients',
        'admin.email-templates.*' => 'facility-email-templates',
        'admin.communications.employee-email-mappings' => 'facility-email-mappings',
        'admin.upload-types.*' => 'facility-documents-management',
        'admin.positions.*' => 'facility-positions-management',
        'admin.training-management.*' => 'facility-training-management',
    ],
];
