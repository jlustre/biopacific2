<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Database\Seeder;

/**
 * Auto-generated from Admin Reports -> Add/update seeder.
 * Last exported: 2026-07-23 15:11:16
 *
 * Do not edit report data by hand; use Admin Reports and re-export.
 */
class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reports = json_decode(<<<'REPORTS_JSON'
[
    {
        "category_name": "Performance Evaluation",
        "name": "Employees Due for Competency Assessment (30 Days Before Hire Anniversary)",
        "description": "Employees whose Part G competency assessment is due 30 days before their hiring anniversary. Filter by facility, department, position, and reports-to supervisor. Includes competency section count and assessment status. Use 0 / All for unrestricted filters. Excludes completed assessments.",
        "sql_template": "SELECT\r\n    COALESCE(f.name, 'Unassigned') AS facility_name,\r\n    COALESCE(d.name, 'Unassigned') AS department_name,\r\n    e.employee_num,\r\n    TRIM(CONCAT(\r\n        e.last_name, ', ', e.first_name,\r\n        IF(COALESCE(e.middle_name, '') = '', '', CONCAT(' ', e.middle_name))\r\n    )) AS employee_name,\r\n    p.title AS position_title,\r\n    COALESCE(rt.title, '') AS reports_to_title,\r\n    e.original_hire_dt AS hire_date,\r\n    anniversary_date,\r\n    DATE_SUB(anniversary_date, INTERVAL 30 DAY) AS competency_due_date,\r\n    DATEDIFF(DATE_SUB(anniversary_date, INTERVAL 30 DAY), CURDATE()) AS days_until_due,\r\n    (\r\n        SELECT COUNT(DISTINCT eci.section)\r\n        FROM employee_competency_items eci\r\n        WHERE eci.position_ids IS NULL\r\n           OR JSON_CONTAINS(eci.position_ids, JSON_QUOTE('global'))\r\n           OR JSON_CONTAINS(eci.position_ids, CAST(a.position_id AS JSON))\r\n    ) AS competencies_count,\r\n    COALESCE(eca.status, 'not_started') AS competency_status\r\nFROM (\r\n    SELECT\r\n        e.employee_num,\r\n        e.first_name,\r\n        e.last_name,\r\n        e.middle_name,\r\n        e.original_hire_dt,\r\n        CASE\r\n            WHEN DATE_FORMAT(e.original_hire_dt, '%m-%d') = '02-29'\r\n                AND DAY(LAST_DAY(CONCAT(YEAR(CURDATE()), '-02-01'))) = 28\r\n            THEN\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-02-28'), '%Y-%m-%d')\r\n                END\r\n            ELSE\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                END\r\n        END AS anniversary_date\r\n    FROM bp_employees e\r\n    WHERE e.deleted_at IS NULL\r\n      AND e.original_hire_dt IS NOT NULL\r\n) e\r\nINNER JOIN bp_emp_job_data a\r\n    ON a.employee_num = e.employee_num\r\n   AND NOT EXISTS (\r\n        SELECT 1\r\n        FROM bp_emp_job_data newer\r\n        WHERE newer.employee_num = a.employee_num\r\n          AND (\r\n              newer.effdt > a.effdt\r\n              OR (newer.effdt = a.effdt AND newer.effseq > a.effseq)\r\n          )\r\n   )\r\nLEFT JOIN facilities f ON f.id = a.facility_id\r\nLEFT JOIN departments d ON d.id = a.dept_id\r\nLEFT JOIN positions p ON p.id = a.position_id\r\nLEFT JOIN positions rt ON rt.id = a.reports_to\r\nLEFT JOIN employee_assessment_periods ap\r\n    ON ap.employee_num = e.employee_num\r\n   AND ap.date_to = DATE_SUB(anniversary_date, INTERVAL 1 DAY)\r\nLEFT JOIN employee_competency_assessments eca\r\n    ON eca.employee_num = e.employee_num\r\n   AND eca.assessment_period_id = ap.id\r\nWHERE a.position_id IS NOT NULL\r\n  AND anniversary_date > e.original_hire_dt\r\n  AND (0 = :facility_id OR a.facility_id = :facility_id)\r\n  AND (0 = :department_id OR a.dept_id = :department_id)\r\n  AND (0 = :position_id OR a.position_id = :position_id)\r\n  AND (0 = :reports_to OR a.reports_to = :reports_to)\r\n  AND DATE_SUB(anniversary_date, INTERVAL 30 DAY) <= DATE_ADD(CURDATE(), INTERVAL :days_ahead DAY)\r\n  AND DATE_SUB(anniversary_date, INTERVAL 30 DAY) >= DATE_SUB(CURDATE(), INTERVAL :overdue_days DAY)\r\n  AND (eca.id IS NULL OR LOWER(COALESCE(eca.status, '')) <> 'completed')\r\nHAVING competencies_count > 0\r\nORDER BY competency_due_date ASC, facility_name ASC, employee_name ASC",
        "parameters": [
            {
                "name": "facility_id",
                "type": "facility",
                "label": "Facility"
            },
            {
                "name": "department_id",
                "type": "department",
                "label": "Department"
            },
            {
                "name": "position_id",
                "type": "position",
                "label": "Position"
            },
            {
                "name": "reports_to",
                "type": "reports_to",
                "label": "Reports To"
            },
            {
                "name": "days_ahead",
                "type": "integer",
                "label": "Days ahead (upcoming due window)",
                "default": 30
            },
            {
                "name": "overdue_days",
                "type": "integer",
                "label": "Overdue days (look-back window)",
                "default": 90
            }
        ],
        "is_active": true,
        "is_public": false,
        "visibility": "roles",
        "visible_roles": [
            "admin",
            "super-admin",
            "rdhr",
            "facility-admin",
            "facility-dsd",
            "don"
        ],
        "visible_facilities": []
    },
    {
        "category_name": "Performance Evaluation",
        "name": "Employees Due for Performance Appraisal (30 Days Before Hire Anniversary)",
        "description": "Employees whose Part F performance appraisal is due 30 days before their hiring anniversary. Filter by facility, department, position, and reports-to supervisor. Includes performance section count and appraisal status. Use 0 / All for unrestricted filters. Excludes completed appraisals.",
        "sql_template": "SELECT\r\n    COALESCE(f.name, 'Unassigned') AS facility_name,\r\n    COALESCE(d.name, 'Unassigned') AS department_name,\r\n    e.employee_num,\r\n    TRIM(CONCAT(\r\n        e.last_name, ', ', e.first_name,\r\n        IF(COALESCE(e.middle_name, '') = '', '', CONCAT(' ', e.middle_name))\r\n    )) AS employee_name,\r\n    p.title AS position_title,\r\n    COALESCE(rt.title, '') AS reports_to_title,\r\n    e.original_hire_dt AS hire_date,\r\n    anniversary_date,\r\n    DATE_SUB(anniversary_date, INTERVAL 30 DAY) AS performance_due_date,\r\n    DATEDIFF(DATE_SUB(anniversary_date, INTERVAL 30 DAY), CURDATE()) AS days_until_due,\r\n    (\r\n        SELECT COUNT(DISTINCT epi.section)\r\n        FROM employee_performance_items epi\r\n        WHERE epi.position_ids IS NULL\r\n           OR JSON_CONTAINS(epi.position_ids, JSON_QUOTE('global'))\r\n           OR JSON_CONTAINS(epi.position_ids, CAST(a.position_id AS JSON))\r\n    ) AS performance_sections_count,\r\n    COALESCE(epa.status, 'not_started') AS performance_status,\r\n    COALESCE(epa.overall_rating, '') AS overall_rating\r\nFROM (\r\n    SELECT\r\n        e.employee_num,\r\n        e.first_name,\r\n        e.last_name,\r\n        e.middle_name,\r\n        e.original_hire_dt,\r\n        CASE\r\n            WHEN DATE_FORMAT(e.original_hire_dt, '%m-%d') = '02-29'\r\n                AND DAY(LAST_DAY(CONCAT(YEAR(CURDATE()), '-02-01'))) = 28\r\n            THEN\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-02-28'), '%Y-%m-%d')\r\n                END\r\n            ELSE\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                END\r\n        END AS anniversary_date\r\n    FROM bp_employees e\r\n    WHERE e.deleted_at IS NULL\r\n      AND e.original_hire_dt IS NOT NULL\r\n) e\r\nINNER JOIN bp_emp_job_data a\r\n    ON a.employee_num = e.employee_num\r\n   AND NOT EXISTS (\r\n        SELECT 1\r\n        FROM bp_emp_job_data newer\r\n        WHERE newer.employee_num = a.employee_num\r\n          AND (\r\n              newer.effdt > a.effdt\r\n              OR (newer.effdt = a.effdt AND newer.effseq > a.effseq)\r\n          )\r\n   )\r\nLEFT JOIN facilities f ON f.id = a.facility_id\r\nLEFT JOIN departments d ON d.id = a.dept_id\r\nLEFT JOIN positions p ON p.id = a.position_id\r\nLEFT JOIN positions rt ON rt.id = a.reports_to\r\nLEFT JOIN employee_assessment_periods ap\r\n    ON ap.employee_num = e.employee_num\r\n   AND ap.date_to = DATE_SUB(anniversary_date, INTERVAL 1 DAY)\r\nLEFT JOIN employee_performance_assessments epa\r\n    ON epa.employee_num = e.employee_num\r\n   AND epa.assessment_period_id = ap.id\r\nWHERE a.position_id IS NOT NULL\r\n  AND anniversary_date > e.original_hire_dt\r\n  AND (0 = :facility_id OR a.facility_id = :facility_id)\r\n  AND (0 = :department_id OR a.dept_id = :department_id)\r\n  AND (0 = :position_id OR a.position_id = :position_id)\r\n  AND (0 = :reports_to OR a.reports_to = :reports_to)\r\n  AND DATE_SUB(anniversary_date, INTERVAL 30 DAY) <= DATE_ADD(CURDATE(), INTERVAL :days_ahead DAY)\r\n  AND DATE_SUB(anniversary_date, INTERVAL 30 DAY) >= DATE_SUB(CURDATE(), INTERVAL :overdue_days DAY)\r\n  AND (\r\n        epa.id IS NULL\r\n        OR (\r\n            LOWER(COALESCE(epa.status, '')) <> 'completed'\r\n            AND COALESCE(epa.finalized, 0) = 0\r\n        )\r\n      )\r\nHAVING performance_sections_count > 0\r\nORDER BY performance_due_date ASC, facility_name ASC, employee_name ASC",
        "parameters": [
            {
                "name": "facility_id",
                "type": "facility",
                "label": "Facility"
            },
            {
                "name": "department_id",
                "type": "department",
                "label": "Department"
            },
            {
                "name": "position_id",
                "type": "position",
                "label": "Position"
            },
            {
                "name": "reports_to",
                "type": "reports_to",
                "label": "Reports To"
            },
            {
                "name": "days_ahead",
                "type": "integer",
                "label": "Days ahead (upcoming due window)",
                "default": 30
            },
            {
                "name": "overdue_days",
                "type": "integer",
                "label": "Overdue days (look-back window)",
                "default": 90
            }
        ],
        "is_active": true,
        "is_public": false,
        "visibility": "roles",
        "visible_roles": [
            "admin",
            "super-admin",
            "rdhr",
            "facility-admin",
            "facility-dsd",
            "don"
        ],
        "visible_facilities": []
    },
    {
        "category_name": "Training & Inservice",
        "name": "Employees Due for Training (30 Days Before Hire Anniversary)",
        "description": "Employees whose Part H trainings are due 30 days before their hiring anniversary (recurring), or on hire date (hiring one-time). Filter by facility, department, position, and reports-to supervisor. Includes training name, frequency, last completion, and status. Use 0 / All for unrestricted filters. Excludes completed hiring trainings, period-completed recurring trainings, and items still satisfied through the anniversary.",
        "sql_template": "SELECT\r\n    COALESCE(f.name, 'Unassigned') AS facility_name,\r\n    COALESCE(d.name, 'Unassigned') AS department_name,\r\n    e.employee_num,\r\n    TRIM(CONCAT(\r\n        e.last_name, ', ', e.first_name,\r\n        IF(COALESCE(e.middle_name, '') = '', '', CONCAT(' ', e.middle_name))\r\n    )) AS employee_name,\r\n    p.title AS position_title,\r\n    COALESCE(rt.title, '') AS reports_to_title,\r\n    e.original_hire_dt AS hire_date,\r\n    anniversary_date,\r\n    ti.name AS training_name,\r\n    CASE ti.frequency\r\n        WHEN 'hiring' THEN 'Hiring (one-time)'\r\n        WHEN 'annual' THEN 'Annual'\r\n        WHEN 'biennial' THEN 'Every 2 years'\r\n        WHEN 'triennial' THEN 'Every 3 years'\r\n        ELSE ti.frequency\r\n    END AS frequency,\r\n    DATE(lc.completed_at) AS last_completed_at,\r\n    CASE\r\n        WHEN ti.frequency = 'hiring' THEN COALESCE(DATE(e.original_hire_dt), CURDATE())\r\n        ELSE DATE_SUB(anniversary_date, INTERVAL 30 DAY)\r\n    END AS training_due_date,\r\n    DATEDIFF(\r\n        CASE\r\n            WHEN ti.frequency = 'hiring' THEN COALESCE(DATE(e.original_hire_dt), CURDATE())\r\n            ELSE DATE_SUB(anniversary_date, INTERVAL 30 DAY)\r\n        END,\r\n        CURDATE()\r\n    ) AS days_until_due,\r\n    COALESCE(latest_any.status, 'not_started') AS training_status\r\nFROM (\r\n    SELECT\r\n        e.employee_num,\r\n        e.first_name,\r\n        e.last_name,\r\n        e.middle_name,\r\n        e.original_hire_dt,\r\n        CASE\r\n            WHEN DATE_FORMAT(e.original_hire_dt, '%m-%d') = '02-29'\r\n                AND DAY(LAST_DAY(CONCAT(YEAR(CURDATE()), '-02-01'))) = 28\r\n            THEN\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-02-28'), '%Y-%m-%d')\r\n                END\r\n            ELSE\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                END\r\n        END AS anniversary_date\r\n    FROM bp_employees e\r\n    WHERE e.deleted_at IS NULL\r\n      AND e.original_hire_dt IS NOT NULL\r\n) e\r\nINNER JOIN bp_emp_job_data a\r\n    ON a.employee_num = e.employee_num\r\n   AND NOT EXISTS (\r\n        SELECT 1\r\n        FROM bp_emp_job_data newer\r\n        WHERE newer.employee_num = a.employee_num\r\n          AND (\r\n              newer.effdt > a.effdt\r\n              OR (newer.effdt = a.effdt AND newer.effseq > a.effseq)\r\n          )\r\n   )\r\nINNER JOIN employee_training_items ti\r\n    ON ti.is_active = 1\r\n   AND (\r\n        ti.position_ids IS NULL\r\n        OR JSON_CONTAINS(ti.position_ids, JSON_QUOTE('global'))\r\n        OR JSON_CONTAINS(ti.position_ids, CAST(a.position_id AS JSON))\r\n        OR JSON_CONTAINS(ti.position_ids, JSON_QUOTE(CAST(a.position_id AS CHAR)))\r\n   )\r\nLEFT JOIN facilities f ON f.id = a.facility_id\r\nLEFT JOIN departments d ON d.id = a.dept_id\r\nLEFT JOIN positions p ON p.id = a.position_id\r\nLEFT JOIN positions rt ON rt.id = a.reports_to\r\nLEFT JOIN employee_assessment_periods ap\r\n    ON ap.employee_num = e.employee_num\r\n   AND ap.date_to = DATE_SUB(anniversary_date, INTERVAL 1 DAY)\r\nLEFT JOIN employee_training_completions lc\r\n    ON lc.id = (\r\n        SELECT etc.id\r\n        FROM employee_training_completions etc\r\n        WHERE etc.employee_num = e.employee_num\r\n          AND etc.employee_training_item_id = ti.id\r\n          AND etc.status = 'completed'\r\n          AND etc.completed_at IS NOT NULL\r\n        ORDER BY etc.completed_at DESC, etc.id DESC\r\n        LIMIT 1\r\n    )\r\nLEFT JOIN employee_training_completions period_completion\r\n    ON period_completion.id = (\r\n        SELECT etc.id\r\n        FROM employee_training_completions etc\r\n        WHERE etc.employee_num = e.employee_num\r\n          AND etc.employee_training_item_id = ti.id\r\n          AND etc.status = 'completed'\r\n          AND etc.assessment_period_id = ap.id\r\n        ORDER BY etc.completed_at DESC, etc.id DESC\r\n        LIMIT 1\r\n    )\r\nLEFT JOIN employee_training_completions latest_any\r\n    ON latest_any.id = (\r\n        SELECT etc2.id\r\n        FROM employee_training_completions etc2\r\n        WHERE etc2.employee_num = e.employee_num\r\n          AND etc2.employee_training_item_id = ti.id\r\n        ORDER BY etc2.updated_at DESC, etc2.id DESC\r\n        LIMIT 1\r\n    )\r\nWHERE a.position_id IS NOT NULL\r\n  AND (\r\n        ti.frequency = 'hiring'\r\n        OR anniversary_date > e.original_hire_dt\r\n      )\r\n  AND (0 = :facility_id OR a.facility_id = :facility_id)\r\n  AND (0 = :department_id OR a.dept_id = :department_id)\r\n  AND (0 = :position_id OR a.position_id = :position_id)\r\n  AND (0 = :reports_to OR a.reports_to = :reports_to)\r\n  AND (\r\n        CASE\r\n            WHEN ti.frequency = 'hiring' THEN COALESCE(DATE(e.original_hire_dt), CURDATE())\r\n            ELSE DATE_SUB(anniversary_date, INTERVAL 30 DAY)\r\n        END\r\n      ) <= DATE_ADD(CURDATE(), INTERVAL :days_ahead DAY)\r\n  AND (\r\n        CASE\r\n            WHEN ti.frequency = 'hiring' THEN COALESCE(DATE(e.original_hire_dt), CURDATE())\r\n            ELSE DATE_SUB(anniversary_date, INTERVAL 30 DAY)\r\n        END\r\n      ) >= DATE_SUB(CURDATE(), INTERVAL :overdue_days DAY)\r\n  AND NOT (ti.frequency = 'hiring' AND lc.id IS NOT NULL)\r\n  AND NOT (ti.frequency <> 'hiring' AND period_completion.id IS NOT NULL)\r\n  AND NOT (\r\n        ti.frequency <> 'hiring'\r\n        AND lc.completed_at IS NOT NULL\r\n        AND DATE_ADD(\r\n            DATE(lc.completed_at),\r\n            INTERVAL CASE ti.frequency\r\n                WHEN 'biennial' THEN 2\r\n                WHEN 'triennial' THEN 3\r\n                ELSE 1\r\n            END YEAR\r\n        ) > anniversary_date\r\n      )\r\nORDER BY training_due_date ASC, facility_name ASC, employee_name ASC, training_name ASC",
        "parameters": [
            {
                "name": "facility_id",
                "type": "facility",
                "label": "Facility"
            },
            {
                "name": "department_id",
                "type": "department",
                "label": "Department"
            },
            {
                "name": "position_id",
                "type": "position",
                "label": "Position"
            },
            {
                "name": "reports_to",
                "type": "reports_to",
                "label": "Reports To"
            },
            {
                "name": "days_ahead",
                "type": "integer",
                "label": "Days ahead (upcoming due window)",
                "default": 30
            },
            {
                "name": "overdue_days",
                "type": "integer",
                "label": "Overdue days (look-back window)",
                "default": 90
            }
        ],
        "is_active": true,
        "is_public": false,
        "visibility": "roles",
        "visible_roles": [
            "admin",
            "super-admin",
            "rdhr",
            "facility-admin",
            "facility-dsd",
            "don"
        ],
        "visible_facilities": []
    },
    {
        "category_name": "Facility",
        "name": "Get All Facilities domain",
        "description": "Get facilities id, name and domain names.",
        "sql_template": "SELECT `id`,`name`,`domain` FROM `facilities` WHERE `is_active` = :is_active",
        "parameters": [
            {
                "name": "is_active",
                "type": "integer",
                "label": "Is Active"
            }
        ],
        "is_active": true,
        "is_public": false,
        "visibility": "roles",
        "visible_roles": [
            "admin",
            "facility-admin",
            "facility-editor"
        ],
        "visible_facilities": []
    },
    {
        "category_name": "Licensure & Certification",
        "name": "Get Expiring Licenses & Certifications (30 Days Before Hire Anniversary)",
        "description": "Employees whose required licenses/certifications are due for renewal 30 days before their hiring anniversary. Based on position document requirements (license/certification types). Shows anniversary, renewal due date, latest on-file expiration when available, and status. Use 0 / All for unrestricted filters.",
        "sql_template": "SELECT\r\n    COALESCE(f.name, 'Unassigned') AS facility_name,\r\n    COALESCE(d.name, 'Unassigned') AS department_name,\r\n    e.employee_num,\r\n    TRIM(CONCAT(\r\n        e.last_name, ', ', e.first_name,\r\n        IF(COALESCE(e.middle_name, '') = '', '', CONCAT(' ', e.middle_name))\r\n    )) AS employee_name,\r\n    p.title AS position_title,\r\n    e.original_hire_dt AS hire_date,\r\n    anniversary_date,\r\n    ut.name AS document_name,\r\n    latest.expiration_date,\r\n    DATE_SUB(anniversary_date, INTERVAL 30 DAY) AS renewal_due_date,\r\n    DATEDIFF(DATE_SUB(anniversary_date, INTERVAL 30 DAY), CURDATE()) AS days_until_due,\r\n    CASE\r\n        WHEN DATE_SUB(anniversary_date, INTERVAL 30 DAY) < CURDATE() THEN 'Overdue'\r\n        WHEN DATE_SUB(anniversary_date, INTERVAL 30 DAY) = CURDATE() THEN 'Due today'\r\n        WHEN DATE_SUB(anniversary_date, INTERVAL 30 DAY) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Due in 30 days'\r\n        ELSE 'Upcoming'\r\n    END AS due_status,\r\n    CASE\r\n        WHEN latest.expiration_date IS NULL THEN 'Not on file'\r\n        WHEN latest.expiration_date < CURDATE() THEN 'Expired'\r\n        WHEN latest.expiration_date < anniversary_date THEN 'Expires before anniversary'\r\n        ELSE COALESCE(latest.verification_status, 'On file')\r\n    END AS verification_status\r\nFROM (\r\n    SELECT\r\n        e.employee_num,\r\n        e.first_name,\r\n        e.last_name,\r\n        e.middle_name,\r\n        e.original_hire_dt,\r\n        CASE\r\n            WHEN DATE_FORMAT(e.original_hire_dt, '%m-%d') = '02-29'\r\n                AND DAY(LAST_DAY(CONCAT(YEAR(CURDATE()), '-02-01'))) = 28\r\n            THEN\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-02-28'), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-02-28'), '%Y-%m-%d')\r\n                END\r\n            ELSE\r\n                CASE\r\n                    WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d') >= CURDATE()\r\n                    THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                    ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(e.original_hire_dt, '%m-%d')), '%Y-%m-%d')\r\n                END\r\n        END AS anniversary_date\r\n    FROM bp_employees e\r\n    WHERE e.deleted_at IS NULL\r\n      AND e.original_hire_dt IS NOT NULL\r\n) e\r\nINNER JOIN bp_emp_job_data a\r\n    ON a.employee_num = e.employee_num\r\n   AND NOT EXISTS (\r\n        SELECT 1\r\n        FROM bp_emp_job_data newer\r\n        WHERE newer.employee_num = a.employee_num\r\n          AND (\r\n              newer.effdt > a.effdt\r\n              OR (newer.effdt = a.effdt AND newer.effseq > a.effseq)\r\n          )\r\n   )\r\nINNER JOIN upload_types ut\r\n    ON ut.is_license_or_certification = 1\r\n   AND (\r\n        ut.applies_to_all_positions = 1\r\n        OR EXISTS (\r\n            SELECT 1\r\n            FROM position_upload_type_requirements pur\r\n            WHERE pur.upload_type_id = ut.id\r\n              AND pur.position_id = a.position_id\r\n              AND pur.is_required = 1\r\n        )\r\n   )\r\nLEFT JOIN facilities f ON f.id = a.facility_id\r\nLEFT JOIN departments d ON d.id = a.dept_id\r\nLEFT JOIN positions p ON p.id = a.position_id\r\nLEFT JOIN (\r\n    SELECT\r\n        employee_num,\r\n        upload_type_id,\r\n        expiration_date,\r\n        verification_status\r\n    FROM (\r\n        SELECT\r\n            u.employee_num,\r\n            u.upload_type_id,\r\n            u.expires_at AS expiration_date,\r\n            COALESCE(u.verification_status, 'not submitted') AS verification_status,\r\n            ROW_NUMBER() OVER (\r\n                PARTITION BY u.employee_num, u.upload_type_id\r\n                ORDER BY u.expires_at DESC, u.id DESC\r\n            ) AS rn\r\n        FROM uploads u\r\n        WHERE u.upload_type_id IS NOT NULL\r\n          AND u.employee_num IS NOT NULL\r\n    ) ranked_uploads\r\n    WHERE rn = 1\r\n) latest\r\n    ON latest.employee_num = e.employee_num\r\n   AND latest.upload_type_id = ut.id\r\nWHERE a.position_id IS NOT NULL\r\n  AND anniversary_date > e.original_hire_dt\r\n  AND (0 = :facility_id OR a.facility_id = :facility_id)\r\n  AND (0 = :department_id OR a.dept_id = :department_id)\r\n  AND (0 = :position_id OR a.position_id = :position_id)\r\n  AND DATE_SUB(anniversary_date, INTERVAL 30 DAY) <= DATE_ADD(CURDATE(), INTERVAL :days_ahead DAY)\r\n  AND DATE_SUB(anniversary_date, INTERVAL 30 DAY) >= DATE_SUB(CURDATE(), INTERVAL :overdue_days DAY)\r\n  AND (\r\n        latest.expiration_date IS NULL\r\n        OR latest.expiration_date < anniversary_date\r\n        OR COALESCE(latest.verification_status, '') <> 'approved'\r\n      )\r\nORDER BY renewal_due_date ASC, facility_name ASC, employee_name ASC, document_name ASC",
        "parameters": [
            {
                "name": "facility_id",
                "type": "facility",
                "label": "Facility",
                "default": 0
            },
            {
                "name": "department_id",
                "type": "department",
                "label": "Department",
                "default": 0
            },
            {
                "name": "position_id",
                "type": "position",
                "label": "Position",
                "default": 0
            },
            {
                "name": "days_ahead",
                "type": "integer",
                "label": "Days ahead (upcoming due window)",
                "default": 30
            },
            {
                "name": "overdue_days",
                "type": "integer",
                "label": "Overdue days (look-back window)",
                "default": 90
            }
        ],
        "is_active": true,
        "is_public": false,
        "visibility": "roles",
        "visible_roles": [
            "admin",
            "super-admin",
            "rdhr",
            "facility-admin",
            "facility-dsd",
            "don"
        ],
        "visible_facilities": []
    },
    {
        "category_name": "Compliance",
        "name": "List of Documents That need Expiry Tracking",
        "description": "List of Documents That need to be tracked its expiration date.",
        "sql_template": "SELECT `id`,`name` FROM `upload_types` WHERE `requires_expiry` = :requires_expiry",
        "parameters": [
            {
                "name": "requires_expiry",
                "type": "boolean",
                "label": "Requires Expiry"
            }
        ],
        "is_active": true,
        "is_public": false,
        "visibility": "admin",
        "visible_roles": [],
        "visible_facilities": []
    }
]
REPORTS_JSON, true) ?? [];

        foreach ($reports as $report) {
            $categoryId = null;

            if (! empty($report['category_name'])) {
                $categoryId = ReportCategory::query()
                    ->where('name', $report['category_name'])
                    ->value('id');
            }

            Report::query()->updateOrCreate(
                ['name' => $report['name']],
                [
                    'category_id' => $categoryId,
                    'description' => $report['description'] ?? null,
                    'sql_template' => $report['sql_template'],
                    'parameters' => $report['parameters'] ?? [],
                    'is_active' => (bool) ($report['is_active'] ?? true),
                    'is_public' => (bool) ($report['is_public'] ?? false),
                    'visibility' => $report['visibility'] ?? 'admin',
                    'visible_roles' => $report['visible_roles'] ?? [],
                    'visible_facilities' => $report['visible_facilities'] ?? [],
                ]
            );
        }
    }
}
