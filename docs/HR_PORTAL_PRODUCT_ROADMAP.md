# HR Portal Product Experience Roadmap

This roadmap is a prioritized future-development backlog based on the application-wide review of navigation, workflows, roles, facility scoping, reliability, and user experience.

## Priority and effort guide

- **P0 — Secure first:** Security, authorization, and facility-isolation work that should precede feature expansion.
- **P1 — Next delivery:** Core workflow, usability, accessibility, and reliability improvements.
- **P2 — Growth:** Operational intelligence, automation, and communication enhancements.
- **P3 — Strategic:** Longer-term platform and integration capabilities.
- **S:** A focused change in one bounded workflow.
- **M:** A multi-file change requiring targeted tests and rollout.
- **L:** A cross-cutting initiative requiring design, migration, testing, and staged delivery.

## Recommended sequence

1. Complete all P0 authorization and facility-isolation work.
2. Stabilize automated testing, browser coverage, and operational monitoring.
3. Improve the highest-volume employee and leadership workflows.
4. Add analytics, automation, and proactive compliance tools.
5. Begin strategic platform and integration work.

---

## P0 — Security and access

- [ ] **Enforce facility scope on every facility document query** — Effort: **M**
  - Ensure facility users cannot list, view, download, or modify another facility's files.

- [ ] **Add target-facility checks to training approval and rejection** — Effort: **M**
  - Verify both the reviewer's role and their employee, direct-report, or facility scope.

- [ ] **Protect all assessment mutation routes** — Effort: **M**
  - Require authentication and record-level authorization for period deletion, Part F/G saves, signatures, and workflow actions.

- [ ] **Authorize assessment PDF and employee-file downloads** — Effort: **M**
  - Check employee, facility, and reviewer scope before serving sensitive files.

- [ ] **Retire or secure legacy import endpoints** — Effort: **M**
  - Apply consistent preset, role, facility, validation, and audit controls to every workbook and import endpoint.

- [ ] **Align sidebar gates, middleware, policies, and permissions** — Effort: **L**
  - Generate and test an access matrix proving that visible links and callable endpoints agree for every role.

- [ ] **Add policies to global administration CRUD operations** — Effort: **L**
  - Define explicit system and facility authorization for users, facilities, settings, events, BAA records, blogs, and security pages.

- [ ] **Audit all sensitive and destructive actions** — Effort: **L**
  - Record the actor, facility, target, before-and-after values, timestamp, reason, and originating request.

---

## P1 — Workflow user experience

- [ ] **Create a unified Approval Work Center** — Effort: **L**
  - Let DSDs, DONs, supervisors, and administrators review documents, training, profiles, and assessments from one facility-scoped queue.

- [ ] **Add human-readable workflow timelines** — Effort: **M**
  - Show who submitted an item, its current owner, reviewer notes, next action, and complete status history.

- [ ] **Upgrade My Messages into a managed inbox** — Effort: **L**
  - Add read/unread state, archive, snooze, preferences, and direct links to the records needing attention.

- [ ] **Add applicant and new-employee onboarding wizards** — Effort: **L**
  - Provide a guided checklist showing progress, blockers, ownership, due dates, and the next recommended action.

- [ ] **Add global search and command navigation** — Effort: **L**
  - Allow authorized users to quickly find employees, applicants, facilities, tasks, documents, reports, and settings.

- [ ] **Remove duplicate and dormant navigation** — Effort: **M**
  - Give Leadership, galleries, reports, and legacy sidebar definitions one predictable destination each.

- [ ] **Expand safe bulk operations** — Effort: **L**
  - Support bulk requirement assignment, renewal requests, reminders, and reviews with previews, confirmation, and rollback.

- [ ] **Autosave long forms and assessments** — Effort: **M**
  - Preserve drafts across refreshes and timeouts while displaying save status and handling edit conflicts.

- [ ] **Modernize the document-upload experience** — Effort: **M**
  - Add drag-and-drop, upload progress, file validation, preview, duplicate detection, and clear failure recovery.

- [ ] **Add contextual help from the user manual** — Effort: **M**
  - Link every major page to role-specific instructions, status definitions, troubleshooting, and support.

- [ ] **Complete accessibility and mobile audits** — Effort: **L**
  - Test keyboard navigation, screen readers, focus order, contrast, responsive tables, and touch interactions against WCAG 2.2 AA.

---

## P1 — Reliability and quality

- [ ] **Fix the SQLite test-migration failure** — Effort: **M**
  - Make the complete Laravel test suite run reliably in CI without failing on legacy index or column migrations.

- [ ] **Build end-to-end role and workflow tests** — Effort: **L**
  - Cover facility isolation, role access, hiring, uploads, approvals, imports, and assessments across successful and failure paths.

- [ ] **Add browser regression coverage** — Effort: **L**
  - Automate critical employee and leadership workflows for desktop and mobile viewport profiles.

- [ ] **Add background-job and delivery monitoring** — Effort: **L**
  - Show progress, retries, failures, and ownership for imports, emails, PDFs, scheduled reports, notifications, and backups.

- [ ] **Add archive and restore management** — Effort: **M**
  - Let authorized users review and restore soft-deleted document types and other configuration records.

- [ ] **Replace ad hoc seeder updates with governed snapshots** — Effort: **L**
  - Show differences, version metadata, validation results, backup status, and deployment guidance before overwriting source files.

---

## P2 — Operational intelligence

- [ ] **Create role-scoped compliance scorecards** — Effort: **L**
  - Show facility and department trends for documents, credentials, training, onboarding, and assessments.

- [ ] **Add aging and service-level indicators** — Effort: **M**
  - Display waiting time, due and overdue severity, escalation owner, and median approval duration.

- [ ] **Generate employee compliance packets** — Effort: **L**
  - Export an audit-ready packet containing current documents, approvals, training, credentials, and assessments.

- [ ] **Add calendar integration and proactive renewal reminders** — Effort: **L**
  - Send configurable reminders before credential, document, assessment, and training deadlines.

- [ ] **Support reviewer delegation and out-of-office coverage** — Effort: **M**
  - Temporarily delegate approval tasks with defined facilities, dates, permissions, and audit history.

- [ ] **Add a requirement-impact simulator** — Effort: **L**
  - Preview affected employees and newly created compliance gaps before saving position requirements.

- [ ] **Provide true import dry-run and reconciliation** — Effort: **L**
  - Preview inserts, updates, conflicts, validation failures, and expected totals before committing an import.

- [ ] **Create a data-quality work queue** — Effort: **L**
  - Surface missing assignments, duplicate employees, invalid emails, unmapped positions, and incomplete hire dates as actionable issues.

---

## P2 — Content and communications

- [ ] **Make News & Events a true combined experience** — Effort: **M**
  - Include event dates, RSVP or calendar links, and facility or company visibility alongside news.

- [ ] **Add communication delivery history** — Effort: **L**
  - Trace templates, recipients, sends, bounces, retries, and related employee or applicant records.

- [ ] **Add content draft, review, and publishing workflows** — Effort: **L**
  - Support preview, approval, scheduling, version history, and rollback for news, blogs, galleries, services, and facility pages.

- [ ] **Build a searchable support knowledge base** — Effort: **M**
  - Make manual sections, FAQs, known issues, and guided troubleshooting searchable from the Support menu.

---

## P3 — Platform

- [ ] **Add an administrator permission inspector** — Effort: **L**
  - Explain why a user can or cannot access a page, facility, employee, file, or action.

- [ ] **Implement configurable retention policies** — Effort: **L**
  - Apply explicit retention and legal-hold rules to applicant, employee, document, audit, communication, and security records.

- [ ] **Create a versioned integration API** — Effort: **L**
  - Support approved HRIS, identity, learning, and reporting integrations with scoped tokens, idempotency, and audit logs.

- [ ] **Set performance budgets and query monitoring** — Effort: **M**
  - Define and monitor response-time and query-count targets for employee, document, report, and dashboard pages.

---

## Definition of ready

Before development begins, each roadmap item should have:

- A named owner and stakeholder.
- Affected roles and facilities.
- User stories and acceptance criteria.
- Authorization and audit requirements.
- Data migration and backward-compatibility requirements.
- Automated unit, feature, and browser test coverage.
- Accessibility and mobile acceptance criteria.
- Deployment, monitoring, and rollback plans.

## Definition of done

An item should only be marked complete when:

- Acceptance criteria have been verified.
- Authorization and facility isolation have been tested.
- Automated tests pass in CI.
- Audit logging and monitoring are operational where applicable.
- User documentation has been updated.
- The change has been validated with representative employees and facility leaders.
