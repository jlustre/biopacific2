<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ChecklistItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove all existing items to prevent duplicates
        DB::table('checklist_items')->truncate();
        $now = Carbon::now();
        // isExpiring = 1 for licenses, IDs, certifications, and periodic verifications that expire
        $items = [
            // PART A - APPLICANT INFORMATION
            ['name' => 'Application Form', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Applicant Disclosure', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Reference Check #1', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Reference Check #2', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Offer Letter (if applicable)', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Job Data: Hire / Rehire', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Emergency Contact Information', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            ['name' => 'Job Description', 'section' => 'PART A', 'doc_type_id' => 1, 'isExpiring' => 0],
            // PART A - IDENTIFICATIONS
            ['name' => 'I - 9*', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Social Security Card - Copy', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 0],
            ['name' => "Driver's License - Copy*", 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Green Card or Work Permit Autho. - Copy*', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Passport - Copy*', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Professional License - Copy*', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            // PART A - VERIFICATIONS
            ['name' => 'CPR Card (License Nurses)*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'C.N.A. Certificate*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'Professional License*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'Background Check*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'OIG Verification*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'SAM Verification*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'Medical Exclusion/Ineligible Provider List*', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            // PART B
            ['name' => 'Abuse, Neglect and Exploitation', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Resident Rights', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Employee Handbook', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Code of Conduct', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Spoken Language', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Missed Punch Policy', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Rest and Meal Break Policy: Hydration Program', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Use of ID Badge Policy: Second meal period waiver', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Six-hour meal period waiver', 'section' => 'PART B', 'doc_type_id' => 4, 'isExpiring' => 0],
            // PART C
            ['name' => 'Facility Organizational Chart', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => "Facility Department Heads' Information", 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Facility Floor Plan', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Facility Tour and General Orientation', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Mariner Health Savings Plan / 401K', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'W-4', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'EDD', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Direct Deposit Authorization (Voided Check)', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Orientation Time Sheet', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Medical Insurance Premium Acknowledged', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'WC - Workplace and Ergonomics Safety', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Illness and Injury Prevention Program', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Individual Safety Responsibility', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Hazard Communication Training', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => "Notice to Employee (Labor Code Sec 2810.5)", 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Environmental Care Questionnaire', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Required State Notice Acknowledgement Form', 'section' => 'PART C', 'doc_type_id' => 4, 'isExpiring' => 0],
            // PART D
            ['name' => 'Accuracy, Notice of', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Affirmative, Notice of', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Blood Borne Pathogen', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Confidentiality', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Deficit Reduction Act', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'First Aid for Choking', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Gait Belt Utilization', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'HIPAA & Compliance', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Infection Control', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'No Solicitation, Distribution and Access', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Private Duty', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Pulmonary Tuberculosis', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Substance Abuse and Testing', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
            ['name' => 'Workplace Violence Prevention', 'section' => 'PART D', 'doc_type_id' => 4, 'isExpiring' => 0],
        ];

        $order = 1;
        foreach ($items as $item) {
            DB::table('checklist_items')->insert([
                'name' => $item['name'],
                'section' => $item['section'],
                'doc_type_id' => $item['doc_type_id'],
                'isExpiring' => $item['isExpiring'] ?? 0,
                'position_ids' => isset($item['position_ids']) ? json_encode($item['position_ids']) : null,
                'order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $order++;
        }
    }
}
