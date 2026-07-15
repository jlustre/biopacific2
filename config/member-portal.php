<?php

return [
    'brand_name' => env('MEMBER_PORTAL_BRAND_NAME', 'Bio-Pacific'),

    'brand_tagline' => env('MEMBER_PORTAL_BRAND_TAGLINE', 'Facility'),

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

    /** Roles that can manage public-site content and web communications menus/routes */
    'web_contents_manager_roles' => 'admin|super-admin|rdhr|facility-admin|facility-dsd',

    'corporate_roles' => ['rdhr', 'regional-director'],

    'facility_manager_roles' => ['facility-admin', 'facility-dsd', 'facility-ssd', 'don'],

    /** Roles that can open Documents Management (upload types, employee file items, position requirements). */
    'documents_management_roles' => ['admin', 'super-admin', 'rdhr', 'facility-admin', 'facility-dsd', 'don'],

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
        'admin.training-items.*',
        'admin.baa-registry.*',
        'admin.hipaa-checklist.*',
        'admin.backups.*',
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
        ['id' => 'reports', 'route' => 'admin.reports.index', 'route_is' => ['admin.reports.*', 'admin.scheduled-reports.*'], 'icon' => '📊', 'label' => 'Reports Management'],
        ['id' => 'scheduled-report-runs', 'route' => 'admin.scheduled-report-runs.index', 'route_is' => 'admin.scheduled-report-runs.*', 'icon' => '📋', 'label' => 'Scheduled Report Runs'],
        ['id' => 'training', 'route' => 'admin.training-management.index', 'route_is' => 'admin.training-management.*', 'icon' => '🎓', 'label' => 'Training Management'],
        ['id' => 'employee-portal', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'Employee Portal'],
    ],

    'personal_portal_route_patterns' => [
        'settings.profile',
        'member.tasks',
        'member.tasks.*',
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

    'nav_purpose_groups' => [
        [
            'id' => 'personal',
            'label' => 'Personal',
            'icon' => '👤',
            'gate' => 'everyone',
            'items' => [
                ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'My Dashboard'],
                ['id' => 'tasks', 'route' => 'member.tasks', 'route_is' => ['member.tasks', 'member.tasks.*'], 'icon' => '✅', 'label' => 'My Tasks', 'badge' => 'tasks'],
                ['id' => 'messages', 'route' => 'member.messages', 'route_is' => ['member.messages', 'member.messages.*'], 'icon' => '💬', 'label' => 'My Messages', 'badge' => 'messages'],
                ['id' => 'profile', 'route' => 'settings.profile', 'route_is' => ['settings.profile', 'settings.profile.*'], 'icon' => '🪪', 'label' => 'My Profile'],
                ['id' => 'employment', 'route' => 'employment.portal', 'route_is' => 'employment.*', 'icon' => '💼', 'label' => 'My Employment'],
                ['id' => 'pre-employment', 'route' => 'pre-employment.portal', 'route_is' => 'pre-employment.*', 'icon' => '📋', 'label' => 'Pre-Employment'],
                ['id' => 'documents', 'route' => 'member.documents', 'route_is' => ['member.documents', 'member.documents.*'], 'icon' => '📄', 'label' => 'My Documents', 'badge' => 'documents'],
                ['id' => 'checklists', 'route' => 'member.checklists', 'route_is' => ['member.checklists', 'member.checklists.*', 'member.trainings', 'member.trainings.*'], 'icon' => '📋', 'label' => 'My Checklists'],
                ['id' => 'certifications', 'route' => 'member.certifications', 'route_is' => ['member.certifications', 'member.certifications.*'], 'icon' => '🪪', 'label' => 'My Credentials'],
            ],
        ],
        [
            'id' => 'facility',
            'label' => 'Facility',
            'icon' => '🏢',
            'gate' => 'facility_member',
            'items' => [
                ['id' => 'facility-dashboard', 'route' => 'member.facility.dashboard', 'route_is' => ['member.facility.dashboard', 'admin.facility.dashboard'], 'icon' => '📊', 'label' => 'Facility Dashboard', 'gate' => 'facility_dashboard'],
                ['id' => 'facility-leadership', 'route' => 'admin.facilities.leadership.index', 'route_is' => ['admin.facilities.leadership*', 'admin.facility.leadership*'], 'icon' => '👔', 'label' => 'Facility Leadership', 'gate' => 'facility_ops'],
                ['id' => 'employee-management', 'route' => 'user.hr-portal', 'route_is' => ['user.hr-portal', 'hr-portal.*', 'admin.hr-portal.*', 'admin.facility.hiring*', 'admin.facility.job_openings*'], 'active_also' => [['route_is' => ['admin.facility.employees', 'admin.facility.employees*'], 'unless_query' => ['checklist' => ['partG', 'partF', 'partH']]], ['route_is' => ['admin.employees.edit', 'admin.employees.update', 'admin.employees.*'], 'unless_query' => ['checklist_tab' => ['partG', 'partF', 'partH']]]], 'icon' => '👥', 'label' => 'Employee Management', 'gate' => 'hr_portal'],
                ['id' => 'facility-positions', 'route' => 'admin.positions.index', 'route_is' => 'admin.positions.*', 'icon' => '📌', 'label' => 'Positions', 'gate' => 'positions'],
                ['id' => 'facility-trainings', 'route' => 'admin.facility.trainings', 'route_is' => ['admin.facility.trainings'], 'active_also' => [['route_is' => ['admin.facility.employees', 'admin.facility.employees*'], 'query' => ['checklist' => 'partH']], ['route_is' => ['admin.employees.edit', 'admin.employees.update'], 'query' => ['checklist_tab' => 'partH']]], 'icon' => '🎓', 'label' => 'Trainings', 'gate' => 'training_mgmt'],
                ['id' => 'facility-documents', 'route' => 'admin.facility.documents.entry', 'route_is' => ['admin.facility.documents.entry'], 'active_also' => [['route_is' => ['admin.facility.documents', 'admin.facility.uploads.*']]], 'icon' => '📁', 'label' => 'Documents', 'gate' => 'documents_mgmt'],
                ['id' => 'facility-competencies', 'route' => 'admin.facility.competencies', 'route_is' => ['admin.facility.competencies'], 'active_also' => [['route_is' => ['admin.facility.employees', 'admin.facility.employees*'], 'query' => ['checklist' => 'partG']], ['route_is' => ['admin.employees.edit', 'admin.employees.update'], 'query' => ['checklist_tab' => 'partG']]], 'icon' => '🧩', 'label' => 'Competencies', 'gate' => 'hr_portal'],
                ['id' => 'facility-performance', 'route' => 'admin.facility.performance', 'route_is' => ['admin.facility.performance'], 'active_also' => [['route_is' => ['admin.facility.employees', 'admin.facility.employees*'], 'query' => ['checklist' => 'partF']], ['route_is' => ['admin.employees.edit', 'admin.employees.update'], 'query' => ['checklist_tab' => 'partF']]], 'icon' => '📈', 'label' => 'Performance', 'gate' => 'hr_portal'],
                ['id' => 'facility-reports', 'route' => 'admin.reports.index', 'route_is' => ['admin.reports.*', 'admin.scheduled-reports.*'], 'icon' => '📊', 'label' => 'Reports Management', 'gate' => 'reports'],
                ['id' => 'facility-report-runs', 'route' => 'admin.scheduled-report-runs.index', 'route_is' => 'admin.scheduled-report-runs.*', 'icon' => '📋', 'label' => 'Scheduled Report Runs', 'gate' => 'reports'],
                ['id' => 'facility-arbitration', 'route' => 'admin.arbitration-templates.index', 'route_is' => 'admin.arbitration-templates.*', 'icon' => '📄', 'label' => 'Arbitration Templates', 'gate' => 'system_admin'],
                ['id' => 'facility-news', 'route' => 'member.news-events.index', 'route_is' => ['member.news-events.*'], 'icon' => '📰', 'label' => 'News/Events', 'gate' => 'facility_member'],
                ['id' => 'facility-galleries', 'route' => 'member.galleries.index', 'route_is' => ['member.galleries.*'], 'icon' => '🖼️', 'label' => 'Photo Galleries', 'gate' => 'facility_member'],
            ],
        ],
        [
            'id' => 'company',
            'label' => 'Company',
            'icon' => '🌐',
            'gate' => 'everyone',
            'items' => [
                ['id' => 'admin-dashboard', 'route' => 'admin.dashboard.index', 'route_is' => 'admin.dashboard.*', 'icon' => '⚙️', 'label' => 'Admin Dashboard', 'gate' => 'system_admin'],
                ['id' => 'facilities', 'route' => 'admin.facilities.index', 'route_is' => ['admin.facilities.index', 'admin.facilities.create', 'admin.facilities.edit', 'admin.facilities.update', 'admin.facilities.show', 'admin.facilities.store', 'admin.facilities.destroy'], 'icon' => '🏢', 'label' => 'Facilities', 'gate' => 'system_admin'],
                ['id' => 'facilities-websites', 'route' => 'member.facilities.websites', 'route_is' => ['member.facilities.websites', 'member.facilities.websites.*'], 'icon' => '🌍', 'label' => 'Bio-Pacific Websites'],
                ['id' => 'company-leadership', 'route' => 'admin.facilities.leadership.index', 'route_is' => ['admin.facilities.leadership*', 'admin.facility.leadership*'], 'icon' => '🏛️', 'label' => 'Leadership'],
                ['id' => 'company-news', 'route' => 'member.news-events.index', 'route_is' => ['member.news-events.*', 'admin.news.*'], 'icon' => '📰', 'label' => 'News & Events'],
                ['id' => 'company-galleries', 'route' => 'member.galleries.index', 'route_is' => ['member.galleries.*'], 'icon' => '🖼️', 'label' => 'Photo Galleries'],
            ],
        ],
        [
            'id' => 'web_contents',
            'label' => 'Web Contents',
            'icon' => '🧩',
            'gate' => 'web_contents',
            'items' => [
                ['id' => 'web-testimonials', 'route' => 'admin.facilities.webcontents.testimonials', 'route_is' => ['admin.facilities.webcontents.testimonials', 'facilities.webcontents.testimonials*'], 'label' => 'Testimonials'],
                ['id' => 'web-faqs', 'route' => 'admin.facilities.webcontents.faqs', 'route_is' => ['admin.facilities.webcontents.faqs', 'facilities.webcontents.faqs*'], 'label' => 'FAQs'],
                ['id' => 'web-galleries', 'route' => 'admin.galleries.index', 'route_is' => 'admin.galleries.*', 'label' => 'Galleries'],
                ['id' => 'web-news', 'route' => 'admin.news.index', 'route_is' => ['admin.news.*', 'facilities.news-events.*', 'facilities.webcontents.news-events'], 'label' => 'News'],
                ['id' => 'web-blogs', 'route' => 'admin.facilities.webcontents.blogs', 'route_is' => ['admin.facilities.webcontents.blogs', 'facilities.webcontents.blogs', 'admin.blogs.*'], 'label' => 'Blogs'],
                ['id' => 'web-careers', 'route' => 'admin.facilities.webcontents.careers', 'route_is' => 'admin.facilities.webcontents.careers*', 'label' => 'Careers', 'careers' => true],
                ['id' => 'web-services', 'route' => 'admin.services.index', 'route_is' => 'admin.services.*', 'label' => 'Services'],
            ],
        ],
        [
            'id' => 'web_communications',
            'label' => 'Web Communications',
            'icon' => '💬',
            'gate' => 'web_contents',
            'items' => [
                ['id' => 'comm-tours', 'route' => 'admin.tour-requests.index', 'route_is' => 'admin.tour-requests.*', 'label' => 'Tour Requests'],
                ['id' => 'comm-inquiries', 'route' => 'admin.inquiries.index', 'route_is' => 'admin.inquiries.*', 'label' => 'General Inquiries'],
                ['id' => 'comm-webmaster', 'route' => 'admin.webmaster.contacts.index', 'route_is' => 'admin.webmaster.contacts.*', 'label' => 'Webmaster Issues', 'gate' => 'system_admin', 'badge' => 'webmaster'],
                ['id' => 'comm-portal-help', 'route' => 'admin.portal-help-requests.index', 'route_is' => 'admin.portal-help-requests.*', 'label' => 'Portal Help Requests', 'gate' => 'system_admin', 'badge' => 'portal_help'],
                ['id' => 'comm-jobs', 'route' => 'admin.job-applications.index', 'route_is' => 'admin.job-applications.*', 'label' => 'Job Applications'],
                ['id' => 'comm-email-recipients', 'route' => 'admin.email-recipients.index', 'route_is' => 'admin.email-recipients.*', 'label' => 'Email Recipients'],
                ['id' => 'comm-email-templates', 'route' => 'admin.email-templates.index', 'route_is' => 'admin.email-templates.*', 'label' => 'Email Templates'],
                ['id' => 'comm-scheduled-reports', 'route' => 'admin.scheduled-reports.index', 'route_is' => 'admin.scheduled-reports.*', 'label' => 'Scheduled Reports'],
                ['id' => 'comm-email-mappings', 'route' => 'admin.communications.employee-email-mappings', 'route_is' => 'admin.communications.employee-email-mappings', 'label' => 'Employee Email Mappings'],
            ],
        ],
        [
            'id' => 'settings',
            'label' => 'Settings',
            'icon' => '⚙️',
            'gate' => 'everyone',
            'items' => [
                ['id' => 'settings-appearance', 'route' => 'settings.appearance', 'route_is' => 'settings.appearance', 'icon' => '🎨', 'label' => 'Appearance'],
                ['id' => 'settings-role-stats', 'route' => 'admin.role-assignments.statistics', 'route_is' => 'admin.role-assignments.statistics', 'icon' => '📈', 'label' => 'Assignment Statistics', 'gate' => 'system_admin'],
                ['id' => 'settings-baa', 'route' => 'admin.baa-registry.index', 'route_is' => 'admin.baa-registry.*', 'icon' => '🔒', 'label' => 'BAA Vendor Registry', 'gate' => 'system_admin'],
                ['id' => 'settings-backups', 'route' => 'admin.backups.index', 'route_is' => 'admin.backups.*', 'icon' => '💾', 'label' => 'Backup & Restore', 'gate' => 'system_admin'],
                ['id' => 'settings-departments', 'route' => 'admin.departments.index', 'route_is' => 'admin.departments.*', 'icon' => '🗂️', 'label' => 'Departments', 'gate' => 'system_admin'],
                ['id' => 'settings-documents-mgmt', 'route' => 'admin.upload-types.index', 'route_is' => ['admin.upload-types.*', 'admin.checklist-items.*', 'admin.position-document-requirements.*'], 'icon' => '📁', 'label' => 'Documents Settings', 'gate' => 'documents_mgmt'],
                ['id' => 'settings-events', 'route' => 'admin.events.index', 'route_is' => 'admin.events.*', 'icon' => '📅', 'label' => 'Events', 'gate' => 'system_admin'],
                ['id' => 'settings-hipaa', 'route' => 'admin.hipaa-checklist.index', 'route_is' => 'admin.hipaa-checklist.*', 'icon' => '✅', 'label' => 'HIPAA Checklist', 'gate' => 'system_admin'],
                ['id' => 'settings-import-logs', 'route' => 'admin.import-logs.index', 'route_is' => 'admin.import-logs.*', 'icon' => '📜', 'label' => 'Import History', 'gate' => 'system_admin'],
                ['id' => 'settings-import-presets', 'route' => 'admin.import-mapping-presets.index', 'route_is' => 'admin.import-mapping-presets.*', 'icon' => '📥', 'label' => 'Import Preset Management', 'gate' => 'system_admin'],
                ['id' => 'settings-invites', 'route' => 'admin.invites.index', 'route_is' => 'admin.invites.*', 'icon' => '✉️', 'label' => 'Invite Management', 'gate' => 'invite_mgmt'],
                ['id' => 'settings-permissions', 'route' => 'admin.permissions.index', 'route_is' => 'admin.permissions.*', 'icon' => '🔑', 'label' => 'Manage Permissions', 'gate' => 'system_admin'],
                ['id' => 'settings-roles', 'route' => 'admin.roles.index', 'route_is' => 'admin.roles.*', 'icon' => '🛡️', 'label' => 'Manage Roles', 'gate' => 'system_admin'],
                ['id' => 'settings-password', 'route' => 'settings.password', 'route_is' => ['settings.password', 'settings.password.*'], 'icon' => '🔑', 'label' => 'Password'],
                ['id' => 'settings-position-portal-roles', 'route' => 'admin.position-portal-roles.index', 'route_is' => 'admin.position-portal-roles.*', 'icon' => '🏷️', 'label' => 'Position Portal Roles', 'gate' => 'system_admin'],
                ['id' => 'settings-role-assignments', 'route' => 'admin.role-assignments.index', 'route_is' => ['admin.role-assignments.index', 'admin.role-assignments.create', 'admin.role-assignments.store', 'admin.role-assignments.edit', 'admin.role-assignments.update', 'admin.role-assignments.destroy', 'admin.role-assignments.show'], 'icon' => '🔗', 'label' => 'Role Assignments', 'gate' => 'system_admin'],
                ['id' => 'settings-reports', 'route' => 'admin.reports.index', 'route_is' => ['admin.reports.*', 'admin.scheduled-reports.*'], 'icon' => '📊', 'label' => 'Reports Management', 'gate' => 'reports'],
                ['id' => 'settings-report-runs', 'route' => 'admin.scheduled-report-runs.index', 'route_is' => 'admin.scheduled-report-runs.*', 'icon' => '📋', 'label' => 'Scheduled Report Runs', 'gate' => 'reports'],
                ['id' => 'settings-security', 'route' => 'admin.security.dashboard', 'route_is' => 'admin.security.*', 'icon' => '🔐', 'label' => 'Security Monitoring', 'gate' => 'system_admin'],
                ['id' => 'settings-system', 'route' => 'admin.settings.index', 'route_is' => 'admin.settings.*', 'icon' => '🔧', 'label' => 'System Settings', 'gate' => 'system_admin'],
                ['id' => 'settings-training-mgmt', 'route' => 'admin.training-items.index', 'route_is' => ['admin.training-items.*'], 'icon' => '🎓', 'label' => 'Training Configuration', 'gate' => 'documents_mgmt'],
                ['id' => 'settings-users', 'route' => 'admin.users.index', 'route_is' => 'admin.users.*', 'icon' => '👥', 'label' => 'Users', 'gate' => 'system_admin'],
            ],
        ],
        [
            'id' => 'support',
            'label' => 'Support',
            'icon' => '🆘',
            'gate' => 'everyone',
            'items' => [
                ['id' => 'help-center', 'route' => 'member.help.index', 'route_is' => ['member.help.index', 'member.help.show', 'member.help.confirmation'], 'icon' => '📬', 'label' => 'My Help Requests'],
                ['id' => 'help-hr', 'route' => 'member.help.hr', 'route_is' => ['member.help.hr', 'member.help.hr.*'], 'icon' => '🎧', 'label' => 'Contact HR'],
                ['id' => 'help-manuals', 'route' => 'member.help.manuals', 'route_is' => ['member.help.manuals', 'member.help.manuals.*'], 'icon' => '📚', 'label' => 'Manuals and Docs'],
                ['id' => 'help-user-manual', 'route' => 'member.help.user-manual', 'route_is' => 'member.help.user-manual', 'icon' => '📕', 'label' => 'HR Portal User Manual (PDF)'],
                ['id' => 'help-support', 'route' => 'member.help.support', 'route_is' => ['member.help.support', 'member.help.support.*'], 'icon' => '💻', 'label' => 'Technical Support'],
                ['id' => 'feedback', 'route' => 'member.feedback.index', 'route_is' => ['member.feedback', 'member.feedback.*'], 'icon' => '💡', 'label' => 'Report Issue or Idea'],
            ],
        ],
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
            'id' => 'tasks',
            'route' => 'member.tasks',
            'route_is' => ['member.tasks', 'member.tasks.*'],
            'label' => 'My Tasks',
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
                    'label' => 'My Credentials',
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
        'admin.training-items.*' => 'Training Configuration',
    ],

    'corporate_titles' => [
        'admin.positions.*' => 'Positions',
        'admin.upload-types.*' => 'Documents Management',
        'admin.checklist-items.*' => 'Documents Management',
        'admin.training-items.*' => 'Training Configuration',
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
        'admin.training-items.*' => 'settings-training-mgmt',
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
        'member.tasks' => 'tasks',
        'member.tasks.*' => 'tasks',
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
        'member.help.manuals' => 'help-manuals',
        'member.help.manuals.*' => 'help-manuals',
        'member.help.support' => 'help-support',
        'member.help.support.*' => 'help-support',
        'admin.positions.*' => 'facility-positions-management',
        'admin.reports.*' => 'reports',
        'admin.scheduled-reports.*' => 'reports',
        'user.hr-portal' => 'facility-hr-portal',
        'hr-portal.*' => 'facility-hr-portal',
        'admin.upload-types.*' => 'facility-documents-management',
        'admin.checklist-items.*' => 'facility-documents-management',
        'admin.training-items.*' => 'settings-training-mgmt',
    ],

    'employee_active_map' => [
        'dashboard.index' => 'dashboard',
        'member.facilities.websites' => 'facilities-websites',
        'member.news-events.index' => 'news',
        'member.news-events.*' => 'news',
        'pre-employment.*' => 'pre-employment',
        'employment.*' => 'employment',
        'settings.profile' => 'profile',
        'member.tasks' => 'tasks',
        'member.tasks.*' => 'tasks',
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
        'member.help.manuals' => 'help-manuals',
        'member.help.manuals.*' => 'help-manuals',
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
        'member.tasks' => 'tasks',
        'member.tasks.*' => 'tasks',
        'member.documents' => 'documents',
        'user.hr-portal' => 'hr-portal',
        'hr-portal.*' => 'hr-portal',
        'member.trainings' => 'trainings',
        'member.certifications' => 'certifications',
        'member.news-events.*' => 'facility-news',
        'member.galleries.*' => 'facility-galleries',
        'admin.upload-types.*' => 'facility-documents-management',
        'admin.checklist-items.*' => 'facility-documents-management',
        'admin.training-items.*' => 'settings-training-mgmt',
    ],

    /** @deprecated Use employee_dashboard_nav + personal_portal_nav */
    'sidebar_nav' => [],

    'mobile_nav' => [
        ['id' => 'dashboard', 'route' => 'dashboard.index', 'icon' => '🏠', 'label' => 'Home'],
        ['id' => 'news', 'route' => 'member.news-events.index', 'icon' => '📰', 'label' => 'News'],
        ['id' => 'documents', 'route' => 'member.documents', 'icon' => '📄', 'label' => 'Docs'],
            ['id' => 'certifications', 'route' => 'member.certifications', 'icon' => '🪪', 'label' => 'Credentials'],
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
        'admin.upload-types.*',
        'admin.checklist-items.*',
        'admin.position-document-requirements.*',
        'admin.training-items.*',
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
        'admin.scheduled-report-runs.*' => 'Scheduled Report Runs',
        'admin.hr-portal.reports' => 'Reports',
        'admin.facilities.webcontents.testimonials' => 'Testimonials',
        'admin.facilities.webcontents.faqs' => 'FAQs',
        'admin.facilities.webcontents.blogs' => 'Blogs',
        'admin.facilities.webcontents.careers*' => 'Careers',
        'admin.galleries.*' => 'Galleries',
        'admin.news.*' => 'News',
        'member.news-events.*' => 'News/Events',
        'member.galleries.*' => 'Photo Galleries',
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
        'admin.checklist-items.*' => 'Documents Management',
        'admin.position-document-requirements.*' => 'Documents Management',
        'admin.training-items.*' => 'Training Configuration',
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
        'admin.galleries.*' => 'web-galleries',
        'admin.news.*' => 'web-news',
        'member.news-events.*' => 'facility-news',
        'member.galleries.*' => 'facility-galleries',
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
        'admin.checklist-items.*' => 'facility-documents-management',
        'admin.position-document-requirements.*' => 'facility-documents-management',
        'admin.training-items.*' => 'settings-training-mgmt',
        'admin.positions.*' => 'facility-positions-management',
        'admin.training-management.*' => 'facility-training-management',
    ],
];
