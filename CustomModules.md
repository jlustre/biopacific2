# Custom Modules & Submodules with Routes

---

## 1. Facility Management

- **Controllers:**  
  - `FacilityController.php`  
  - `FacilityAdminController.php`
- **Models:**  
  - `Facility.php`, `FacilityValue.php`, `FacilityLegal.php`, `FacilityPolicyVersion.php`
- **Livewire:**  
  - `FacilitiesIndex.php`, `FacilityLandingPage.php`
- **Views:**  
  - `resources/views/admin/facilities/`, `resources/views/facilities/`
- **Routes/URLs:**  
  - `/facilities`  
  - `/admin/facilities`  
  - `/admin/facilities/{facility}`  
  - `/facilities/{facility}`

---

## 2. Layout Builder & Dynamic Layout

- **Controllers:**  
  - `Admin/LayoutBuilderController.php`, `Admin/LayoutSectionController.php`
- **Models:**  
  - `LayoutTemplate.php`, `LayoutSection.php`
- **Livewire:**  
  - `LayoutBuilder.php`
- **Views:**  
  - `resources/views/livewire/layout-builder.blade.php`, `resources/views/components/dynamic-layout.blade.php`, `resources/views/layouts/`
- **Routes/URLs:**  
  - `/admin/layout-builder`  
  - `/admin/layout-sections`  
  - `/layouts/{layout}`

---

## 3. Web Content Management

- **Controllers:**  
  - `WebmasterController.php`
- **Models:**  
  - `WebContent.php`
- **Views:**  
  - `resources/views/admin/facilities/webcontents/`
- **Routes/URLs:**  
  - `/admin/webcontents`  
  - `/admin/webcontents/{id}`

---

## 4. Service Management

- **Controllers:**  
  - `ServicesController.php`
- **Models:**  
  - `Service.php`
- **Views:**  
  - `resources/views/admin/services/`, `resources/views/partials/services/`
- **Routes/URLs:**  
  - `/admin/services`  
  - `/services`

---

## 5. Testimonial Management

- **Controllers:**  
  - `Admin/FacilityTestimonialController.php`
- **Models:**  
  - `Testimonial.php`
- **Views:**  
  - `resources/views/admin/testimonials/`, `resources/views/partials/testimonials/`
- **Routes/URLs:**  
  - `/admin/testimonials`  
  - `/testimonials`

---

## 6. Gallery Management

- **Controllers:**  
  - `GalleryController.php`
- **Models:**  
  - `GalleryImage.php`
- **Views:**  
  - `resources/views/admin/galleries/`, `resources/views/partials/gallery/`
- **Routes/URLs:**  
  - `/admin/galleries`  
  - `/gallery`

---

## 7. News & Events

- **Controllers:**  
  - `NewsController.php`, `EventController.php`
- **Models:**  
  - `News.php`
- **Views:**  
  - `resources/views/admin/news/`, `resources/views/partials/news/`
- **Routes/URLs:**  
  - `/admin/news`  
  - `/news`  
  - `/admin/events`  
  - `/events`

---

## 8. FAQ Management

- **Controllers:**  
  - `FaqController.php`
- **Models:**  
  - `Faq.php`
- **Views:**  
  - `resources/views/partials/faqs/`
- **Routes/URLs:**  
  - `/faqs`  
  - `/admin/faqs`

---

## 9. Careers & Job Applications

- **Controllers:**  
  - `CareersController.php`, `CareersPublicController.php`, `JobApplicationController.php`, `CareersApplicationsController.php`
- **Models:**  
  - `JobApplication.php`, `JobOpening.php`, `EmployeeChecklist.php`, `PreEmploymentApplication.php`, `HiringActivityLog.php`
- **Livewire:**  
  - `JobOpeningsForm.php`
- **Views:**  
  - `resources/views/admin/facilities/pre-employment-review.blade.php`, `resources/views/pre-employment/`, `resources/views/partials/careers/`
- **Routes/URLs:**  
  - `/careers`  
  - `/careers/apply`  
  - `/admin/careers`  
  - `/admin/job-applications`  
  - `/admin/pre-employment-review`

---

## 10. Book a Tour

- **Controllers:**  
  - `BookATourController.php`, `TourController.php`
- **Models:**  
  - `BookATour.php`, `TourRequest.php`
- **Views:**  
  - `resources/views/partials/book/`
- **Routes/URLs:**  
  - `/book-a-tour`  
  - `/admin/tours`

---

## 11. Contact & Webmaster

- **Controllers:**  
  - `ContactController.php`, `WebmasterController.php`
- **Models:**  
  - `WebmasterContact.php`
- **Views:**  
  - `resources/views/partials/contact/`, `resources/views/webmaster/`
- **Routes/URLs:**  
  - `/contact`  
  - `/webmaster`  
  - `/admin/webmaster`

---

## 12. Audit Logging

- **Controllers:**  
  - `AuditController.php`
- **Models:**  
  - `AuditLog.php`
- **Views:**  
  - `resources/views/audit/`
- **Routes/URLs:**  
  - `/admin/audit-logs`

---

## 13. Accessibility & Legal

- **Controllers:**  
  - `AccessibilityController.php`, `PrivacyPolicyController.php`
- **Views:**  
  - `resources/views/partials/topbar/legal.blade.php`, `resources/views/facility/privacy.blade.php`
- **Routes/URLs:**  
  - `/accessibility`  
  - `/privacy-policy`

---

## 14. Settings & User Profile

- **Livewire:**  
  - `Settings/Appearance.php`, `Settings/DeleteUserForm.php`, `Settings/Password.php`, `Settings/Profile.php`
- **Views:**  
  - `resources/views/components/settings/layout.blade.php`, `resources/views/profile/`
- **Routes/URLs:**  
  - `/settings`  
  - `/profile`

---

## 15. Helpers & Middleware

- **Helpers:**  
  - `FacilityDataHelper.php`, `PhoneHelper.php`
- **Middleware:**  
  - `CheckFacilityShutdown.php`, `ResolveTenant.php`, `LogLivewireUpdate.php`
- **Routes/URLs:**  
  - *(Helpers and middleware are used internally and do not have direct routes)*

---

## 16. Console Commands

- `FixFacilityDomains.php`, `LayoutBuilderStatus.php`, `ListLayouts.php`, `VerifySecurityData.php`
- **Routes/URLs:**  
  - *(Console commands are run via Artisan CLI, not via web routes)*

---

## 17. Other Custom Views

- `resources/views/welcome.blade.php`, `resources/views/dashboard/index.blade.php`, `resources/views/layouts/`, `resources/views/partials/`, `resources/views/components/`
- **Routes/URLs:**  
  - `/`  
  - `/dashboard`

---
