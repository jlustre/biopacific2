# Bio-Pacific HR Portal User Manual

**Audience:** Employees, applicants, supervisors, department leaders, Directors of Nursing (DON), Directors of Staff Development (DSD), facility administrators, Regional HR (RDHR), administrators, and super-administrators  
**Application area:** Authenticated Bio-Pacific HR and employee portal  
**Revision date:** July 15, 2026

---

## 1. About this manual

This manual explains the live, authenticated HR portal sidebar and the workflows behind each menu link. The links a user sees are determined by:

- portal role and permissions;
- whether the user is linked to an employee or applicant record;
- the employee's current facility, department, and position;
- the currently selected facility for multi-facility users;
- whether the requested feature is enabled and its route is available.

The sidebar is organized into seven groups:

1. Personal
2. Facility
3. Company
4. Web Contents
5. Web Communications
6. Settings
7. Support

Some destinations appear in more than one group because they serve both personal and facility/company workflows. The application may hide a duplicate Leadership link when the Facility Leadership link is already available.

This is a user and operations manual. For detailed assessment-period rules and implementation references, also see:

- [HR Portal Workflow Reference](HR_PORTAL_WORKFLOWS.md)
- [HR Portal Business Rules](HR_PORTAL_BUSINESS_RULES.md)
- [Developer Guide](DEVELOPER_GUIDE.md)

### Contents

- [Roles and access levels](#2-roles-and-access-levels)
- [Portal navigation basics](#3-portal-navigation-basics)
- [Part I — Personal menu](#part-i--personal-menu)
- [Part II — Facility menu](#part-ii--facility-menu)
- [Part III — Company menu](#part-iii--company-menu)
- [Part IV — Web Contents menu](#part-iv--web-contents-menu)
- [Part V — Web Communications menu](#part-v--web-communications-menu)
- [Part VI — Settings menu](#part-vi--settings-menu)
- [Part VII — Support menu](#part-vii--support-menu)
- [Part VIII — End-to-end HR workflows](#part-viii--end-to-end-hr-workflows)
- [Part IX — Status quick reference](#part-ix--status-quick-reference)
- [Part X — Safety and troubleshooting](#part-x--safety-and-troubleshooting)

---

## 2. Roles and access levels

### Employee and applicant roles

- **regular-user / employee:** Personal tools, assigned facility news, galleries, documents, credentials, training, tasks, messages, and self-service employment records.
- **Applicant:** Pre-Employment tools until the applicant is hired and linked to an employee record.
- **Direct supervisor:** Personal tools plus assigned document/training review tasks and eligible employee assessments for direct reports.

### Facility leadership roles

- **facility-admin:** Facility-wide employee, hiring, document, training, reporting, leadership, and approved web-content workflows.
- **facility-dsd:** Facility-wide employee onboarding, document review, training review, assessments, reporting, leadership, and approved web-content workflows.
- **don:** Nursing/department leadership, employee oversight, documents, training, competencies, performance, positions, and other configured facility tools. Some core employee fields remain read-only.
- **ssd / activities-director / other department leaders:** Facility Dashboard access and department-focused oversight when their position or portal role qualifies.
- **facility-editor:** Selected facility content and operational routes. This role does not receive every HR leadership menu.

### Corporate and system roles

- **rdhr:** Multi-facility HR operations, facility selection, employee management, reports, imports, documents, training, leadership, and approved web/communications tools.
- **admin:** System-wide administration and all configured facilities.
- **super-admin:** Highest system-wide access, including administrative, security, backup, role, permission, and global configuration tools.

### Facility scoping

- Employees and facility leaders normally work within their assigned facility.
- DON and department leaders may see a department-only workforce scope.
- Admin, super-admin, and RDHR can generally choose a facility or work across facilities.
- Changing the selected facility changes the facility context used by many links.
- A visible link does not replace record-level authorization. The portal rechecks facility and employee access when opening protected records.

---

## 3. Portal navigation basics

### Opening and collapsing the sidebar

Use the hamburger button in the top bar to hide or show the sidebar. On smaller screens, the sidebar opens as a sliding menu.

### Expanding menu groups

Select a group heading to expand or collapse its links. The group containing the current page opens automatically.

### Badges

- **My Tasks:** Number of open system and personal tasks.
- **My Messages:** Number of messages requiring attention.
- **My Documents:** Number of document requirements needing attention.
- **Webmaster Issues / Portal Help Requests:** Number of unresolved requests for authorized administrators.

### My Dashboard versus Facility Dashboard

- **My Dashboard** is personal. It shows the current user's work, required documents, training, credentials, and tasks.
- **Facility Dashboard** is for leadership. It summarizes a facility or department and may include staff gaps, document compliance, assessments, and leadership information.

### My Employment versus Pre-Employment

- **My Employment** appears when the account is linked to an employee record.
- **Pre-Employment** appears when the account is still linked to an applicant/pre-employment record and has not resolved to an employee record.

---

# Part I — Personal menu

The Personal group is available to every authenticated portal user. Individual pages may show an empty state when the account is not linked to the required employee or applicant record.

## 4. My Dashboard

**Access:** All authenticated users  
**Purpose:** Personal work and compliance summary.

The dashboard includes:

- My open tasks;
- required checklist/training work;
- credentials requiring attention;
- required position documents;
- required position training;
- required licenses and certifications;
- quick links to common employee tools.

### Workflow

1. Review the four summary cards.
2. Open My Tasks for the complete action queue.
3. Expand the required-document, required-training, or credential panels.
4. Select Upload, Renew, Start, Continue, Revise, or the applicable action.
5. Return to the dashboard to confirm that completed and approved requirements no longer appear as outstanding.

The three requirement panels start collapsed and remember their expanded/collapsed state for the current browser session.

## 5. My Tasks

**Access:** All authenticated users  
**Purpose:** Unified queue for personal tasks and system-generated HR work.

Tasks may include:

- missing, expired, rejected, or pending documents;
- training modules and reviewer decisions;
- employee assessment acknowledgements;
- assigned profile or compliance actions;
- manually created personal tasks.

### Personal task workflow

1. Select **Create Task**.
2. Enter title, priority, due date, and optional description.
3. Assign the task to yourself or an eligible user in the same facility.
4. The assignee marks it complete.
5. If another user created it, the creator confirms completion.
6. Self-assigned tasks move directly to confirmed.

### Task states

- **Pending:** Work is still open.
- **Completed:** Assignee finished; creator confirmation may be required.
- **Confirmed:** Workflow is complete.
- **Cancelled:** Removed from the normal active queue.

System-generated workflow tasks should be completed through their linked document, training, or assessment page.

## 6. My Messages

**Access:** All authenticated users  
**Purpose:** Aggregated HR and workflow inbox.

Messages can come from:

- assigned personal tasks;
- document approval or rejection;
- training approval or return;
- help request updates;
- feedback replies or status changes;
- other portal alerts.

### Workflow

1. Filter by message source if needed.
2. Select a message to open its related record.
3. Complete the action on the linked page.
4. The attention badge updates from the underlying workflow status and recency.

My Messages does not use a conventional manual “mark read” process for every source. Attention is calculated from the source record.

## 7. My Profile

**Access:** All authenticated users  
**Purpose:** Portal identity, contact readiness, avatar, emergency contacts, and HR profile review.

### Employee workflow

1. Review account name and email.
2. Upload or remove a profile avatar.
3. Add emergency contacts and identify the primary contact.
4. Verify the account email after any email change.
5. Review official employee details displayed from the HR record.
6. Submit the profile for HR review when complete.

### Profile review states

- **Incomplete:** Required profile information is missing.
- **Pending HR:** Submitted and waiting for authorized leadership confirmation.
- **Confirmed:** HR review is complete.

Profile submission generally requires a name, verified email, and a complete primary emergency contact.

Official legal name, job, department, reporting line, and similar HR fields are usually maintained through My Employment or Employee Management rather than the portal profile.

## 8. My Employment

**Access:** Authenticated users linked to an employee record  
**Purpose:** Employee self-service view of the HR record.

Available tabs may include:

- Personal;
- Address;
- Job Data;
- Tax Data;
- Documents;
- Checklist.

### Workflow

1. Review personal and employment information.
2. Update fields that are enabled for employee self-service.
3. Add or update phone information.
4. Add a new effective-dated address or tax record where permitted.
5. Upload employee documents from the Documents tab.
6. Review checklist and employment status.

Historical address and tax records are normally read-only. Job Data is generally maintained by authorized HR staff. Approved and historical documents are read-only; use a new upload for renewals or corrections.

## 9. Pre-Employment

**Access:** Authenticated applicants without a linked employee record  
**Purpose:** Complete onboarding information before hire.

Typical checklist items include:

- application form;
- reference checks;
- medical examination;
- references;
- compliance forms.

### Applicant workflow

1. Open each checklist item.
2. Save drafts while information is incomplete.
3. Submit finished items for review.
4. Submitted items become read-only.
5. If HR returns an item, read the notes, revise, and resubmit.
6. Completed items move toward the final hiring decision.

### States

- **Draft:** Applicant can edit.
- **Submitted:** Waiting for review.
- **Returned:** Corrections required.
- **Completed:** Approved.

Hiring and rejection are performed by authorized HR roles after the pre-employment review.

## 10. My Documents

**Access:** All authenticated users; uploads require a linked employee, facility assignment, and applicable document type  
**Purpose:** Position-required documents, uploads, approval status, expiration, and history.

### Employee upload workflow

1. Select the required document type.
2. Select the reason for the submission.
3. Attach the file.
4. Enter effective and expiration dates when required.
5. Add comments if useful.
6. Submit the document.
7. The document becomes **Pending for Approval**.
8. A DSD, facility leader, or eligible supervisor reviews it.
9. If approved, it becomes compliant.
10. If rejected, read the required reviewer notes and upload a correction.

### Compliance states

- **Missing / Not on file:** Upload required.
- **Pending for Approval:** Uploaded but not yet approved.
- **Rejected:** Re-upload required.
- **Expired:** Renewal required.
- **Complete:** Approved and current.

### Version history

- New uploads supersede applicable current versions.
- Earlier files remain in history.
- Expiring documents are tracked by coverage year.
- Approved and historical versions cannot be overwritten by employee self-service.

## 11. My Checklists

**Access:** All authenticated users; content depends on employee position and assessment period  
**Purpose:** Training, orientation, competencies, performance records, acknowledgements, and checklist history.

The page separates:

- recurring/annual checklists;
- upon-hiring checklists.

### Employee-operated training workflow

1. Select the assessment period for recurring modules.
2. Open the module.
3. Select **Start Training**.
4. Complete the module.
5. Submit it with optional notes.
6. A DSD or supervisor reviews the submission.
7. Approval marks it completed.
8. A returned module shows **Returned — revise** and can be restarted and resubmitted.

### Training states

- Not started
- In progress
- Submitted for review
- Completed
- Returned / rejected
- N/A

Orientation, competencies, and performance rows may be read-only because leadership performs or advances those workflows. Completed assessment records may include downloadable PDFs.

## 12. My Credentials

**Access:** All authenticated users; requirements depend on the employee's position  
**Purpose:** Required licenses and certifications.

This is a credential-focused view of the same upload and approval process used by My Documents.

### Workflow

1. Review licenses and certifications required for the current position.
2. Upload missing credentials.
3. Include expiration dates when required.
4. Wait for facility review.
5. Re-upload rejected credentials.
6. Renew credentials before or after expiration.

### Credential states

- Valid
- Expiring soon
- Expiring urgently
- Expires today
- Expired
- Not on file
- Pending for Approval
- Rejected
- Missing expiration date

---

# Part II — Facility menu

The Facility group appears for facility-linked employees and users with facility operational access. Management links are filtered further by role and permission.

## 13. Facility Dashboard

**Access:** Leadership-qualified users, including configured facility, department, corporate, and system leaders  
**Purpose:** Facility or department operational overview.

The dashboard may include:

- workforce totals;
- compliance and action queues;
- required or expiring documents;
- pending document reviews;
- appraisals and competencies due;
- facility leadership;
- staff directory;
- public-site metrics.

### Scope

- Admin, super-admin, and RDHR can choose facilities.
- Facility-admin and facility-DSD normally receive facility-wide scope.
- DON and department leaders may receive department scope.

### Workflow

1. Confirm the selected facility.
2. Review action and awareness panels.
3. Open employee, document, or assessment links requiring attention.
4. Use the staff directory or leadership panel for contact and reporting context.

## 14. Facility Leadership

**Access to view:** Authenticated users  
**Access to edit:** Admin, super-admin, RDHR, facility-admin, and facility-DSD within permitted facilities  
**Purpose:** Facility leadership roster and assignments.

### Workflow

1. Select a facility.
2. Review standard leadership roles.
3. Authorized users assign or update leaders.
4. Add custom leadership assignments when needed.
5. Remove assignments only when business rules permit.

Standard roles may synchronize legacy facility fields. Populated standard roles cannot always be removed until related employee or assignment references are resolved.

## 15. Employee Management

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, and other users with HR Portal permission; DON may receive department-scoped/read-only behavior  
**Purpose:** Employee roster and complete HR record administration.

### Roster workflow

1. Select the facility if prompted.
2. Search or filter employees.
3. Open an employee record.
4. Move through Personal, Address, Job, Tax, Documents, and Checklist tabs.
5. Save changes only in tabs permitted for the current role.

### Common actions

- create and maintain employee records;
- assign facility, department, position, and supervisor;
- generate employee registration invitations;
- upload and review documents;
- manage orientation and training;
- perform competencies and appraisals;
- import employees using a mapping preset.

### Employee registration invitation

1. Confirm the employee has a valid work email and no linked portal user.
2. Select the registration-code action.
3. The system generates an `E-XXXXXX` code with an expiration date.
4. The employee receives an invitation email.
5. Registration verifies invitation details and employee identity.
6. The new user is linked to the employee and receives a position-derived role when configured.

## 16. Positions

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, DON, or users with View Positions permission  
**Purpose:** Global position catalog and reporting structure.

### Workflow

1. Search or filter by department.
2. Create or edit the position title and description.
3. Assign a department and reporting position.
4. Review required document counts.
5. Open Documents Settings to configure requirements.
6. Copy requirements from one position to others when appropriate.

Position changes are global and can affect employees at every facility.

## 17. Trainings

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, and DON  
**Purpose:** Facility training queue and employee Part H workflow.

### Leadership workflow

1. Select the facility.
2. Review employee training status.
3. Filter by employee, department, or attention state.
4. Open the employee's Part H record.
5. Review submitted modules.
6. Approve or return the submission with a reason.

### Training approval states

`Not started → In progress → Submitted → Completed`

Returned work follows:

`Submitted → Rejected → In progress → Submitted`

Reviewers cannot approve their own training. Review decisions generate tasks, email when available, and My Messages updates.

## 18. Documents

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, DON; selected routes may also allow facility-editor  
**Purpose:** Facility document operations and employee document review.

### Workflow

1. Select a facility.
2. Search or filter current employee uploads.
3. Open a file for review.
4. Approve or reject pending employee submissions.
5. Enter required rejection notes when rejecting.
6. Use employee document history for prior versions.
7. Follow up on missing and expired requirements.

Facility-created uploads are recorded with a review status according to the active administrative workflow. Employee self-service uploads always require review.

## 19. Competencies

**Access:** HR Portal roles and eligible supervisors; employee self-assessment is prohibited  
**Purpose:** Part G competency assessment.

### Workflow

1. Select the employee and assessment period.
2. Reviewer completes ratings and required comments.
3. Reviewer submits for employee confirmation.
4. Employee reads the assessment and saves acknowledgement.
5. Status advances to reviewer approval.
6. Reviewer approves.
7. Completed assessment is locked and a final record/PDF may be generated.

### Status sequence

`In Progress → For Employee confirmation → For Reviewer approval → Completed`

An employee may send the assessment back. An authorized reviewer may reopen a completed assessment.

## 20. Performance

**Access:** HR Portal roles and eligible supervisors; employee self-assessment is prohibited  
**Purpose:** Part F performance appraisal.

### Workflow

1. Select the employee and assessment period.
2. Complete applicable ratings, narrative, and development areas.
3. Submit for employee confirmation.
4. Employee acknowledges or sends it back.
5. Reviewer approves.
6. Completed records become read-only unless reopened.

Performance and competency assessments use the same four-stage workflow. Annual periods are based on the employee's original-hire or qualifying rehire anniversary.

## 21. Reports Management

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD; selected report routes also permit DON and facility-editor  
**Purpose:** Run and manage authorized operational reports.

### Report-user workflow

1. Browse reports visible to the current role/facility.
2. Open a report.
3. Enter required parameters.
4. Run the report.
5. View or download table, CSV, JSON, or PDF output where supported.

Facility-bound users have facility parameters enforced. Only admin and super-admin should manage report SQL definitions, visibility, and seeder exports.

## 22. Scheduled Report Runs

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD  
**Purpose:** Review generated scheduled-report output.

### Workflow

1. Filter runs by schedule, facility, status, or date.
2. Open run details.
3. Download available output.
4. Investigate failures.
5. System administrators may archive/delete runs according to the page controls.

Deleting a run may also delete its stored result file.

## 23. Arbitration Templates

**Access:** Admin and super-admin  
**Purpose:** Facility-specific arbitration document templates.

### Workflow

1. Select a facility.
2. Upload a PDF or DOCX template.
3. View or download the active template.
4. Replace it when a new version is approved.
5. Delete it only when it should no longer be available.

Replacing or deleting a template also removes the corresponding stored file.

## 24. News/Events

**Access:** Facility-linked users and facility operations roles  
**Purpose:** Read company-wide and facility news.

### Workflow

1. Filter All, Company-wide, or Facility.
2. Search title or content.
3. Open an item to read details.

The current employee page is primarily a News/announcement reader even though the label includes Events.

## 25. Photo Galleries

**Access to read:** Authenticated facility-linked users  
**Access to manage:** Authorized facility operations users and system administrators, subject to gallery ownership/facility rules  
**Purpose:** Internal facility photo albums.

### Reader workflow

1. Search or filter galleries by year.
2. Open an album.
3. Browse photos and captions.

### Manager workflow

1. Create an album in the owning facility.
2. Set title, year, description, linked event, visibility, and publication state.
3. Upload photos and captions.
4. Edit or delete albums and images you are authorized to manage.
5. Share the gallery with additional facilities when appropriate.

Shared galleries are read-only outside the owning facility.

---

# Part III — Company menu

## 26. Admin Dashboard

**Access:** Admin and super-admin from the live sidebar  
**Purpose:** System-wide administrative summary and shortcuts.

Use this dashboard to review facilities, users, reports, security, imports, and other global administration areas. Other leadership roles use My Dashboard or Facility Dashboard for their operational view.

## 27. Facilities

**Access from sidebar:** Admin and super-admin  
**Purpose:** Facility identity, public-site configuration, branding, contacts, services, and operational state.

### Workflow

1. Open the facility list.
2. Create or edit a facility.
3. Configure name, number, address, contacts, branding, domains, layout, services, and public-site settings.
4. Preview the public site.
5. Use seeder export only when the configuration should be restored by future database seeding.

Facility updates affect public and HR portal behavior. Treat shutdown, domain, branding, and seeder actions as administrative changes.

## 28. Bio-Pacific Websites

**Access:** All authenticated users  
**Purpose:** Directory of Bio-Pacific facility websites.

Select a facility to open its public website. The available URL may use a custom production domain or an operational slug URL.

## 29. Leadership

**Access:** All authenticated users for reading  
**Purpose:** Company/facility leadership directory.

This link opens the same leadership area described under Facility Leadership. It may be hidden when Facility Leadership is already present.

## 30. News & Events

**Access:** All authenticated users  
**Purpose:** Company and facility announcements.

Most users open the employee news reader. Web-content managers are directed to News administration so they can create and publish content.

## 31. Photo Galleries

**Access:** All authenticated users, with facility visibility rules  
**Purpose:** Internal company and facility photo albums.

This opens the same gallery destination described under the Facility group.

---

# Part IV — Web Contents menu

The Web Contents group is visible to admin, super-admin, RDHR, facility-admin, and facility-DSD. Facility users should operate only in their authorized facility context.

## 32. Testimonials

**Purpose:** Public facility testimonials.

### Workflow

1. Select a facility.
2. Create or edit the person's name, title/relationship, rating, testimonial text, and photo.
3. Set active and featured status.
4. Save and verify the public page.
5. Delete obsolete entries when authorized.

## 33. FAQs

**Purpose:** Public frequently asked questions.

### Workflow

1. Select a facility.
2. Review shared defaults and facility-specific FAQs.
3. Create or edit facility questions and answers.
4. Order or activate entries as supported.
5. Delete only facility-owned entries you are authorized to manage.

Shared default FAQs may be read-only for facility users.

## 34. Galleries

**Purpose:** Public website gallery administration.

### Workflow

1. Select a facility.
2. Upload and manage public gallery images.
3. Edit metadata or ordering.
4. Remove individual images as needed.
5. Use **Clear Facility** only when all facility gallery records and stored images should be permanently removed.

This management area is distinct from the employee-facing Photo Galleries page.

## 35. News

**Purpose:** Create and publish company/facility announcements.

### Workflow

1. Filter by status or facility.
2. Create title, summary, body, image, and publication date.
3. Choose global or facility assignments.
4. Save as draft or publish.
5. Edit, remove an image, or delete the news item as authorized.

Published, portal-visible content appears in the employee News & Events page.

## 36. Blogs

**Purpose:** Public website articles.

### Workflow

1. Create article metadata and body.
2. Assign facilities.
3. Add an image and publication date.
4. Set draft/published status.
5. Edit or delete as authorized.

## 37. Careers

**Purpose:** Public job openings and application intake.

### Job-opening workflow

1. Select a facility.
2. Create a job opening and position details.
3. Publish it to the facility careers page.
4. Review submitted applications.
5. Update application status and advance eligible applicants.
6. Close or delete openings no longer accepting applications.

## 38. Services

**Purpose:** Public facility services.

### Workflow

1. Filter global and facility-specific services.
2. Create or edit service name, description, image/icon, and active state.
3. Assign the service to facilities.
4. Verify the public facility page.
5. Remove a service only after checking facility associations.

---

# Part V — Web Communications menu

The Web Communications group is generally available to admin, super-admin, RDHR, facility-admin, and facility-DSD. Some subpages are restricted further.

## 39. Tour Requests

**Purpose:** Review public tour-booking requests.

### Workflow

1. Filter requests.
2. Open requester and preferred-tour details.
3. Contact the requester.
4. Update information/status where supported.
5. Delete obsolete records according to retention policy.

## 40. General Inquiries

**Purpose:** Review general public contact submissions.

### Workflow

1. Filter by facility or status.
2. Open the inquiry.
3. Respond using the appropriate communication channel.
4. Delete only when permitted by the organization's retention policy.

## 41. Webmaster Issues

**Access:** Admin and super-admin  
**Purpose:** Technical/public-site issue queue.

### Workflow

1. Filter by status, category, source, urgency, facility, or search.
2. Open the issue; it is marked read.
3. Add administrator comments or replies.
4. Move it through Open, In Progress, and Resolved.
5. Delete only when the record is no longer required.

## 42. Portal Help Requests

**Access:** Admin and super-admin in the sidebar; RDHR may have route access  
**Purpose:** Employee HR/technical support queue.

### Workflow

1. Filter by request type, category, facility, or status.
2. Open the request.
3. Investigate and contact the requester.
4. Set Open, In Progress, or Resolved.

## 43. Job Applications

**Purpose:** Central applicant review queue.

### Status workflow

`Pending → Reviewed → Interview → Pre-Employment → Hired or Rejected`

### Workflow

1. Filter applications by facility, opening, or status.
2. Review application details and résumé.
3. Update status.
4. Generate an applicant registration invitation when moving to pre-employment.
5. Review pre-employment submissions.
6. Authorized HR staff hire or reject the applicant.

Hiring creates or links employee data and assignment records. DSD and content roles may review but do not necessarily have final hire/reject authority.

## 44. Email Recipients

**Purpose:** Configure facility-specific recipient addresses for automated communications.

### Workflow

1. Select or filter by facility.
2. Create the recipient and communication purpose.
3. Verify the email address.
4. Edit or delete obsolete mappings.

Changes can affect who receives applicant, portal, and website communications.

## 45. Email Templates

**Purpose:** Reusable message templates.

### Workflow

1. Create a template with subject and body.
2. Use supported placeholders.
3. Preview content.
4. Use the template when replying where supported.
5. Edit or delete obsolete templates.

Confirm placeholder output and recipient information before sending.

## 46. Scheduled Reports

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, and selected DON workflows  
**Purpose:** Configure automatic report execution and delivery.

### Workflow

1. Choose a report or schedule template.
2. Set facility, parameters, recipients, and frequency.
3. Save the schedule.
4. Use **Run Now** for immediate testing.
5. Review run history and output.
6. Edit, disable, or delete the schedule as authorized.

## 47. Employee Email Mappings

**Purpose:** Map employee/leadership email recipients to communication types.

### Workflow

1. Select the mapping category or facility.
2. Add or update recipient assignments.
3. Save.
4. Test the related communication workflow.

This area also supports recipient configuration used by portal-help communications.

---

# Part VI — Settings menu

The Settings group mixes personal settings with administrative configuration. Personal links are available to everyone; the remaining links appear only when the user's role passes the corresponding gate.

## 48. Appearance

**Access:** All authenticated users  
**Purpose:** Choose Light, Dark, or System theme.

The preference is client-side and does not require HR approval.

## 49. Assignment Statistics

**Access:** Admin and super-admin  
**Purpose:** Role distribution and users without roles.

Use this page to review assignment totals before changing roles or permissions.

## 50. BAA Vendor Registry

**Access from sidebar:** Admin and super-admin  
**Purpose:** Track vendors with electronic protected health information access and Business Associate Agreements.

### Workflow

1. Create a vendor/service entry.
2. Record facility, ePHI access, BAA status, and notes.
3. Upload the BAA document where applicable.
4. Update renewal/status details.
5. Delete only according to compliance retention requirements.

## 51. Backup & Restore

**Access:** Admin and super-admin  
**Purpose:** Create, download, audit, and restore backups.

### Workflow

1. Review backup status and history.
2. Create a backup.
3. Confirm completion and storage location.
4. Download an archive when required.
5. Use Restore only after validating the backup and understanding the affected data.
6. Complete every restore confirmation safeguard.

Restore is a destructive operation and may replace application/database data.

## 52. Departments

**Access from sidebar:** Admin and super-admin  
**Purpose:** Global facility/corporate department catalog.

### Workflow

1. Search or filter departments.
2. Create or edit name, type, and description.
3. Review attached positions.
4. Reassign positions before deleting a department.
5. Use **Update Seeder** only when the current catalog should survive `migrate:fresh --seed`.

## 53. Documents Settings

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, DON  
**Purpose:** Global document types, employee-file items, and position requirements.

### Position requirements

1. Select a position.
2. Review all available document types.
3. Mark required documents.
4. Review expiry and license/certification flags.
5. Save.
6. Use bulk assignment or overview tools for larger changes.

Requirements are global by position and apply across facilities.

### Employee file items

1. Create or edit employee-file checklist items.
2. Assign sections and position applicability.
3. Configure expiry and document type behavior.
4. Use the employee-file seeder export when required.

### All document types

1. Create/edit general document types.
2. Configure departments, expiry, and credential classification.
3. Admin/super-admin can permanently delete general types.
4. DSD and other document-management roles archive general types.
5. Checklist-linked types must be managed through Employee File Items.

### Seeder controls

- **Apply Seeder:** Loads definitions from seeder files into the database.
- **Update Seeder:** Overwrites the corresponding source data file with current settings.

Commit exported files to source control if they must persist across deployments or fresh migrations.

## 54. Events

**Access from sidebar:** Admin and super-admin  
**Purpose:** Dated company/facility event definitions.

Create, edit, and delete events. Confirm whether an event is used by galleries or public content before deletion.

## 55. HIPAA Checklist

**Access from sidebar:** Admin and super-admin  
**Purpose:** Track organizational HIPAA compliance activities.

Review each control, supporting evidence, owner, and status. Follow the organization's security and compliance process for remediation and sign-off.

## 56. Import History

**Access:** Admin and super-admin  
**Purpose:** Audit and, where supported, revert employee/data imports.

### Workflow

1. Filter by facility, file, user, preset, or status.
2. Open an import log.
3. Review affected tables and row-level changes.
4. Select **Revert** only when the imported changes should be undone.
5. Select **Delete History** only when the audit record—not imported data—should be removed.

Running imports cannot be deleted. Revert and Delete History are different actions.

## 57. Import Preset Management

**Access:** Admin and super-admin  
**Purpose:** Define reusable spreadsheet-to-database mappings.

### Workflow

1. Create a global or facility preset.
2. Upload a workbook.
3. Select the worksheet.
4. Map spreadsheet columns to approved database fields.
5. Validate the mapping.
6. Save or duplicate the preset.
7. Run a test import and review Import History.
8. Confirm overwrite only after reviewing collisions.

Seeder export updates the preset seeder source file.

## 58. Invite Management

This item is configured but is not shown because its named route is not currently registered. Employee and applicant invitations are generated from Employee Management and Job Applications.

## 59. Manage Permissions

**Access:** Admin and super-admin  
**Purpose:** Permission catalog and role capability building blocks.

### Workflow

1. Search or categorize permissions.
2. Create or edit a permission.
3. Assign permissions to roles.
4. Review users/roles affected before deletion.

Core administrative permissions and permissions assigned to roles are protected from deletion.

## 60. Manage Roles

**Access:** Admin and super-admin  
**Purpose:** Portal role definitions and permission sets.

### Workflow

1. Create or edit a role.
2. Assign permissions.
3. Review users assigned to the role.
4. Update the role-permission seeder when required.
5. Delete only unused, non-protected roles.

The `admin` and `super-admin` roles are protected.

## 61. Password

**Access:** All authenticated users  
**Purpose:** Change the current account password.

Enter the current password and a valid new password. Use a unique password and follow organizational security requirements.

## 62. Position Portal Roles

**Access:** Admin and super-admin  
**Purpose:** Automatic role assignment during employee registration.

### Workflow

1. Select a position.
2. Choose the portal role employees in that position should receive.
3. Activate or deactivate the mapping.
4. Use **Sync Defaults** to import configured defaults.
5. Use **Update Seeder** to preserve mappings for fresh database seeding.

Mappings are global.

## 63. Role Assignments

**Access:** Admin and super-admin  
**Purpose:** Assign one or more roles to users.

### Workflow

1. Search for users.
2. Open a user assignment.
3. Add or remove roles.
4. Use quick or bulk assignment when appropriate.
5. Review Assignment Statistics afterward.

Removing all roles can remove portal capabilities. Verify administrative coverage before changing admin users.

## 64. Reports Management

This is the same report catalog described in the Facility group. It appears under Settings for users who manage or regularly run reports.

## 65. Scheduled Report Runs

This is the same run-history destination described in the Facility group.

## 66. Security Monitoring

**Access from sidebar:** Admin and super-admin  
**Purpose:** Secure-access logs, anomalies, incidents, and cleanup.

### Workflow

1. Filter logs by facility, status, or token/record type.
2. Investigate anomalies.
3. Mark incidents under review or resolved.
4. Export records when needed.
5. Use cleanup only after setting precise filters and confirming irreversible deletion.

## 67. System Settings

**Access from sidebar:** Admin and super-admin  
**Purpose:** Global application settings such as site identity, contact email, theme, and feature flags.

Review every field before saving. These values can affect the entire application.

## 68. Training Configuration

**Access:** Admin, super-admin, RDHR, facility-admin, facility-DSD, DON  
**Purpose:** Global training-module catalog and position assignments.

### Workflow

1. Create or edit a training module.
2. Set name, description, provider, content URL, frequency, order, and active state.
3. Assign all positions or selected positions.
4. Use bulk position assignment for multiple modules.
5. Test the module from My Checklists.
6. Admin/super-admin can update the training seeder.

Deleting a module may also remove its completion records. Treat deletion as destructive.

## 69. Users

**Access from sidebar:** Admin and super-admin  
**Purpose:** Portal user accounts, roles, facility assignment, email, and password administration.

### Workflow

1. Search/filter users.
2. Create a user with role and facility.
3. Edit name, email, role, facility, or password.
4. Use Role Assignments for multi-role management.
5. Delete only after checking employee, applicant, task, and audit relationships.

---

# Part VII — Support menu

## 70. My Help Requests

**Access:** All authenticated users  
**Purpose:** View submitted HR and technical help requests and their statuses.

Open a request to review its conversation/status. Use Contact HR or Technical Support to create a new request.

## 71. Contact HR

**Access:** All authenticated users  
**Purpose:** Submit employment, payroll, benefits, onboarding, or other HR questions.

### Workflow

1. Select the HR request category.
2. Describe the issue clearly.
3. Attach or reference supporting information only when appropriate.
4. Submit.
5. Follow progress in My Help Requests and My Messages.

Do not include unnecessary sensitive information.

## 72. Manuals and Docs

**Access:** All authenticated users  
**Purpose:** Portal documentation and guidance.

Use this page to find current manuals, policies, and workflow references.

## 73. Technical Support

**Access:** All authenticated users  
**Purpose:** Report login, browser, page, upload, or other technical problems.

Include the page, time, action attempted, error text, and a screenshot when appropriate. Do not expose passwords or sensitive employee data in screenshots.

## 74. Report Issue or Idea

**Access:** All authenticated users  
**Purpose:** Submit application defects, usability concerns, or enhancement ideas.

### Workflow

1. Choose issue or idea.
2. Describe expected and actual behavior.
3. Include reproduction steps.
4. Submit.
5. Review replies/status updates in My Messages.

---

# Part VIII — End-to-end HR workflows

## 75. Applicant-to-employee workflow

1. Publish a job opening under Careers.
2. Applicant submits an application.
3. HR reviews it under Job Applications.
4. Move the application through review/interview.
5. Set it to Pre-Employment and issue a `T-XXXXXX` registration code.
6. Applicant registers and completes Pre-Employment.
7. HR reviews, returns, or approves checklist items.
8. Authorized HR staff hire or reject.
9. Hiring creates/transfers the employee and assignment records.
10. Issue an employee `E-XXXXXX` portal invitation if needed.
11. Employee registers and begins My Employment, documents, training, and credentials.

## 76. Document compliance workflow

1. Configure document types in Documents Settings.
2. Assign required documents to positions.
3. Employee sees outstanding requirements on My Dashboard/My Documents.
4. Employee uploads a document.
5. Review task goes to eligible leadership/supervisor.
6. Reviewer approves or rejects.
7. Approval removes the current requirement from outstanding panels.
8. Rejection creates correction messaging and requires re-upload.
9. Expiration returns the requirement to action-needed status.
10. Version history remains available.

## 77. Position training workflow

1. Configure modules in Training Configuration.
2. Assign modules to positions.
3. Employee sees required training on My Dashboard/My Checklists.
4. Employee starts and submits the module.
5. Reviewer approves or returns it.
6. Completion updates the dashboard and training history.
7. Recurring training becomes due again according to frequency and assessment period.

## 78. Credential workflow

1. Mark an applicable document type as a license/certification.
2. Assign it to the position.
3. Employee uploads it with expiration details.
4. Reviewer approves or rejects through document verification.
5. Approved credentials become Valid.
6. The portal warns as expiration approaches.
7. Employee uploads a renewal.
8. A newer pending renewal remains outstanding until approved.

## 79. Performance and competency workflow

1. Confirm employee hire/rehire date and assessment period.
2. Reviewer completes Part F or Part G.
3. Submit for employee confirmation.
4. Employee acknowledges or sends back.
5. Reviewer approves.
6. Completed assessment is locked.
7. Reopen only when correction is necessary.

## 80. Employee import workflow

1. Create/choose an approved import preset.
2. Open Employee Management and select Import Employees.
3. Upload the workbook.
4. Select worksheet and target facility.
5. Validate mappings.
6. Review errors or collisions.
7. Run the import.
8. Confirm overwrite only when intended.
9. Review Import History.
10. Revert only when the imported data must be undone.

## 81. Seeder-update workflow

Seeder buttons are operational source-code exports.

1. Make and verify configuration changes in the database.
2. Select **Update Seeder**.
3. Confirm the target file.
4. Review the generated file.
5. Commit it to source control.
6. Test `migrate:fresh --seed` in a safe environment.

Seeder exports exist for areas including departments, document types, checklist items, position requirements, training modules, position portal roles, galleries/media, reports, and import presets.

---

# Part IX — Status quick reference

## Documents

- Missing / Not on file
- Pending for Approval
- Rejected — re-upload required
- Expired
- Complete / Approved

## Credentials

- Valid
- Expiring soon
- Expiring urgently
- Expires today
- Expired
- Not on file
- Pending for Approval
- Rejected
- Missing expiration date

## Training

- Not started
- In progress
- Submitted for review
- Completed
- Returned / rejected
- N/A

## Performance and competency

- In Progress
- For Employee confirmation
- For Reviewer approval
- Completed

## Pre-employment

- Draft
- Submitted
- Returned
- Completed

## Personal tasks

- Pending
- Completed, awaiting confirmation
- Confirmed
- Cancelled

---

# Part X — Safety and troubleshooting

## 82. If a menu link is missing

Check:

1. Is the user signed in with the expected account?
2. Is the account assigned the correct role?
3. Is the employee linked to a current facility, department, and position?
4. Is a facility selected for a multi-facility user?
5. Does the user have the required permission?
6. Is the feature route enabled?

Contact an administrator rather than using a direct URL to bypass a missing menu.

## 83. If employee requirements are empty

Check:

- employee account linkage;
- current job assignment;
- position assignment;
- position document requirements;
- training position assignments;
- license/certification classification.

## 84. If an upload is still outstanding

An upload remains outstanding when it is:

- pending approval;
- rejected;
- expired;
- missing a required expiration date;
- superseded by a newer pending renewal.

Open My Documents or My Credentials for details and reviewer notes.

## 85. If facility data looks incorrect

Confirm the selected facility in the portal and the user's assigned facility. Stop before editing if the page appears to show an unexpected facility, and contact an administrator.

## 86. Destructive actions

Use extra care with:

- permanent document-type deletion;
- department, position, user, content, or template deletion;
- import reversion;
- gallery clearing;
- backup restore;
- security-log cleanup;
- seeder overwrite/apply actions;
- report SQL configuration.

Read every confirmation message and verify the selected facility and record.

## 87. Privacy and security

- Access only records required for assigned duties.
- Do not share portal credentials or registration codes.
- Do not place unnecessary sensitive data in comments, help requests, or screenshots.
- Download employee/applicant documents only when operationally necessary.
- Follow retention requirements for applicant, employee, BAA, security, and communication records.
- Report suspected inappropriate access through Technical Support or the security/incident process.

---

## 88. Documentation maintenance

Update this manual whenever:

- sidebar groups or labels change;
- route roles or permission gates change;
- facility scoping changes;
- a workflow adds or removes statuses;
- a new seeder action is introduced;
- document, training, credential, hiring, or assessment workflows change.

The live navigation definitions are maintained in `config/member-portal.php`, with gate behavior in `app/Support/MemberPortalLayout.php`.
