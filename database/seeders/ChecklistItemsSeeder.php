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
        $items = [
            // PART A - APPLICANT INFORMATION
            ['name' => 'Application Form', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Applicant Disclosure', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Reference Check #1', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Reference Check #2', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Offer Letter (if applicable)', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Job Data: Hire / Rehire', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Emergency Contact Information', 'section' => 'PART A', 'doc_type_id' => 1],
            ['name' => 'Job Description', 'section' => 'PART A', 'doc_type_id' => 1],
            // PART A - IDENTIFICATIONS
            ['name' => 'I - 9', 'section' => 'PART A', 'doc_type_id' => 2],
            ['name' => 'Social Security Card - Copy', 'section' => 'PART A', 'doc_type_id' => 2],
            ["name" => "Driver's License - Copy", 'section' => 'PART A', 'doc_type_id' => 2],
            ['name' => 'Green Card or Work Permit Autho. - Copy', 'section' => 'PART A', 'doc_type_id' => 2],
            ['name' => 'Passport - Copy', 'section' => 'PART A', 'doc_type_id' => 2],
            ['name' => 'Professional License - Copy', 'section' => 'PART A', 'doc_type_id' => 2],
            // PART A - VERIFICATIONS
            ['name' => 'CPR Card (License Nurses)', 'section' => 'PART A', 'doc_type_id' => 3],
            ['name' => 'C.N.A. Certificate', 'section' => 'PART A', 'doc_type_id' => 3],
            ['name' => 'Professional License', 'section' => 'PART A', 'doc_type_id' => 3],
            ['name' => 'Background Check', 'section' => 'PART A', 'doc_type_id' => 3],
            ['name' => 'OIG Verification', 'section' => 'PART A', 'doc_type_id' => 3],
            ['name' => 'SAM Verification', 'section' => 'PART A', 'doc_type_id' => 3],
            ['name' => 'Medical Exclusion/Ineligible Provider List', 'section' => 'PART A', 'doc_type_id' => 3],
            // PART B
            ['name' => 'Abuse, Neglect and Exploitation', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Resident Rights', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Employee Handbook', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Code of Conduct', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Spoken Language', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Missed Punch Policy', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Rest and Meal Break Policy: Hydration Program', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Use of ID Badge Policy: Second meal period waiver', 'section' => 'PART B', 'doc_type_id' => 4],
            ['name' => 'Six-hour meal period waiver', 'section' => 'PART B', 'doc_type_id' => 4],
            // PART C
            ['name' => 'Facility Organizational Chart', 'section' => 'PART C', 'doc_type_id' => 4],
            ["name" => "Facility Department Heads' Information", 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Facility Floor Plan', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Facility Tour and General Orientation', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Mariner Health Savings Plan / 401K', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'W-4', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'EDD', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Direct Deposit Authorization (Voided Check)', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Orientation Time Sheet', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Medical Insurance Premium Acknowledged', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'WC - Workplace and Ergonomics Safety', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Illness and Injury Prevention Program', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Individual Safety Responsibility', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Hazard Communication Training', 'section' => 'PART C', 'doc_type_id' => 4],
            ["name" => "Notice to Employee (Labor Code Sec 2810.5)", 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Environmental Care Questionnaire', 'section' => 'PART C', 'doc_type_id' => 4],
            ['name' => 'Required State Notice Acknowledgement Form', 'section' => 'PART C', 'doc_type_id' => 4],
            // PART D
            ['name' => 'Accuracy, Notice of', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Affirmative, Notice of', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Blood Borne Pathogen', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Confidentiality', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Deficit Reduction Act', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'First Aid for Choking', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Gait Belt Utilization', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'HIPAA & Compliance', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Infection Control', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'No Solicitation, Distribution and Access', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Private Duty', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Pulmonary Tuberculosis', 'section' => 'PART D', 'doc_type_id' => 4],
            ['name' => 'Substance Abuse and Testing', 'section' => 'PART D', 'doc_type_id' => 4],

        ];

        $order = 1;
        foreach ($items as $item) {
            DB::table('checklist_items')->insert([
                'name' => $item['name'],
                'section' => $item['section'],
                'doc_type_id' => $item['doc_type_id'],
                'position_ids' => isset($item['position_ids']) ? json_encode($item['position_ids']) : null,
                'order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $order++;
        }
    }
}
