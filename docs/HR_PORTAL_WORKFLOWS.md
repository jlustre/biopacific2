# Bio Pacific HR Portal — Workflow Reference

This document describes **every HR workflow** implemented or planned in the Bio Pacific portal. Each section includes the workflow title, purpose, who is involved, step-by-step process, business rules, and implementation status.

**Audience:** HR staff, facility administrators, DSDs, developers, and trainers.

**Related documentation**

| Document | Purpose |
|----------|---------|
| [HR Portal Business Rules](HR_PORTAL_BUSINESS_RULES.md) | Assessment periods, hire dates, dashboard rules |
| [Developer Guide](DEVELOPER_GUIDE.md) | Technical architecture and code locations |
| [Features](FEATURES.md) | Broader application feature list |

**Export to PDF:** Open this file in VS Code, Cursor, or any Markdown viewer and use *Print → Save as PDF*, or run `pandoc docs/HR_PORTAL_WORKFLOWS.md -o docs/HR_PORTAL_WORKFLOWS.pdf`.

---

## Table of Contents

1. [Role Overview](#1-role-overview)
2. [HR Portal Access & Facility Selection](#2-hr-portal-access--facility-selection)
3. [Public Job Application](#3-public-job-application)
4. [Job Application Review & Status Progression](#4-job-application-review--status-progression)
5. [Applicant Portal Registration](#5-applicant-portal-registration)
6. [Pre-Employment Applicant Portal](#6-pre-employment-applicant-portal)
7. [Pre-Employment Admin Review](#7-pre-employment-admin-review)
8. [Hire or Reject Applicant](#8-hire-or-reject-applicant)
9. [Employee Portal Registration (Existing Staff)](#9-employee-portal-registration-existing-staff)
10. [Employee Record Management (HR)](#10-employee-record-management-hr)
11. [Employee Self-Service (My Employment)](#11-employee-self-service-my-employment)
12. [Employee Document Upload & DSD Verification](#12-employee-document-upload--dsd-verification)
13. [Facility Document Management](#13-facility-document-management)
14. [Employee Checklist — Parts C & D (HR Acknowledgements & Policies)](#14-employee-checklist--parts-c--d-hr-acknowledgements--policies)
15. [Part E — Orientation Checklist](#15-part-e--orientation-checklist)
16. [Part F — Performance Appraisal](#16-part-f--performance-appraisal)
17. [Part G — Competency Assessment](#17-part-g--competency-assessment)
18. [Assessment Period Management](#18-assessment-period-management)
19. [Job Openings Management](#19-job-openings-management)
20. [Facility Hiring Hub](#20-facility-hiring-hub)
21. [Employee Data Import (Excel)](#21-employee-data-import-excel)
22. [HR Reports & Scheduled Reports](#22-hr-reports--scheduled-reports)
23. [Positions, Departments & Checklist Configuration](#23-positions-departments--checklist-configuration)
24. [Employee Email Mappings (Communications)](#24-employee-email-mappings-communications)
25. [Member Dashboard (Employee Hub)](#25-member-dashboard-employee-hub)
26. [User, Role & Permission Administration](#26-user-role--permission-administration)
27. [Secure Staff Access to Sensitive Applicant Data](#27-secure-staff-access-to-sensitive-applicant-data)
28. [Arbitration Templates](#28-arbitration-templates)
29. [Termination Management](#29-termination-management)
30. [Attendance Tracking](#30-attendance-tracking)
31. [HIPAA Facility Checklist](#31-hipaa-facility-checklist)
32. [End-to-End Hiring Pipeline Summary](#32-end-to-end-hiring-pipeline-summary)
33. [Implementation Status Matrix](#33-implementation-status-matrix)

---

## 1. Role Overview

Understanding roles is essential before reading individual workflows.

| Role | Description | Typical HR capabilities |
|------|-------------|-------------------------|
| **super-admin / admin** | System administrators | All facilities, users, security, report builder, imports, global configuration |
| **rdhr** | Regional Director of HR | All facilities via facility picker; full HR operations; hire/reject; registration codes |
| **facility-admin** | Facility administrator | Single assigned facility; employees, hiring, documents, reports, hire/reject |
| **facility-dsd** | Director of Staff Development | Single facility; document verification, assessments, pre-employment review; **cannot** hire/reject |
| **facility-editor** | Facility content editor | Facility dashboard, document uploads; limited HR verification |
| **regular-user** | Employee or applicant | Self-service: My Employment, My Pre-Employment, member dashboard |

**Facility scoping:** `facility-admin` and `facility-dsd` users are restricted to employees and records at their assigned facility (`users.facility_id`).

---

## 2. HR Portal Access & Facility Selection

**Status:** Complete

### Purpose

Provide a single entry point for HR staff to reach facility-specific tools (employees, hiring, documents, reports, checklists).

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd

### Steps

1. User logs in and navigates to **HR Portal** (`/admin/hr-portal` or `/hr-portal`).
2. **RDHR / Admin:** sees a list of facilities and selects one.
3. **Facility-admin / DSD:** is redirected automatically to their assigned facility dashboard (or an error if no facility is assigned).
4. Selected facility is stored in session via **Select Facility** (`/admin/hr-portal/select-facility/{facility}`).
5. User lands on the **Facility Dashboard** with quick links to employees, hiring, documents, reports, and checklist shortcuts.

### Business rules

- Facility managers without a `facility_id` cannot access facility-scoped HR tools.
- Quick-action links on the dashboard only appear when the user’s role has permission for that route.

### Key locations

- Routes: `hr-portal.index`, `admin.hr-portal.select-facility`, `admin.facility.dashboard`
- Views: `resources/views/admin/hr-portal/`, `resources/views/admin/facilities/dashboard.blade.php`

---

## 3. Public Job Application

**Status:** Complete

### Purpose

Allow job seekers to apply for open positions published on facility careers pages.

### Roles

- **Applicant:** public (no login required)
- **Reviewers:** admin, rdhr, facility-admin, facility-dsd, facility-editor

### Steps

1. Applicant finds a job listing on the public careers site.
2. Applicant completes and submits the application form (resume, contact info, position).
3. System creates a **Job Application** record with status **pending**.
4. Assigned facility HR staff receive the application in the admin job applications list or facility hiring hub.
5. Staff review resume, contact applicant, and advance status (see Workflow 4).

### Business rules

- Applications are tied to a facility and job opening where applicable.
- Sensitive applicant data can be accessed via secure token links (see Workflow 27).

### Key locations

- Public apply: `POST /careers/apply`
- Admin review: `AdminJobApplicationController`, `resources/views/admin/job-applications/`

---

## 4. Job Application Review & Status Progression

**Status:** Complete

### Purpose

Move applicants through the hiring pipeline from initial review to pre-employment or rejection.

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd, facility-editor (facility-scoped)

### Steps

1. HR opens **Job Applications** (`/admin/job-applications`) or the facility **Hiring Hub**.
2. HR reviews application details, resume preview/download, and notes.
3. HR updates status along the pipeline:
   - **pending** → initial submission
   - **reviewed** → HR has reviewed
   - **interview** → interview scheduled/completed
   - **pre-employment** → applicant invited to complete pre-employment checklist
   - **hired** / **rejected** → final outcomes
4. When status changes to **pre-employment**:
   - System may auto-generate an applicant registration code (`T-XXXXXX`).
   - Pre-employment invitation email is sent.
5. HR can manually generate a registration code from the application detail page if needed.

### Business rules

- Status changes are logged for audit purposes.
- Pre-employment status triggers applicant portal onboarding (Workflows 5–6).
- Only authorized roles can advance to hire (Workflow 8).

### Key locations

- Route: `PATCH admin/job-applications/{id}/status`
- Controller: `AdminJobApplicationController@updateStatus`, `@generateRegistrationCode`
- Policy: `JobApplicationPolicy`

---

## 5. Applicant Portal Registration

**Status:** Complete

### Purpose

Convert a pre-employment applicant into a portal user so they can complete checklist items online.

### Roles

- **HR:** generates registration codes
- **Applicant:** registers using code

### Steps

1. HR generates an applicant registration code (`T-XXXXXX`) from the job application page, or the system auto-generates one when status becomes **pre-employment**.
2. Applicant receives email with code and link to `/register/{code}`.
3. Applicant completes registration form (name, email, password, code validation).
4. System links the new **User** account to the **Job Application** and assigns **regular-user** role.
5. Applicant logs in and is directed to **My Pre-Employment** portal.

### Business rules

- Registration codes expire (default 14 days).
- Each code is single-use and tied to one applicant record.
- Code format: `T-XXXXXX` for applicants, `E-XXXXXX` for employees (Workflow 9).

### Key locations

- Service: `app/Support/RegistrationCodeService.php`
- Model: `app/Models/RegistrationCode.php`
- Livewire: `app/Livewire/Auth/Register.php`

---

## 6. Pre-Employment Applicant Portal

**Status:** Complete

### Purpose

Allow applicants to complete required forms and checklist items before hire.

### Roles

- **Applicant:** regular-user linked to pre-employment record
- **HR:** returns or approves checklist sections

### Steps

1. Applicant visits `/my-pre-employment` after login.
2. Portal displays checklist sections, typically including:
   - Employee application form
   - Reference checks
   - Medical exam documentation
   - Compliance forms
3. Applicant completes each section and submits.
4. HR reviews submissions in the admin pre-employment review screen (Workflow 7).
5. If HR **returns** an item, applicant receives feedback and must revise and resubmit.
6. If HR **approves** items, applicant progresses toward hire decision.

### Business rules

- Return and approve actions require admin, facility-admin, or rdhr role (not facility-dsd).
- Reference checks may use confidential AJAX endpoints to protect reference contact data.

### Key locations

- Routes: `pre-employment.portal`, `pre-employment.checklist.*`
- Controllers: `PreEmploymentController`, `PreEmploymentChecklistController`, `EmployeeApplicationController`
- Views: `resources/views/pre-employment/`

---

## 7. Pre-Employment Admin Review

**Status:** Complete

### Purpose

Allow facility HR to review applicant submissions, generate PDFs, and update pre-employment status.

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd, facility-editor

### Steps

1. HR opens facility **Hiring Hub** → selects pre-employment application.
2. HR navigates to `/admin/facility/{facility}/pre-employment/{application}`.
3. HR reviews all submitted forms, documents, and reference checks.
4. HR generates or downloads **pre-employment PDF** for records.
5. HR updates pre-employment status (e.g., returned, completed).
6. HR downloads applicant-uploaded documents as needed.

### Business rules

- Facility-dsd can view and review but cannot perform hire/reject (Workflow 8).
- Activity is logged in hiring activity log where configured.

### Key locations

- Controller: `QuickActionsController@reviewPreEmployment`, `@createPreEmploymentPdf`, `@updatePreEmploymentStatus`
- Policy: `PreEmploymentApplicationPolicy`

---

## 8. Hire or Reject Applicant

**Status:** Complete

### Purpose

Finalize the hiring decision and create an employee record from a successful applicant.

### Roles

**Hire / Reject:** admin, rdhr, facility-admin  
**Not authorized:** facility-dsd, facility-editor, regular-user

### Steps

1. From pre-employment review, HR opens the **Hire** action.
2. HR enters hire date, position, department, facility assignment, and notes.
3. System sets application status to **hired** and copies applicant data into **bp_employees** (new employee record).
4. HR is redirected to the new employee profile to continue onboarding (checklists, documents).
5. Alternatively, HR selects **Reject**, enters reason, and application status becomes **rejected**.

### Business rules

- Hire creates/links employee number and assignment records.
- Rejected applicants cannot be hired without reversing status through admin process.
- Post-hire, employee may receive portal registration invite (Workflow 9).

### Key locations

- Routes: `admin.pre-employment.hire`, `admin.pre-employment.reject`
- Controller: `HireApplicantController`

---

## 9. Employee Portal Registration (Existing Staff)

**Status:** Complete

### Purpose

Give existing employees (already in `bp_employees`) access to the self-service portal.

### Roles

- **HR:** admin, super-admin, rdhr, facility-admin, facility-dsd (generates codes)
- **Employee:** registers with code

### Steps

1. HR confirms employee has an email on file and no linked portal user yet.
2. HR clicks **Generate Registration Code** on employee list or employee record.
3. System creates `E-XXXXXX` code (14-day expiry) and emails **Employee Registration Invite**.
4. Employee visits `/register/{code}` and completes registration.
5. Employee verifies identity with **employee number** or **SSN last 4 digits**.
6. System links `User` ↔ `BPEmployee` and assigns **regular-user** role.
7. Employee accesses **My Employment** portal.

### Business rules

- One active registration code per employee at a time (new code invalidates or supersedes prior as configured).
- Employee must match identity verification fields on file.

### Key locations

- Route: `POST admin/employees/{employee}/registration-code`
- Service: `RegistrationCodeService@generateForEmployee`
- Mail: `EmployeeRegistrationInviteMail`

---

## 10. Employee Record Management (HR)

**Status:** Complete

### Purpose

Maintain complete employee demographic, job, tax, and contact data.

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd (facility-scoped for facility roles)

### Steps

1. HR opens **Employees** list for a facility or global admin employees index.
2. HR creates new employee (`/admin/employees/create`) or opens existing employee edit.
3. HR works through tabbed sections:
   - **Personal** — name, DOB, SSN (restricted), email, demographics, hire dates, action (New Hire / Rehire)
   - **Address** — home/mailing addresses
   - **Job Data** — facility, department, position, compensation, effective dates, supervisor
   - **Tax Data** — W-4 and withholding
   - **Documents** — uploads and verification (Workflow 12)
   - **Checklist** — Parts C through G (Workflows 14–17)
4. HR saves each tab; validation enforces required fields and role-based edit rules.
5. HR manages phones and emergency contacts via dedicated endpoints.

### Business rules

- **Rehire Date** appears only when Action = Rehire; otherwise stored as NULL.
- SSN updates restricted to admin, rdhr, or self (with unmasked input only).
- Facility-admin/dsd cannot edit their **own** job data (read-only on own record); super-admin, admin, rdhr can.
- Assessment anchor date derives from rehire date (if Rehire action) or original hire date — see [HR Portal Business Rules](HR_PORTAL_BUSINESS_RULES.md).

### Key locations

- Controller: `EmployeesController`
- Views: `resources/views/admin/facilities/employee/`
- Authorization: `EmployeeJobDataAuthorization.php`

---

## 11. Employee Self-Service (My Employment)

**Status:** Complete

### Purpose

Allow employees to view and update their own profile and documents without full HR admin access.

### Roles

regular-user (linked to `bp_employees` record)

### Steps

1. Employee logs in and opens **My Employment** (`/my-employment`).
2. Employee sees the same tabbed layout as HR edit, in **read-only or limited-edit** mode:
   - Personal, address, tax, phones — editable where permitted
   - Job data — read-only for most employees
   - Documents — upload and submit for review (Workflow 12)
3. Employee saves changes; redirects stay within employment portal routes.
4. Employee downloads own documents via secure download routes.

### Business rules

- Self-service reuses `EmployeesController@edit` with `isSelfService = true`.
- Document edit/delete limited to documents the employee uploaded.
- Job data self-edit blocked except for elevated HR roles on own record.

### Key locations

- Controller: `EmploymentController`
- Routes: `employment.portal`, `employment.personal.update`, `employment.documents.*`

---

## 12. Employee Document Upload & DSD Verification

**Status:** Complete

### Purpose

Employees upload credentials and compliance documents; DSD verifies and approves or rejects them.

### Roles

| Action | Who |
|--------|-----|
| Upload | Employee (self) or HR staff |
| Submit for review | Employee (own uploads only) |
| Approve / Reject | admin, rdhr, facility-admin, **facility-dsd** |
| Expiry reminder (to employee) | HR staff (admin expiry notification flow) |

### Steps — Employee upload & submission

1. Employee (or HR) uploads document with **upload type**, file, optional effective/expiration dates, and comments.
2. Document appears in Documents table with status **Not submitted**.
3. Employee clicks **Submit for DSD review** (bell icon) on their own upload.
4. Employee selects **Reason for upload** (required):
   - Initial upload — new document
   - Renewal or replacement
   - Corrected or updated version
   - Required compliance submission
   - Updating an expiring document
5. Employee optionally edits email subject/message and submits.
6. System sets `verification_status = pending`, stores reason, emails facility **DSD** user(s).
7. Document status shows **Pending review**.

### Steps — DSD verification

1. DSD receives email with document details, reason, and link to employee Documents tab.
2. DSD opens employee record → **Documents** tab.
3. DSD reviews file (view/download).
4. DSD clicks **Approve** → status **Approved**, timestamp and verifier recorded.
5. Or DSD clicks **Reject**, enters **reason for rejection** (required) → status **Rejected**; employee sees rejection note.
6. If rejected, employee may upload corrected file and resubmit (returns to step 3).

### Business rules

- Only the **uploader** may edit or delete a document.
- DSD must have `facility-dsd` role and valid email at the document’s facility.
- Pending documents cannot be resubmitted until approved or rejected.
- Separate **expiry notification** flow (HR → employee) applies to documents expiring within 120 days; that is distinct from DSD submission review.

### Key locations

- Model: `Upload` (`verification_status`, `submission_reason`)
- Support: `UploadSubmissionReason`, `UploadNotificationContext`
- Mail: `EmployeeDocumentSubmissionMail`
- Routes: `admin.employees.documents.*`, `employment.documents.*`

---

## 13. Facility Document Management

**Status:** Complete

### Purpose

Browse and manage all employee documents across a facility from a central documents page.

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd, facility-editor

### Steps

1. HR opens `/admin/facility/{facility}/documents`.
2. HR filters by employee, upload type, expiry range, or filename.
3. HR uploads documents on behalf of employees (assign employee, type, file).
4. HR downloads, views, edits, or deletes facility-scoped uploads.
5. HR sends **expiry notifications** to employees for documents nearing expiration.

### Business rules

- Facility documents page shows cross-employee view; employee tab shows single-employee view.
- Expiry color coding: expired (red), ≤30 days (urgent), 31–120 days (warning), 120+ days (green).

### Key locations

- Controller: `QuickActionsController@documents`, `UploadController`
- View: `resources/views/admin/facilities/partials/upload-table.blade.php`

---

## 14. Employee Checklist — Parts C & D (HR Acknowledgements & Policies)

**Status:** Complete

### Purpose

Track HR verification of required acknowledgements (Part C) and policy sign-offs (Part D).

### Roles

- **HR verifier:** admin, rdhr, facility-admin, facility-dsd
- **Employee:** acknowledges/signs where required

### Steps

1. HR opens employee **Checklist** tab → Part C or Part D.
2. HR reviews each checklist item applicable to employee’s position.
3. HR opens verification modal, confirms completion, enters verifier info and dates.
4. System stores verification in `BPEmpChecklist` JSON structure.
5. HR can **revoke** a verification if completed in error.
6. Self-assessment prevention blocks employees from verifying their own checklist items as HR.

### Business rules

- Checklist items are position-applicable (configured in admin checklist items).
- Expiry dates tracked where item requires renewal.

### Key locations

- Controller: `EmployeesController@saveChecklistVerification`, `@revokeChecklistItem`
- Views: `employee-checklist-part_c.blade.php`, `employee-checklist-part_d.blade.php`

---

## 15. Part E — Orientation Checklist

**Status:** Complete

### Purpose

Track orientation completion for new and transferring employees.

### Roles

HR reviewers; employee completes orientation acknowledgements

### Steps

1. HR opens Part E on employee checklist or via facility dashboard shortcut.
2. System loads position-specific orientation items from `OrientationChecklistSource`.
3. Employee and/or HR complete orientation steps (sign-offs, dates).
4. HR verifies completed orientation items.
5. Orientation checklist marked complete when all required items satisfied.

### Key locations

- Livewire: `PartEOrientationChecklist`
- View: `employee-checklist-part_e.blade.php`

---

## 16. Part F — Performance Appraisal

**Status:** Complete

### Purpose

Conduct annual (or period-based) performance evaluations with scored areas, narratives, and PDF export.

### Roles

admin, rdhr, facility-admin, facility-dsd (reviewers); employee participates in narrative/sign-off where configured

### Steps

1. HR selects or creates an **assessment period** for the employee (Workflow 18).
2. HR opens Part F on employee checklist.
3. HR rates performance areas (Livewire performance appraisal sections).
4. HR enters supervisor narrative, areas for development, employee comments.
5. HR saves **draft** or **finalizes** assessment.
6. HR downloads performance assessment PDF.
7. HR may **revoke** finalized assessment if correction needed (with audit trail).

### Business rules

- Reviewers cannot perform self-assessment on their own employee record.
- Scoring uses `PartFPerformanceScoring` templates aligned to position.
- Assessment must reference valid assessment period for that employee.

### Key locations

- Controller: `EmployeePerformanceAssessmentController`
- Livewire: `PerformanceAppraisalAreas`
- Support: `PartFPerformancePdfItemsBuilder`, `PerformanceAppraisalTemplate`
- See [HR Portal Business Rules](HR_PORTAL_BUSINESS_RULES.md) for period date rules.

---

## 17. Part G — Competency Assessment

**Status:** Complete

### Purpose

Evaluate clinical and operational competencies by position (nursing skills, medication administration, CNA skills, DSD competencies, etc.).

### Roles

Same as Part F; employee signs completed assessment; reviewer signs after review

### Steps

1. HR selects assessment period and opens Part G.
2. HR completes position-specific competency sections (15+ specialized Livewire components).
3. HR saves section drafts; addresses below-expectations ratings with item-level review notes.
4. HR submits full competency assessment.
5. Employee signs (`employee-sign` endpoint); reviewer signs (`reviewer-sign` endpoint).
6. HR downloads full assessment PDF or individual section PDFs.
7. Competency history table shows prior assessments.

### Business rules

- Sections shown depend on employee position (RN, LVN, CNA, DSD, etc.).
- Draft responses persist per assessment period until submitted.

### Key locations

- Controller: `EmployeePerformanceAssessmentController` (competency methods)
- Livewire: `app/Livewire/Admin/Facilities/Checklist/PartGSections/*`
- Support: `PartGCompetencyScoring`, `CompetencyAssessmentHistoryBuilder`

---

## 18. Assessment Period Management

**Status:** Complete

### Purpose

Define the time window for Part F and Part G assessments tied to hire anniversaries or manual dates.

### Roles

Authenticated HR staff on employee record

### Steps

1. HR opens **Assessment Period** modal on employee checklist.
2. System suggests period based on hire/rehire anchor date (see business rules doc).
3. HR creates new period with review start/end dates or accepts suggested period.
4. HR selects active period for current Part F/G work.
5. HR deletes obsolete periods if no assessments attached (where permitted).

### Business rules

- Periods are employee-specific (`employee_num` FK).
- Cannot use another employee’s period ID on save (validated server-side).
- Anchor date: Rehire action + rehire date, else original hire date.

### Key locations

- Service: `EmployeeAssessmentPeriodService`, `EmployeeAssessmentPeriodCalculator`
- Routes: `admin.employees.performance-assessment.period`, `employees/{employee}/assessment-periods/modal-data`

---

## 19. Job Openings Management

**Status:** Complete

### Purpose

Publish and manage open positions per facility, linked to public careers pages.

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd, facility-editor

### Steps

1. HR opens `/admin/facility/{facility}/job-openings`.
2. HR creates job opening from template or scratch (title, description, department, status).
3. HR toggles active/inactive and changes posting status.
4. HR manages **job description templates** for reuse.
5. Public careers page displays active openings; applications flow to Workflow 3.

### Key locations

- Controller: `JobOpeningController`
- Routes: `admin.facility.job_openings.*`

---

## 20. Facility Hiring Hub

**Status:** Complete

### Purpose

Single page overview of hiring activity: openings, applications, pre-employment pipeline.

### Roles

Facility HR roles (admin, rdhr, facility-admin, facility-dsd, facility-editor)

### Steps

1. HR opens `/admin/facility/{facility}/hiring`.
2. HR views counts and lists: active job openings, pending applications, pre-employment in progress.
3. HR navigates to individual application or pre-employment review.
4. HR accesses hire modal prerequisites (positions, departments).

### Key locations

- Controller: `QuickActionsController@hiring`
- View: `resources/views/admin/facilities/hiring.blade.php`

---

## 21. Employee Data Import (Excel)

**Status:** Complete

### Purpose

Bulk import employee records from Excel workbooks with column mapping and duplicate detection.

### Roles

- **Import execution:** admin, rdhr, facility-admin, facility-dsd, facility-editor
- **Mapping preset admin:** admin, super-admin

### Steps

1. HR uploads Excel file via facility import UI.
2. HR maps spreadsheet columns to `bp_employees` (and related) fields.
3. HR saves mapping as preset for future imports (optional).
4. HR previews import; system flags duplicates.
5. HR confirms import; records created/updated in batch.
6. System admin manages global presets and import logs (revert where supported).

### Key locations

- Controllers: `FilesController`, `ImportMappingPresetController`
- Routes: `admin.facility.files.import*`

---

## 22. HR Reports & Scheduled Reports

**Status:** Complete

### Purpose

Run ad-hoc SQL-based reports and schedule recurring report delivery to HR roles/emails.

### Roles

| Action | Roles |
|--------|-------|
| Run/download reports | admin, super-admin, rdhr, facility-admin, facility-dsd |
| Build/edit report definitions | admin, super-admin |
| Schedule reports | admin, super-admin, rdhr, facility-admin, facility-dsd |

### Steps

1. HR opens facility **Reports** or **HR Portal Reports** (`/admin/hr-portal/reports`).
2. HR selects report, sets parameters, runs (PDF/CSV/JSON).
3. Admin creates/edits report SQL definitions (validated before save).
4. HR schedules recurring runs with notify roles/emails.
5. HR views scheduled run history, downloads archived outputs, archives old runs.

### Key locations

- Controllers: `ReportController`, `HrPortalReportsController`, `ScheduledReportController`
- Routes: `admin.reports.*`, `admin.scheduled-reports.*`

---

## 23. Positions, Departments & Checklist Configuration

**Status:** Complete

### Purpose

Maintain organizational structure and which checklist/competency items apply to each position.

### Roles

admin, super-admin, rdhr, facility-admin, facility-dsd

### Steps

1. HR administers **Departments** and **Positions** (`/admin/departments`, `/admin/positions`).
2. HR configures **Checklist Items** and assigns applicability by position.
3. HR bulk-assigns checklist items to multiple positions.
4. Position drives Part E orientation items, Part G competency sections, and hire modal defaults.

### Key locations

- Controllers: `DepartmentController`, `PositionController`, `ChecklistItemController`
- Seeders: `EmployeePerformanceItemsSeeder`, competency item seeders

---

## 24. Employee Email Mappings (Communications)

**Status:** Complete

### Purpose

Configure facility-level email routing for HR communication categories (e.g., document notifications).

### Roles

admin, facility-admin, rdhr, facility-dsd

### Steps

1. HR opens **Employee Email Mappings** (`/admin/communications/employee-email-mappings`).
2. HR sets primary contact email per facility and category.
3. System uses mappings for automated notifications where configured.

### Key locations

- Livewire: `Admin/EmployeeEmailMappings`

---

## 25. Member Dashboard (Employee Hub)

**Status:** Complete

### Purpose

Give employees a personalized home page with tasks, document reminders, and quick links.

### Roles

regular-user (employees/applicants)

### Steps

1. Employee logs in → `/dashboard` (member view).
2. Dashboard shows todos: documents needed, pending signatures, assessment reminders, checklist compliance gaps.
3. Quick links: My Employment, My Pre-Employment, documents, certifications, trainings, schedule, news.
4. `MemberDashboardService` aggregates data from uploads, checklists, and assessments.

### Key locations

- Controller: `DashboardController@memberDashboard`
- Service: `MemberDashboardService`
- View: `resources/views/dashboard/member/index.blade.php`

---

## 26. User, Role & Permission Administration

**Status:** Complete

### Purpose

Manage portal users, Spatie roles/permissions, and facility assignments.

### Roles

admin, super-admin (primary); facility-admin for facility user lists

### Steps

1. Admin opens Users, Roles, Permissions admin sections.
2. Admin creates/edits users, assigns roles and facility.
3. Admin uses bulk role assignment tools where available.
4. Facility admin views facility-scoped user list.

### Key locations

- Controllers: `AdminUserController`, `AdminRoleController`, `AdminPermissionController`
- Seeder: `RolePermissionSeeder` (8 roles)

---

## 27. Secure Staff Access to Sensitive Applicant Data

**Status:** Complete

### Purpose

Protect PHI/PII in job applications and inquiries via tokenized secure links and staff verification.

### Roles

- **Admin:** security monitoring, token regeneration
- **Staff:** verify identity before viewing secure application/inquiry/tour data

### Steps

1. System generates secure token link for sensitive record access.
2. Staff opens link; must pass verification step before viewing data.
3. Admin monitors access via security dashboard.
4. Admin regenerates tokens if link compromised.

### Key locations

- Controllers: `SecureJobApplicationController`, `SecureInquiryController`, `SecurityMonitoringController`

---

## 28. Arbitration Templates

**Status:** Partial

### Purpose

Store arbitration document templates for legal/HR use during hiring or employment.

### Roles

admin, super-admin

### Steps

1. Admin uploads/manages arbitration templates (`/admin/arbitration-templates`).
2. Admin downloads templates for facility or applicant use.
3. *(Planned)* Full applicant/facility arbitration signing workflow in portal.

### Key locations

- Controller: `ArbitrationTemplateController`
- DB: `facility_arbitration_documents`, `applicant_arbitration_documents`

---

## 29. Termination Management

**Status:** Partial (placeholder)

### Purpose

Process employee separations, exit checklist, and record status changes.

### Roles

Planned: facility-admin, rdhr, admin

### Current state

- Route exists: `/admin/facility/{facility}/termination`
- View is placeholder text only; no termination workflow implemented yet.

### Planned steps (not yet built)

1. HR initiates termination with last day worked and reason.
2. System triggers exit checklist and access revocation.
3. HR completes termination documentation and archive.

---

## 30. Attendance Tracking

**Status:** Not implemented

### Purpose

Track employee time and attendance by facility.

### Current state

Route commented out in `routes/web.php` (`admin.facility.attendance`). No UI or backend.

---

## 31. HIPAA Facility Checklist

**Status:** Partial

### Purpose

Track facility HIPAA compliance checklist items (website/forms security, etc.).

### Roles

admin, facility-admin, rdhr, facility-dsd

### Steps

1. Admin views HIPAA checklist index.
2. Facility manager completes interactive HIPAA checklist for assigned facility.
3. Admin toggles HIPAA flag on facility record.

### Current state

Views and routes exist; some controller TODOs remain on index presentation.

### Key locations

- Routes: `admin.hipaa-checklist.index`, `admin.facilities.hipaa.interactive`
- Support: `HipaaWebsiteChecklist`

---

## 32. End-to-End Hiring Pipeline Summary

```
Public Apply
    ↓
Staff Review (status: reviewed → interview)
    ↓
Pre-Employment (registration code T-XXXXXX + email)
    ↓
Applicant Portal (checklist forms)
    ↓
HR Pre-Employment Review (PDF, return/approve)
    ↓
Hire → Employee Record (bp_employees)
    ↓
Employee Registration (code E-XXXXXX) → My Employment
    ↓
Onboarding Checklists (C → D → E → F → G)
    ↓
Document Upload → DSD Submit for Review → Approve/Reject
```

---

## 33. Implementation Status Matrix

| # | Workflow | Status |
|---|----------|--------|
| 2 | HR Portal & facility selection | Complete |
| 3 | Public job application | Complete |
| 4 | Application review & status | Complete |
| 5 | Applicant registration codes | Complete |
| 6 | Pre-employment applicant portal | Complete |
| 7 | Pre-employment admin review | Complete |
| 8 | Hire / reject | Complete |
| 9 | Employee registration codes | Complete |
| 10 | Employee record management | Complete |
| 11 | My Employment self-service | Complete |
| 12 | Document upload & DSD verification | Complete |
| 13 | Facility document management | Complete |
| 14 | Checklist Parts C & D | Complete |
| 15 | Part E orientation | Complete |
| 16 | Part F performance appraisal | Complete |
| 17 | Part G competency assessment | Complete |
| 18 | Assessment period management | Complete |
| 19 | Job openings | Complete |
| 20 | Hiring hub | Complete |
| 21 | Excel employee import | Complete |
| 22 | Reports & scheduled reports | Complete |
| 23 | Positions & checklist config | Complete |
| 24 | Email mappings | Complete |
| 25 | Member dashboard | Complete |
| 26 | User/role admin | Complete |
| 27 | Secure applicant access | Complete |
| 28 | Arbitration templates | Partial |
| 29 | Termination | Placeholder |
| 30 | Attendance | Not implemented |
| 31 | HIPAA facility checklist | Partial |

---

## Document maintenance

When adding a new HR workflow to the portal:

1. Add a numbered section to this file (title, purpose, roles, steps, rules, status, key locations).
2. Update the [Implementation Status Matrix](#33-implementation-status-matrix).
3. Add cross-references in [HR_PORTAL_BUSINESS_RULES.md](HR_PORTAL_BUSINESS_RULES.md) if new business rules apply.
4. Update the end-to-end diagram if the workflow affects hiring or onboarding.

*Last updated: May 2026 — reflects document DSD verification, registration codes, and employee self-service rules.*
