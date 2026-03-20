<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EmployeeChecklistItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        // Set isExpiring = 1 for items that typically expire (e.g., licenses, IDs, background checks, etc.)
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
            ['name' => 'I - 9', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Social Security Card - Copy', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => "Driver's License - Copy", 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Green Card or Work Permit Autho. - Copy', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Passport - Copy', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            ['name' => 'Professional License - Copy', 'section' => 'PART A', 'doc_type_id' => 2, 'isExpiring' => 1],
            // PART A - VERIFICATIONS
            ['name' => 'CPR Card (License Nurses)', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'C.N.A. Certificate', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'Professional License', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'Background Check', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'OIG Verification', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'SAM Verification', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
            ['name' => 'Medical Exclusion/Ineligible Provider List', 'section' => 'PART A', 'doc_type_id' => 3, 'isExpiring' => 1],
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
            // PART E - SKILLS (CNA)
            ['name' => 'Ambulation', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Back Rub', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bed Bath', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bed Making, Occupied', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bed Making, Unoccupied', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bed Pan, Urinal', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bladder Management/Toileting', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bladder Patterning/Retraining', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Body Mechanics- Gen. Rules', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Lifting and Moving', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Positioning', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Transferring', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Catheter Care', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Choking, Heimlich Maneuver', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Dementia Training', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Dialysis', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Dressing/undressing', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Emergency Procedures/Reporting', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Feeding, Special Issues', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Tray service', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Dining Program', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Feeding Tubes', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Gastric', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Jejunostomy', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Nasogastric', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Fluid Restrictions, Dot system', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Grooming', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Hand Washing', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Incontinence Care/Perineal Care', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Infection Control, waste', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Linen Handling', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Mechanical Lift', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Oral Hygiene', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Ostomy protocol review', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Oxygen, CPAP, BiPAP Tubing Care', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Tracheostomy-ADL care (CNA scope of practice)', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Pain Identification/Management', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Protective Devices', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Post-mortem care', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Range of motion', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Restraint Devices', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Scales, weighing', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Shaving', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Shower / Bathing', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Specimen Collection', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Splints/Orthosis', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Standard Precautions', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Use of Cane', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Walker', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Prosthetic devices', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bed controls', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Wheelchair', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Vital signs', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Temperature, axilla', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Temperature, ear', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Temperature, oral', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Temperature, rectal', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Pulse Rate', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Respiratory Rate', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Blood Pressure', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Documentation:', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => '-RFPR', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Bed mobility', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Transfers', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Eating', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Toileting', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => '-Meal Monitoring', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => '-Intake/Output, measurement', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => '-STOP and WATCH', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => '-Shower Skin Sheet', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => '-RNA Form (for RNAs)', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
            ['name' => 'Other:', 'section' => 'PART E', 'doc_type_id' => 5, 'isExpiring' => 0],
        ];

        // Truncate the table before seeding
        \App\Models\ChecklistItem::truncate();

        // Insert each item using the model to allow mass assignment
        foreach ($items as $item) {
            \App\Models\ChecklistItem::create($item);
        }
    }
}
