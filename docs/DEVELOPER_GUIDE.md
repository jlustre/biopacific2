# Developer's Guide: Bio-Pacific Laravel Application

## 1. Facility Management

**Purpose:** Manage facilities, their details, and related content.

- **Controllers:**
  - app/Http/Controllers/FacilityController.php
  - app/Http/Controllers/FacilityAdminController.php
- **Models:**
  - app/Models/Facility.php
  - app/Models/FacilityValue.php
  - app/Models/FacilityLegal.php
  - app/Models/FacilityPolicyVersion.php
- **Livewire:**
  - app/Livewire/FacilitiesIndex.php
  - app/Livewire/FacilityLandingPage.php
- **Views:**
  - resources/views/admin/facilities/
  - resources/views/facilities/
- **Helpers:**
  - app/Helpers/FacilityDataHelper.php
- **Routes:**
  - routes/web.php (`/facilities`, `/admin/facilities`, `/facilities/{facility}`)

---

## 2. Layout Builder & Dynamic Layout

**Purpose:** Dynamic page layouts and section configuration.

- **Controllers:**
  - app/Http/Controllers/LayoutBuilderController.php
- **Models:**
  - app/Models/LayoutTemplate.php
  - app/Models/LayoutSection.php
- **Livewire:**
  - app/Livewire/LayoutBuilder.php
- **Services:**
  - app/Services/DynamicLayoutService.php
- **Views:**
  - resources/views/layouts/
  - resources/views/components/layouts/
- **Routes:**
  - routes/web.php (`/admin/layouts`, `/layouts/{template}`)

---

## 3. Web Content Management

**Purpose:** Manage static and dynamic web content.

- **Controllers:**
  - app/Http/Controllers/WebContentController.php
- **Models:**
  - app/Models/WebContent.php
- **Views:**
  - resources/views/content/
- **Routes:**
  - routes/web.php (`/content`, `/admin/content`)

---

## 4. Service Management

**Purpose:** Manage facility services and amenities.

- **Controllers:**
  - app/Http/Controllers/ServicesController.php
- **Models:**
  - app/Models/Service.php
- **Livewire:**
  - app/Livewire/ServiceManager.php
- **Views:**
  - resources/views/services/
  - resources/views/partials/services/
- **Routes:**
  - routes/web.php (`/services`, `/admin/services`)

---

## 5. Testimonial Management

**Purpose:** Manage patient and family testimonials.

- **Controllers:**
  - app/Http/Controllers/TestimonialController.php
- **Models:**
  - app/Models/Testimonial.php
- **Livewire:**
  - app/Livewire/TestimonialManager.php
- **Views:**
  - resources/views/testimonials/
- **Routes:**
  - routes/web.php (`/testimonials`, `/admin/testimonials`)

---

## 6. Gallery Management

**Purpose:** Manage facility photo galleries.

- **Controllers:**
  - app/Http/Controllers/GalleryController.php
- **Models:**
  - app/Models/GalleryImage.php
- **Livewire:**
  - app/Livewire/GalleryManager.php
- **Views:**
  - resources/views/gallery/
- **Routes:**
  - routes/web.php (`/gallery`, `/admin/gallery`)

---

## 7. News & Events

**Purpose:** Manage news articles and events.

- **Controllers:**
  - app/Http/Controllers/NewsController.php
  - app/Http/Controllers/EventController.php
- **Models:**
  - app/Models/News.php
  - app/Models/Event.php
- **Views:**
  - resources/views/news/
  - resources/views/events/
- **Routes:**
  - routes/web.php (`/news`, `/events`, `/admin/news`, `/admin/events`)

---

## 8. FAQ Management

**Purpose:** Manage frequently asked questions.

- **Controllers:**
  - app/Http/Controllers/FaqController.php
- **Models:**
  - app/Models/Faq.php
- **Views:**
  - resources/views/faq/
- **Routes:**
  - routes/web.php (`/faq`, `/admin/faq`)

---

## 9. Careers & Job Applications

**Purpose:** Manage job openings and applications.

- **Controllers:**
  - app/Http/Controllers/CareersController.php
  - app/Http/Controllers/CareersPublicController.php
  - app/Http/Controllers/JobApplicationController.php
  - app/Http/Controllers/CareersApplicationsController.php
- **Models:**
  - app/Models/JobApplication.php
  - app/Models/JobOpening.php
  - app/Models/EmployeeChecklist.php
  - app/Models/PreEmploymentApplication.php
  - app/Models/HiringActivityLog.php
- **Livewire:**
  - app/Livewire/JobOpeningsForm.php
- **Views:**
  - resources/views/admin/facilities/pre-employment-review.blade.php
  - resources/views/pre-employment/
  - resources/views/partials/careers/
- **Routes:**
  - routes/web.php (`/careers`, `/careers/apply`, `/admin/careers`, `/admin/job-applications`, `/admin/pre-employment-review`)

---

## 10. Book a Tour

**Purpose:** Manage facility tour bookings.

- **Controllers:**
  - app/Http/Controllers/BookATourController.php
- **Models:**
  - app/Models/BookATour.php
- **Livewire:**
  - app/Livewire/BookATour.php
- **Mail:**
  - app/Mail/BookATourMail.php
  - app/Mail/SecureBookATourMail.php
- **Views:**
  - resources/views/tour/
- **Routes:**
  - routes/web.php (`/book-a-tour`, `/admin/book-a-tour`)

---

## 11. Contact & Webmaster

**Purpose:** Manage contact forms and webmaster notifications.

- **Controllers:**
  - app/Http/Controllers/ContactController.php
  - app/Http/Controllers/WebmasterController.php
- **Models:**
  - app/Models/WebmasterContact.php
- **Mail:**
  - app/Mail/ContactMail.php
  - app/Mail/SecureContactMail.php
- **Views:**
  - resources/views/contact/
  - resources/views/webmaster/
- **Routes:**
  - routes/web.php (`/contact`, `/webmaster`)

---

## 12. Audit Logging

**Purpose:** Track and review system activity.

- **Controllers:**
  - app/Http/Controllers/AuditController.php
- **Models:**
  - app/Models/AuditLog.php
- **Services:**
  - app/Services/AuditService.php
- **Views:**
  - resources/views/audit/
- **Routes:**
  - routes/web.php (`/admin/audit-logs`)

---

## 13. Accessibility & Legal

**Purpose:** Accessibility and legal compliance pages.

- **Controllers:**
  - app/Http/Controllers/AccessibilityController.php
  - app/Http/Controllers/PrivacyPolicyController.php
- **Views:**
  - resources/views/partials/topbar/legal.blade.php
  - resources/views/facility/privacy.blade.php
- **Routes:**
  - routes/web.php (`/accessibility`, `/privacy-policy`)

---

## 14. Settings & User Profile

**Purpose:** User settings and profile management.

- **Livewire:**
  - app/Livewire/Settings/Appearance.php
  - app/Livewire/Settings/DeleteUserForm.php
  - app/Livewire/Settings/Password.php
  - app/Livewire/Settings/Profile.php
- **Views:**
  - resources/views/components/settings/layout.blade.php
  - resources/views/profile/
- **Routes:**
  - routes/web.php (`/settings`, `/profile`)

---

## 15. Helpers & Middleware

**Purpose:** Utility helpers and middleware for core logic.

- **Helpers:**
  - app/Helpers/FacilityDataHelper.php
  - app/Helpers/PhoneHelper.php
  - app/Helpers/SecureAssetHelper.php
- **Middleware:**
  - app/Http/Middleware/CheckFacilityShutdown.php
  - app/Http/Middleware/ResolveTenant.php
  - app/Http/Middleware/LogLivewireUpdate.php

---

## 16. Console Commands

**Purpose:** Artisan CLI commands for maintenance and automation.

- **Commands:**
  - app/Console/Commands/FixFacilityDomains.php
  - app/Console/Commands/LayoutBuilderStatus.php
  - app/Console/Commands/ListLayouts.php
  - app/Console/Commands/VerifySecurityData.php
  - ... (see app/Console/Commands/ for full list)

---

## 17. Other Custom Views & Components

**Purpose:** Shared layouts, partials, and Blade components.

- **Blade Components:**
  - resources/views/components/ (e.g., video-modal, text-input, nav-link, layouts/)
- **Layouts:**
  - resources/views/layouts/
- **Partials:**
  - resources/views/partials/
- **JS:**
  - resources/js/app.js

---

## 18. Core Models (Reference)

- app/Models/Facility.php
- app/Models/Service.php
- app/Models/Testimonial.php
- app/Models/GalleryImage.php
- app/Models/LayoutTemplate.php
- app/Models/LayoutSection.php
- app/Models/User.php
- app/Models/BookATour.php
- app/Models/AuditLog.php

---

## 19. Policies, Traits, Services, and Providers

- **Policies:**
  - app/Policies/PreEmploymentApplicationPolicy.php
  - app/Policies/JobApplicationPolicy.php
- **Traits:**
  - app/Traits/EncryptsEphi.php
  - app/Traits/BelongsToTenant.php
  - app/Traits/Auditable.php
- **Services:**
  - app/Services/TenantConfigService.php
  - app/Services/TenantAssetService.php
  - app/Services/DynamicLayoutService.php
  - app/Services/AuditService.php
- **Providers:**
  - app/Providers/AppServiceProvider.php
  - app/Providers/VoltServiceProvider.php

---

## 20. Routes

- routes/web.php
- routes/web-ajax.php
- routes/positions_ajax.php
- routes/console.php
- routes/auth.php
- routes/admin_webmaster_contacts.php
- routes/admin_incident_contacts.php
- routes/admin_arbitration_templates.php

---
