<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeCompetencyItemsSeeder extends Seeder
{
    protected function getPositionIdsByTitles(array|string $titles): array
    {
        $titles = array_values(array_filter(array_map('trim', (array) $titles)));

        if ($titles === []) {
            return [];
        }

        return DB::table('positions')
            ->whereIn('title', $titles)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function run(): void
    {
        $directorOfStaffDevelopmentPositionIds = $this->getPositionIdsByTitles('Director of Staff Development');
        $positionIds = $directorOfStaffDevelopmentPositionIds !== [] ? $directorOfStaffDevelopmentPositionIds : ['global'];

        $competencyItems = [
            'IN-SERVICE EDUCATION PROGRAM MANAGEMENT' => [
                'Annual In-Service Plan (meets **California Title 22 & CMS requirements)',
                'Mandatory Nursing In-Services (Skills, Clinical Competency)',
                'Mandatory In-Services for ALL Staff (Abuse, Infection Control, Safety, HIPAA)',
                'Survey-Driven Education',
                'Competency Validation (Return Demonstrations)',
                'Education Tracking Logs (Attendance, Make-ups)',
                'CNA 12-hour Annual In-Service Compliance',
                'Nurse Continuing Education Tracking',
            ],
            'ORIENTATION PROGRAM MANAGEMENT' => [
                'New Hire Orientation (All Departments)',
                'General Orientation (Facility Policies, Emergency Preparedness)',
                'Clinical Orientation (Nursing-specific skills validation)',
                'CNA Orientation and Preceptor Program',
                'Agency/Registry Staff Orientation',
                'Orientation Checklist Completion Monitoring',
            ],
            'SKILLS COMPETENCY PROGRAM (ALL DEPARTMENTS)' => [
                'Licensed Nurses Annual Skills',
                'CNA Skills Competency',
                'DON Competency Validation',
                'Infection Preventionist Competency',
                'MDS Coordinator Competency',
                'Dietary Staff Competency',
                'Social Services Competency',
                'Activities Competency',
                'EVS/Maintenance/Laundry',
            ],
            'LICENSES, CERTIFICATIONS TRACKING' => [
                'RN/LVN (including Registry Staff)',
                'CNA (including Registry Staff)',
                'REHAB STAFF-Secure from Bio-Pacific',
                'CPR-RN/LVN and PT/OT/SLP (from Bio-Pacific)',
                'Subacute Ventilator Care Certification (RN/LVN)',
            ],
            'HUMAN RESOURCES COORDINATION' => [
                'Background Checks Verified',
                'Annual Performance Evaluations Completed',
                'Employee Files Audit (completeness)',
                'I-9 Compliance',
                'UKG/Time Clock Correction',
                'Leave of Absence Tracking',
                'Payroll Adjustment Coordination',
            ],
            'STAFFING & PPD MONITORING' => [
                'Daily PPD Monitoring',
                'Staffing Posting Compliance',
                'CNA Schedules (monthly)',
                'Daily Assignments Reviewed',
            ],
            'WORKERS COMP & SAFETY PROGRAM' => [
                'OSHA Log Maintenance',
                'Workmen’s Compensation Tracking',
                'Modified Duty Program Monitoring',
                'Safety Committee Lead',
            ],
            'COMMITTEE PARTICIPATION' => [
                'Infection Control Committee',
                'QAPI Committee',
                'Education Reports to QAPI',
                'PIP Support (Falls, Wounds, Psychotropics)',
            ],
            'CLINICAL OVERSIGHT & EDUCATION ROUNDS' => [
                'Rounds-Daily (Monthly with IP)',
                'Direct Care Observations/On-the-spot In-services',
            ],
            'INFECTION PREVENTION SUPPORT' => [
                'Isolation/Transmission-Based Precautions Education',
                'COVID-19 protocols and reporting requirements',
                'Isolation/Transmission-Based Precautions Education',
                'Outbreak Education Response',
            ],
            'EMPLOYEE HEALTH PROGRAM' => [
                'Initial TB Screening',
                'Annual TB Screening',
                'Annual TB Questionnaire',
                'CXR for Positive PPD + MD Clearance',
                'CXR every 5 years or as MD ordered',
                'Initial Physical Exam',
                'Annual Physical Exam',
                'COVID-19 Vaccination Program',
                'Annual Flu Vaccine',
                'Hepatitis B Vaccine',
                'Childhood Diseases Immunization',
            ],
            'POLICIES AND PROCEDURES MANAGEMENT' => [
                'Emergency/Disaster, Fire Safety- RED Binders',
                'Abuse/Neglect/Grievance Binder',
                'MCN access for ALL P&Ps',
                'Lippincott Nursing Procedures Book',
                'Pharmacy LTC Manual and IV Therapy Manual',
            ],
            'RESTORATIVE NURSING PROGRAM' => [
                'RNA Weekly Meeting and Notes',
                'Updating RNA Orders and Care Plans',
                'RNA Monthly Recap',
            ],
            'EHR / MATRIXCARE COMPETENCY' => [
                'Staff Training/ Order Entry / Clinical Workflow Training',
                'Documentation Audits',
                'Dashboard Monitoring (alerts/compliance)',
            ],
            'SURVEY READINESS' => [],
        ];

        $order = 0;
        foreach ($competencyItems as $section => $items) {
            foreach ($items as $item) {
                DB::table('employee_competency_items')->insert([
                    'section' => $section,
                    'item' => $item,
                    'position_ids' => json_encode($positionIds),
                    'order' => $order++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}