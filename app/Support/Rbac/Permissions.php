<?php

namespace App\Support\Rbac;

final class Permissions
{
    public const VIEW_FACILITIES = 'view facilities';
    public const CREATE_FACILITIES = 'create facilities';
    public const EDIT_FACILITIES = 'edit facilities';
    public const DELETE_FACILITIES = 'delete facilities';
    public const MANAGE_FACILITY_SETTINGS = 'manage facility settings';
    public const VIEW_FACILITY_ANALYTICS = 'view facility analytics';

    public const VIEW_USERS = 'view users';
    public const CREATE_USERS = 'create users';
    public const EDIT_USERS = 'edit users';
    public const DELETE_USERS = 'delete users';
    public const MANAGE_USERS = 'manage users';
    public const VIEW_USER_PROFILES = 'view user profiles';

    public const VIEW_ROLES = 'view roles';
    public const CREATE_ROLES = 'create roles';
    public const EDIT_ROLES = 'edit roles';
    public const DELETE_ROLES = 'delete roles';
    public const MANAGE_ROLES = 'manage roles';
    public const VIEW_PERMISSIONS = 'view permissions';
    public const CREATE_PERMISSIONS = 'create permissions';
    public const EDIT_PERMISSIONS = 'edit permissions';
    public const DELETE_PERMISSIONS = 'delete permissions';
    public const MANAGE_PERMISSIONS = 'manage permissions';
    public const ASSIGN_ROLES = 'assign roles';
    public const REVOKE_ROLES = 'revoke roles';

    public const VIEW_CONTENT = 'view content';
    public const CREATE_CONTENT = 'create content';
    public const EDIT_CONTENT = 'edit content';
    public const DELETE_CONTENT = 'delete content';
    public const PUBLISH_CONTENT = 'publish content';
    public const MANAGE_TESTIMONIALS = 'manage testimonials';
    public const MANAGE_NEWS = 'manage news';
    public const MANAGE_FAQS = 'manage faqs';
    public const MANAGE_GALLERIES = 'manage galleries';
    public const MANAGE_SERVICES = 'manage services';

    public const VIEW_COMMUNICATIONS = 'view communications';
    public const MANAGE_TOUR_REQUESTS = 'manage tour requests';
    public const MANAGE_INQUIRIES = 'manage inquiries';
    public const MANAGE_JOB_APPLICATIONS = 'manage job applications';
    public const MANAGE_EMAIL_RECIPIENTS = 'manage email recipients';

    public const VIEW_SECURITY_DASHBOARD = 'view security dashboard';
    public const VIEW_AUDIT_LOGS = 'view audit logs';
    public const MANAGE_SECURITY_SETTINGS = 'manage security settings';
    public const VIEW_SYSTEM_MONITORING = 'view system monitoring';

    public const ACCESS_ADMIN_PANEL = 'access admin panel';
    public const ACCESS_HR_PORTAL = 'access hr portal';
    public const VIEW_POSITIONS = 'view positions';
    public const EDIT_POSITIONS = 'edit positions';
    public const EDIT_EMPLOYEE_CORE_TABS = 'edit employee core tabs';
    public const VIEW_SYSTEM_SETTINGS = 'view system settings';
    public const MANAGE_SYSTEM_SETTINGS = 'manage system settings';
    public const VIEW_REPORTS = 'view reports';
    public const EXPORT_DATA = 'export data';

    public const USE_IMPORT_MAPPING_PRESETS = 'use import mapping presets';
    public const CREATE_IMPORT_MAPPING_PRESETS = 'create import mapping presets';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            self::VIEW_FACILITIES,
            self::CREATE_FACILITIES,
            self::EDIT_FACILITIES,
            self::DELETE_FACILITIES,
            self::MANAGE_FACILITY_SETTINGS,
            self::VIEW_FACILITY_ANALYTICS,
            self::VIEW_USERS,
            self::CREATE_USERS,
            self::EDIT_USERS,
            self::DELETE_USERS,
            self::MANAGE_USERS,
            self::VIEW_USER_PROFILES,
            self::VIEW_ROLES,
            self::CREATE_ROLES,
            self::EDIT_ROLES,
            self::DELETE_ROLES,
            self::MANAGE_ROLES,
            self::VIEW_PERMISSIONS,
            self::CREATE_PERMISSIONS,
            self::EDIT_PERMISSIONS,
            self::DELETE_PERMISSIONS,
            self::MANAGE_PERMISSIONS,
            self::ASSIGN_ROLES,
            self::REVOKE_ROLES,
            self::VIEW_CONTENT,
            self::CREATE_CONTENT,
            self::EDIT_CONTENT,
            self::DELETE_CONTENT,
            self::PUBLISH_CONTENT,
            self::MANAGE_TESTIMONIALS,
            self::MANAGE_NEWS,
            self::MANAGE_FAQS,
            self::MANAGE_GALLERIES,
            self::MANAGE_SERVICES,
            self::VIEW_COMMUNICATIONS,
            self::MANAGE_TOUR_REQUESTS,
            self::MANAGE_INQUIRIES,
            self::MANAGE_JOB_APPLICATIONS,
            self::MANAGE_EMAIL_RECIPIENTS,
            self::VIEW_SECURITY_DASHBOARD,
            self::VIEW_AUDIT_LOGS,
            self::MANAGE_SECURITY_SETTINGS,
            self::VIEW_SYSTEM_MONITORING,
            self::ACCESS_ADMIN_PANEL,
            self::ACCESS_HR_PORTAL,
            self::VIEW_POSITIONS,
            self::EDIT_POSITIONS,
            self::EDIT_EMPLOYEE_CORE_TABS,
            self::VIEW_SYSTEM_SETTINGS,
            self::MANAGE_SYSTEM_SETTINGS,
            self::VIEW_REPORTS,
            self::EXPORT_DATA,
            self::USE_IMPORT_MAPPING_PRESETS,
            self::CREATE_IMPORT_MAPPING_PRESETS,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function protected(): array
    {
        return [
            self::ACCESS_ADMIN_PANEL,
            self::ACCESS_HR_PORTAL,
            self::MANAGE_USERS,
            self::MANAGE_ROLES,
            self::MANAGE_PERMISSIONS,
        ];
    }
}
