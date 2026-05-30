# HR Portal & Employee Assessment Business Rules

This document captures business rules discussed and implemented for the Bio Pacific admin HR portal, employee records, and performance/competency assessment periods (Part F / Part G).

**See also:** [HR Portal Workflows](HR_PORTAL_WORKFLOWS.md) (full workflow catalog) · [Developer Guide](DEVELOPER_GUIDE.md) (section 9.1) · [Features](FEATURES.md) (HR Portal Business Rules section)

**Related implementation**

| Area | Primary code |
|------|----------------|
| Assessment period dates | `app/Support/EmployeeAssessmentPeriodCalculator.php` |
| Period sync & lookup | `app/Services/EmployeeAssessmentPeriodService.php` |
| Part F / Part G workflow status | `app/Support/AssessmentWorkflowStatus.php` |
| Self-assessment guard | `app/Support/PreventsSelfAssessment.php`, `app/Livewire/Concerns/GuardsAgainstSelfAssessment.php` |
| Part F save & workflow actions | `EmployeesController@saveAreasDevelopment` |
| Part G save & workflow actions | `EmployeesController@saveCompetencyWorkflow` |
| Part G competency API (legacy JSON sign) | `EmployeePerformanceAssessmentController` (`employee-sign`, `reviewer-sign`, `submit`) |
| Admin dashboard by role | `app/Support/AdminDashboardPresenter.php` |
| Employee personal tab | `resources/views/admin/facilities/employee/_employee-profile-form.blade.php` |
| Period UI | `resources/views/admin/facilities/checklist/employee-assessment-period-*.blade.php` |
| Part F summary & signatures UI | `resources/views/admin/facilities/checklist/employee-assessment-summary-form.blade.php` |
| Part G acknowledgement UI | `resources/views/admin/facilities/checklist/part-g-competency-workflow-form.blade.php` |

---

## 1. Admin dashboard (role-based)

Route: `/admin/dashboard` (`admin.dashboard.index`)

Each role sees stats, quick actions, and facility context appropriate to their access—not the full system admin surface.

| Role | Dashboard focus |
|------|-----------------|
| **admin / super-admin** | All facilities, users, system settings, imports, security, HIPAA, BAA, reports |
| **rdhr** | Multi-facility HR: facilities list, job openings/applications, scheduled reports, web content, communications |
| **facility-admin / facility-dsd** | Single assigned facility: facility hub, employees, hiring, documents, reports, scoped web/communications |

**Notes**

- `super-admin` is treated as a system administrator (same dashboard group as `admin`).
- Facility managers without `facility_id` see a “no facility assigned” message.
- Quick-action links are only included when the route exists and matches middleware for that role.

---

## 2. Employee hire dates (`bp_employees`)

### 2.1 Fields

| Column | Label (UI) | Purpose |
|--------|------------|---------|
| `original_hire_dt` | Original Hire Date | First/original hire anniversary anchor |
| `rehire_dt` | Rehire Date | Rehire anniversary anchor (only when applicable) |
| `action_id` | Action | Select option (type **Action**): New Hire, Rehire, Current, etc. |

### 2.2 Rehire Date visibility (Personal tab)

- **Rehire Date** is shown only when **Action** = **Rehire**.
- Changing Action away from Rehire hides the field and clears the input on save.
- If Action is not Rehire, `rehire_dt` is stored as `NULL` (not used for calculations).

### 2.3 Assessment anchor date

The anniversary used to build annual assessment windows:

1. If **Action** is **Rehire** and `rehire_dt` is set → anchor = `rehire_dt`.
2. Otherwise → anchor = `original_hire_dt`.
3. If neither is set → no automatic periods can be calculated.

Implemented on: `BPEmployee::usesRehireDate()` and `EmployeeAssessmentPeriodCalculator::resolveAnchorDate()`.

### 2.4 Seeding

`BPEmpEmployeesTableSeeder` sets `original_hire_dt` for all seeded employees and backfills any existing rows missing it. Rehire dates are not seeded by default.

---

## 3. Employee assessment periods

Table: `employee_assessment_periods`  
Model: `App\Models\EmployeeAssessmentPeriod`

### 3.1 Employee-specific periods

- Each period belongs to one employee via **`employee_num`** (FK to `bp_employees.employee_num`).
- Performance assessments, competency assessments, item entries, and section comments reference `assessment_period_id` for that employee’s period.
- Users may not use another employee’s period ID (validated on save).

### 3.2 Annual period shape (anniversary-based)

Periods are **annual** and aligned to the employee’s **anchor date** (month/day of original hire or rehire).

**Example:** Anchor = May 18  

| Period start year | `date_from` | `date_to` |
|-------------------|-------------|-----------|
| 2024 | 2024-05-18 | 2025-05-17 |
| 2025 | 2025-05-18 | 2026-05-17 |

Rule: `date_from` = anchor in year N; `date_to` = day before anchor in year N+1 (inclusive 12-month window).

### 3.3 Review type

- **`review_type`**: `A` = Annual (default, auto-calculated windows), `Q` = Quarterly (manual dates when created).
- Auto-sync and suggested periods use **Annual** (`A`) unless staff enter custom dates for quarterly reviews.

### 3.4 Default period for an assessment (prior year rule)

When performing or opening an assessment on date **D** (defaults to today):

> **The assessment period is always the completed annual cycle immediately before the cycle that contains D** — i.e. the **prior year** of the assessment date, relative to the hire/rehire anniversary.

**Example:** Anchor May 18, assessment on May 20, **2026**

| Cycle | Range | Role |
|-------|-------|------|
| In-progress (contains May 20, 2026) | 2025-05-18 → 2026-05-17 | Current employment year (not used as default) |
| **Default assessment period** | **2024-05-18 → 2025-05-17** | Prior year of assessment date |

**First employment year:** If the employee has not yet completed a full prior cycle (e.g. hired May 18, 2025 and reviewed May 20, 2025), there is **no** auto-suggested prior period until a completed earlier cycle exists.

**Implementation:** `EmployeeAssessmentPeriodCalculator::annualPeriodForAssessmentOn()`

### 3.5 Period sync

On employee edit/create (when hire/rehire/action changes) and when loading periods for Part F/G:

- System ensures annual period rows exist from **anchor year** through the **prior-year assessment window** (not future or in-progress cycles only).
- Implemented in `EmployeeAssessmentPeriodService::syncAnnualPeriodsForEmployee()`.

### 3.6 UI behavior (Part F / Part G)

- **Assessment Period** dropdown lists periods for **this employee only**.
- If no `assessment_period_id` is in the URL, the UI **defaults** to the prior-year period when it exists.
- **New Period** modal pre-fills dates from `annualPeriodForAssessmentOn()` for annual reviews.
- Creating a period requires `employee_num`; overlapping ranges for the same employee can warn and allow force save.
- Part F and Part G show a **status badge** on the checklist tab (e.g. *In Progress*, *For Employee confirmation*, *For Reviewer approval*, *Completed*).
- Assessment period history rows display the same status labels via `AssessmentWorkflowStatus::label()`.

### 3.7 Facility access (related)

- **facility-admin / facility-dsd** may only open facility dashboards and employees for their assigned `facility_id`.
- **admin / super-admin / rdhr** may access any facility (subject to route middleware).

---

## 4. Part F & Part G assessment workflow

Part F (performance appraisal) and Part G (competency assessment) share the same **four-stage workflow**. Status values are defined in `App\Support\AssessmentWorkflowStatus` and stored on:

| Part | Table | Status column | Completed flag |
|------|-------|---------------|----------------|
| **Part F** | `employee_performance_assessments` | `status` | `finalized` (synced when status = `completed`) |
| **Part G** | `employee_competency_assessments` | `status` | `completed_at` set when status = `completed` |

Legacy Part G values `for_employee_signature` and `for_reviewer_signature` are normalized to the current slugs at read time.

### 4.1 Status values and UI labels

| Slug (`status`) | UI label | Meaning |
|-----------------|----------|---------|
| `draft` | **In Progress** | Reviewer is rating items and entering narrative fields. |
| `for_employee_confirmation` | **For Employee confirmation** | Reviewer submitted; employee must acknowledge. |
| `for_reviewer_approval` | **For Reviewer approval** | Employee saved acknowledgement; reviewer must approve. |
| `completed` | **Completed** | Approved; assessment is read-only unless reopened or sent back. |

### 4.2 Allowed transitions

```
In Progress (draft)
    │  Reviewer: Submit for Employee Confirmation
    ▼
For Employee confirmation
    │  Employee: Save Acknowledgement
    ▼
For Reviewer approval
    │  Reviewer: Approve Assessment
    ▼
Completed
    │  Reviewer: Reopen for Editing  →  In Progress
    │  Employee: Send Back to Reviewer  →  For Reviewer approval (if was Completed)
    │                                    →  In Progress (if was For Employee confirmation)
```

**Send back (employee only)**

- From **For Employee confirmation** → **In Progress** (reviewer revises ratings/narrative).
- From **Completed** → **For Reviewer approval** (reviewer must re-approve after changes).

**Reopen (reviewer only)**

- From **Completed** → **In Progress**; `finalized` / completion timestamp cleared as applicable.

### 4.3 Who can edit what (by status)

| Status | Reviewer (not self) | Employee (self) |
|--------|---------------------|-----------------|
| In Progress | Rate items, development notes, submit for confirmation | Cannot rate or submit (see §5) |
| For Employee confirmation | Read-only on ratings; cannot submit | **Employee Comments**, acknowledgement date (auto-filled to today), **Save Acknowledgement** or **Send Back** |
| For Reviewer approval | May edit ratings/notes; **Approve Assessment** | Read-only |
| Completed | Read-only; **Reopen for Editing** | Read-only; **Send Back to Reviewer** |

When status is **Completed**, all checklist fields are read-only until a reviewer reopens or the employee sends the record back.

### 4.4 Part F — actions and endpoints

Form: **Performance Evaluation Summary** on Part F (`areasDevelopmentForm`).

| Button / action | Actor | `action` value | Result |
|-----------------|-------|----------------|--------|
| Save as Draft | Reviewer | `save` | Persists narrative; status stays **In Progress**. |
| Submit for Employee Confirmation | Reviewer | `submit` | Validates all scorable items rated + required development text; status → **For Employee confirmation**. |
| Save Acknowledgement | Employee | `acknowledge` | Saves employee comments; sets `acknowledge_dt` (today if blank); status → **For Reviewer approval**. |
| Approve Assessment | Reviewer | `approve` | status → **Completed**; `finalized = 1`. |
| Send Back to Reviewer | Employee | `send_back` | See §4.2. |
| Reopen for Editing | Reviewer | `reopen` | status → **In Progress**; `finalized = 0`. |

**Route:** `POST admin/employees/{employee}/areas-development` → `EmployeesController@saveAreasDevelopment`

Item-level ratings persist via Livewire (`PerformanceAppraisalAreas`) and item entry APIs; revoking a single item rating remains available with audit trail.

### 4.5 Part G — actions and endpoints

Panel: **Competency Assessment Acknowledgement** at the bottom of Part G.

| Button / action | Actor | `action` value | Result |
|-----------------|-------|----------------|--------|
| Submit for Employee Confirmation | Reviewer | `submit` | Creates assessment row if needed; status → **For Employee confirmation**. |
| Save Acknowledgement | Employee | `acknowledge` | Saves `employee_comments`, `employee_signed_at`; status → **For Reviewer approval**. |
| Approve Assessment | Reviewer | `approve` | status → **Completed**; `completed_at` set. |
| Send Back to Reviewer | Employee | `send_back` | See §4.2. |
| Reopen for Editing | Reviewer | `reopen` | status → **In Progress**. |

**Route:** `POST admin/employees/{employee}/competency-workflow` → `EmployeesController@saveCompetencyWorkflow`

Section-level competency ratings still save via Livewire Part G components (`saveDraft`, section submit). JSON endpoints (`competency-assessment/employee-sign`, `reviewer-sign`, `submit`) remain for API clients and use the same status slugs.

### 4.6 Read-only and locking rules

- `AssessmentWorkflowStatus::isLocked()` is true only for **Completed**.
- Part F Livewire passes `assessmentLocked` when ratings must not change (completed, awaiting employee confirmation, or self-view outside confirmation step).
- Part G Livewire sections use the same pattern via `$partGRatingsLocked` on the checklist blade.
- Completed assessments block narrative saves and new ratings unless reopened or sent back per §4.2.

---

## 5. Self-assessment (reviewer on own record)

When the logged-in user is the **same person** as the employee on the checklist (`PreventsSelfAssessment::isSelfAssessment()`):

- The user **cannot** rate performance items (Part F), review competency items (Part G), submit for confirmation, or approve as reviewer.
- Attempts to rate or review show: *"You cannot perform this assessment on yourself. Another supervisor or evaluator must complete it."*
- When status is **For Employee confirmation**, the employee **may** enter **Employee Comments**, confirm the acknowledgement date, and click **Save Acknowledgement** or **Send Back to Reviewer**.

Detection compares `User::resolvedBpEmployee()` to the target `employee_num`.

---

## 6. Summary decision tree

```
Assessment period for employee E on date D
│
├─ Anchor date?
│   ├─ Action = Rehire AND rehire_dt set → anchor = rehire_dt
│   └─ Else → anchor = original_hire_dt
│
├─ No anchor → cannot auto-generate periods
│
└─ Annual window for assessment on D
    ├─ Find cycle containing D (in-progress year)
    └─ Default assessment period = previous completed cycle
        (prior year of D on anniversary calendar)
        └─ If none exists yet (first year) → no auto-default

Part F / Part G assessment (same employee + period)
│
├─ Reviewer completes ratings & narrative (In Progress)
│   └─ Submit for Employee Confirmation
│
├─ Employee confirms (For Employee confirmation)
│   ├─ Save Acknowledgement → For Reviewer approval
│   └─ Send Back → In Progress
│
├─ Reviewer approves (For Reviewer approval)
│   └─ Approve Assessment → Completed
│
└─ Completed
    ├─ Reviewer: Reopen → In Progress
    └─ Employee: Send Back → For Reviewer approval
```

---

## 7. Change log (conversation scope)

| Date (context) | Rule |
|----------------|------|
| Admin dashboard | Role-specific links and stats per admin / super-admin / rdhr / facility-admin / facility-dsd |
| `original_hire_dt` | Required in seeder; used for hire anniversary |
| `employee_num` on periods | Periods are per-employee, not global |
| Hire/rehire anchor | Rehire date only when Action = Rehire; else original hire only |
| Prior year | Assessment defaults to the annual period before the cycle containing the assessment date |
| Part F / Part G workflow | Four statuses: In Progress → For Employee confirmation → For Reviewer approval → Completed; send-back and reopen paths |
| Self-assessment | Reviewers cannot rate/review themselves; employees may acknowledge when status is For Employee confirmation |
| Part F `status` column | Added to `employee_performance_assessments`; `finalized` synced on Completed |

---

*Update this file when business rules change. Prefer updating `EmployeeAssessmentPeriodCalculator`, `AssessmentWorkflowStatus`, and this document together.*
