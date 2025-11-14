# Custom File Structure Hierarchy for BioPacific App

This document outlines all custom (non-third-party) files and their purpose in the BioPacific Laravel application. Third-party/vendor files are excluded. The structure is grouped by main directories and includes a brief description of each file or folder's use.

---

## app/

- **Console/Commands/**
  - `FixFacilityDomains.php`: Command to fix facility domain records.
  - `LayoutBuilderStatus.php`: Command to check layout builder status.
  - `ListLayouts.php`: Command to list available layouts.
- **Helpers/**
  - `FacilityDataHelper.php`: Helper for facility-related data operations.
  - `PhoneHelper.php`: Helper for phone number formatting and validation.
- **Http/Controllers/**
  - `AdminUserController.php`: Admin CRUD for users.
  - `AuditController.php`: Handles audit log viewing/export.
  - `DashboardController.php`: General dashboard logic (may be replaced by FacilityAdminController).
  - `FacilityAdminController.php`: Admin dashboard and facility management.
  - `FacilityController.php`: Public and admin facility logic.
  - `FaqController.php`, `HomeController.php`, `ProfileController.php`, `TourController.php`, `WebmasterController.php`: Various feature controllers.
  - **Admin/**: Controllers for admin CRUD (events, facilities, testimonials, galleries, layouts, news, services, webmaster contacts).
  - **Auth/**: Authentication controllers (login, registration, password, verification).
- **Http/Middleware/**
  - `CheckFacilityShutdown.php`: Middleware to check if a facility is shut down.
  - `ResolveTenant.php`: Middleware for tenant resolution.
- **Http/Requests/**
  - `ProfileUpdateRequest.php`: Request validation for profile updates.
  - **Auth/**: Auth-related request validation.
- **Livewire/**
  - `FacilitiesIndex.php`, `FacilityLandingPage.php`, `HipaaChecklistInteractive.php`, `LayoutBuilder.php`: Livewire components for facility and layout features.
  - **Actions/**: `Logout.php`: Livewire logout action.
  - **Admin/**: `HipaaChecklist.php`: Admin HIPAA checklist component.
  - **Settings/**: `Appearance.php`, `DeleteUserForm.php`, `Password.php`, `Profile.php`: User settings components.
- **Models/**
  - `AuditLog.php`, `ColorScheme.php`, `Event.php`, `Facility.php`, `FacilityLegal.php`, `FacilityPolicyVersion.php`, `FacilityValue.php`, `Faq.php`, `GalleryImage.php`, `LayoutSection.php`, `LayoutTemplate.php`, `Legal.php`, `News.php`, `PolicyVersion.php`, `Service.php`, `Testimonial.php`, `User.php`, `WebContent.php`, `WebmasterContact.php`: Eloquent models for all main entities.
- **Providers/**
  - `AppServiceProvider.php`, `VoltServiceProvider.php`: Laravel service providers.
- **Services/**
  - `AuditService.php`: Audit log service.
  - `DynamicLayoutService.php`: Dynamic layout logic.
  - `TenantAssetService.php`, `TenantConfigService.php`: Tenant-specific asset/config services.
- **Support/**
  - `HipaaWebsiteChecklist.php`: Support class for HIPAA checklist.
- **Traits/**
  - `Auditable.php`, `BelongsToTenant.php`: Model traits for auditing and tenancy.
- **View/Components/**
  - `AppLayout.php`, `GuestLayout.php`: Blade layout components.
  - **Admin/**: `WebmasterContactNotifications.php`: Admin notification component.

---

## resources/views/

- **index.blade.php**: Main landing page.
- **admin/**
  - **dashboard/**: `index.blade.php`, `custom.blade.php`: Admin dashboard views.
  - **facilities/**: Facility management views (create, edit, hipaa, index, webcontents).
    - **webcontents/**: Facility web content management (blogs, careers, faqs, news-events, testimonials).
  - **users/**: User management views (create, edit, index).
  - **news/**: News management views (create, edit, index, show).
  - **services/**: Service management view (create).
  - **testimonials/**: Testimonial management views (create, edit, index).
  - **galleries/**: Gallery management views (create, index, facility selection).
  - **webmaster_contacts/**: Webmaster contact management (index, show).
- **dashboard/**: General dashboard view for authenticated users.
- **facilities/**: Public facility views (create, index, preview, show).
- **profile/**: User profile edit and partials (delete user, update password/profile).
- **webmaster/**: Webmaster contact form.
- **audit/**: Audit log views (index, show).
- **auth/**: Authentication views (login, register, password, verification).
- **components/**
  - Various Blade components for UI (buttons, forms, nav, layout, etc.).
  - **admin/**: `webmaster-contact-notifications.blade.php`: Admin notification UI.
  - **settings/**: `layout.blade.php`: Settings layout component.
- **layouts/**
  - `admin.blade.php`, `app.blade.php`, `dashboard.blade.php`, `default-template.blade.php`, `dynamic.blade.php`, `guest.blade.php`, `layout1.blade.php`, `layout2.blade.php`, `navigation.blade.php`, `sidebar.blade.php`, `topnav.blade.php`: Layout templates for various sections.
- **partials/**
  - Shared partials for about, accessibility, book, careers, contact, cta, faqs, footer, gallery, gototop, head, header, hero, hipaa_checklist, navigation, news, resources, rooms, section_header, services, settings-heading, testimonials, toast, topbar.

---

## Other Custom Files

- **config/**: Custom configuration files (app.php, auth.php, etc.)
- **routes/**: Route definitions (web.php, admin_webmaster_contacts.php, auth.php).
- **database/**: Migrations, seeders, factories for custom models.
- **public/**: Custom assets (css, js, images, build output).

---

## Notes

- All files above are custom to the BioPacific app and support its multi-tenant, admin, and facility management features.
- Third-party/vendor files (in `vendor/`, `node_modules/`, etc.) are excluded.
- For more details on any file, see its location and name above.
