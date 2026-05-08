<?php

namespace Database\Seeders;

use App\Models\EmployeeCompetencyItem;
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

    protected function seedCompetencySet(array $competencyItems, array $positionIds, int &$order): void
    {
        foreach ($competencyItems as $section => $items) {
            foreach ($items as $item) {
                EmployeeCompetencyItem::query()->updateOrCreate(
                    [
                        'section' => $section,
                        'item' => $item,
                        'order' => $order,
                    ],
                    [
                        'position_ids' => $positionIds,
                    ]
                );

                $order++;
            }
        }
    }

    public function run(): void
    {
        $directorOfStaffDevelopmentPositionIds = $this->getPositionIdsByTitles('Director of Staff Development');
        $dsdPositionIds = $directorOfStaffDevelopmentPositionIds !== [] ? $directorOfStaffDevelopmentPositionIds : ['global'];
        $cnaPositionIds = $this->getPositionIdsByTitles('Certified Nursing Assistant');
        $licensedNursePositionIds = $this->getPositionIdsByTitles([
            'Director of Nursing',
            'Registered Nurse',
            'Licensed Vocational Nurse',
            'Licensed Nurse',
            'Charge Nurse',
            'IP Nurse',
        ]);

        $dsdCompetencyItems = [
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

        $cnaCompetencyItems = [
            'CNA SKILLS CHECKLIST' => [
                'Ambulation',
                'Back Rub',
                'Bed Bath',
                'Bed Making, Occupied',
                'Bed Making, Unoccupied',
                'Bed Pan, Urinal',
                'Bladder Management/Toileting',
                'Bladder Patterning/Retraining',
                'Body Mechanics- Gen. Rules',
                '-Lifting and Moving',
                '-Positioning',
                '-Transferring',
                'Catheter Care',
                'Choking, Heimlich Maneuver',
                'Dementia Training',
                'Dialysis',
                'Dressing/undressing',
                'Emergency Procedures/Reporting',
                'Feeding, Special Issues',
                '-Tray service',
                '-Dining Program',
                'Feeding Tubes',
                '-Gastric',
                '-Jejunostomy',
                '-Nasogastric',
                'Fluid Restrictions, Dot system',
                'Grooming',
                'Hand Washing',
                'Incontinence Care/Perineal Care',
                'Infection Control, waste',
                'Linen Handling',
                'Mechanical Lift',
                'Oral Hygiene',
                'Ostomy protocol review',
                'Oxygen, CPAP, BiPAP Tubing Care',
                'Tracheostomy-ADL care (CNA scope of practice)',
                'Pain Identification/Management',
                'Protective Devices',
                'Post-mortem care',
                'Range of motion',
                'Restraint Devices',
                'Scales, weighing',
                'Shaving',
                'Shower / Bathing',
                'Specimen Collection',
                'Splints/Orthosis',
                'Standard Precautions',
                'Use of Cane',
                '-Walker',
                '-Prosthetic devices',
                '-Bed controls',
                '-Wheelchair',
                'Vital signs',
                '-Temperature, axilla',
                '-Temperature, ear',
                '-Temperature, oral',
                '-Temperature, rectal',
                '-Pulse Rate',
                '-Respiratory Rate',
                '-Blood Pressure',
                'Documentation:',
                '-RFPR',
                '--Bed mobility',
                '--Transfers',
                '--Eating',
                '--Toileting',
                '-Meal Monitoring',
                '-Intake/Output, measurement',
                '-STOP and WATCH',
                '-Shower Skin Sheet',
                '-RNA Form (for RNAs)',
                'Other:',
            ],
            'PERINEAL CARE' => [
                '-OBSERVATIONS',
                '--Equipment are properly set up: gloves, washcloths, clean basin, mild soap, bath towel, bath blanket, incontinent pad, toilet tissue, linen saver pad, trash bag.',
                '--Optional: bedpan, peri--bottle, antiseptic soap, petroleum jelly, or zinc oxide cream, or Vitamin A and D ointment, and an ABD pad.',
                '--Obtain ointment or cream, as needed.',
                '--Fill basin two--thirds full of warm water (or fill peri--bottle with warm water).',
                '--Assemble equipment and provide privacy.',
                '--Wash hands and put on gloves.',
                '--Explain the procedure to the resident.',
                '--Adjust bed to working height and lower head of bed.',
                '-FEMALE RESIDENT:',
                '--Position resident on side to expose perineal area. Place incontinent pad underneath.',
                '--Clean, rinse and dry perineal area, starting at posterior vaginal vault opening and wiping front to back.',
                '--Re--position resident to supine position. Use bath blanket to cover resident while exposing perineum.',
                '--Ask resident to bend knees slightly and spread her legs. Wet clean washcloth.',
                '--Separate labia with one hand and wash with the other, using gentle downward strokes from front to back of perineum.',
                '--Use clean section of washcloth for each stroke.',
                '--Rinse thoroughly from front to back and pat area dry with bath towel.',
                '--Apply ordered ointments or creams.',
                '-MALE RESIDENT:',
                '--Assist to supine position and place incontinent pad under buttocks.',
                '--Drape legs for privacy and expose genital area.',
                '--Hold shaft of penis with one hand and wash with the other, beginning at the tip and working in a circular motion from the center to the periphery.',
                '--Use clean section of washcloth for each stroke.',
                '--Rinse thoroughly, using same circular motion.',
                '--For the uncircumcised resident, gently retract the foreskin and clean beneath it.',
                '--Rinse well but do not dry because moisture provides lubrication and prevents friction when replacing foreskin. Pull foreskin back over glans.',
                '--Wash rest of penis, using downward strokes toward scrotum. Rinse well and pat dry.',
                '--Clean scrotum, rinse well and pat dry.',
                '--Turn resident on side, clean bottom of scrotum and anal area. Rinse and dry.',
                '-AFTER CARE:',
                '--Reposition resident, remove bath blanket and incontinent pad, and replace bed linens.',
                '--Clean equipment; store appropriately; discard gloves; and wash hands.',
            ],
        ];

        $licensedNurseCompetencyItems = [
            'BLOOD TRANSFUSION COMPETENCY' => [
                '-Consider cultural and religious influences and educational level when administering blood and performing teaching with patients receiving blood and blood products.',
                '-Utilize principles of body substance isolation, explain procedure to patient, and educate patient about transfusion reaction symptoms to report to nurse.',
                '-Type and Screen is required for any red blood cell, whole blood product, platelet, or plasma administration.',
                '-Draw and send this sample to blood bank in accordance with policy. (Cryoprecipitate does not require Type and Screen.)',
                '-Assess the patient for a history of transfusions and transfusion reaction.',
                'Pre-transfusion checks to be performed prior to blood pick up:',
                '-1. Verify there is an ORDER TO TRANFUSE the blood product',
                '-2. Make sure there is a CONSENT FOR TRANSFUSION',
                '-3. Check patency of IV LINE, >22 gauge required, >20 gauge preferred. Establishes IV at KO rate or per physician\'s order.',
                '--a. Prime Y tubing with normal saline solution.',
                '--b. Follow procedure for starting large bore IV, greater than 20 gauge for adults.',
                '-4. Check PRE-TRANSFUSION VITAL SIGNS for acceptability. If the patient has a fever or any other cause for delay of transfusion, resolve prior to blood pick up',
                '-5. Check that the patient is wearing a BLOOD BANK ID BAND for any order to give RBCs',
                '-Check if blood is ready in Powerchart via BB Product Inquiry. Do NOT call the Blood Bank to ask if blood is ready. DO call the Blood Bank if you see the blood is not ready and would like a status update.',
                '-Bring a copy of the order and blood consent to the Blood Bank for blood pick up.',
                'Perform identification and verification checks with another RN/physician/perfusionist at the recipient\'s bedside. Do not start the transfusion if there is any discrepancy. Verify audibly and view concurrently the following:',
                '-a. Blood product with physician\'s written order.',
                '-b. Patient identity by any combination of any two of the following: patient name, date of birth, medical record number, last four digits of Social Security number, photograph identification',
                '-c. Patient name and medical record number on the identification wristband, the blood transfusion administration record (TAR) and the compatibility label on the blood product.',
                '-d. Verifying that the unit number, donor ABO/Rh, Patient ABO/Rh, expiration date and compatibility label are identical for the blood bag, compatibility label and TAR.',
                '-e. The TAR remains with the patient throughout transfusion. Both staff members sign the chart copy of the TAR and place in patient chart',
                '-Blood transfusions must be started as soon as possible after blood product issuance. There is no need to return the blood to the Blood Bank if not started within 30 minutes.',
                '-Blood transfusions must be completed within 4 hours from issuance, regardless of start time.',
                '-RN must stay at the patient\'s bedside for the first 15 minutes of transfusion.',
                '-Obtain vital signs:',
                '--a. Pre-transfusion vital signs are required, including a temperature. Vital signs must be taken within 30 minutes prior to beginning transfusion.',
                '--b. The nurse remains with the patient for the first 15 minutes of the transfusion, monitoring the patient closely for signs/symptoms of transfusion reaction.',
                '--c. Vital signs are taken 15 minutes after the transfusion is initiated, hourly during infusion, and upon completion of each unit.',
                '--d. Post-transfusion, within 2 hours after completion of transfusion',
                'Exception: in critical situation, take vital signs no more than 15 min. prior start of transfusion and at completion of unit.',
                '-Premedicate patient if ordered.',
                '-Attach blood to tubing. Spike the blood bag before hanging it up onto the pole. Do not piggyback blood.',
                '-Upon completion of administration of blood, flush tubing with normal saline unless otherwise ordered.',
                '-Change blood tubing after every 2 units or every 4 hours.',
                '-See blood transfusion policy for usual lengths of transfusion. (for whole blood 2-4 hours, must be infused within 4 hours of leaving Blood Bank)',
                '-If no transfusion reaction noted, dispose of blood bag and tubing in biohazard container.',
                '-If transfusion was documented in Cerner Bridge and the patient has a transfusion reaction after the transfusion has ended, the reaction must be documented in Bridge.',
                '-If the blood transfusion was started on paper, continue documentation on paper.',
                '-Always notify the Blood Bank if there is an emergent need for blood at extension 8315.',
                '-Document patient/family teaching.',
                '-See policy for blood transfusion reaction signs/symptoms and for steps to take if transfusion reaction occurs.',
            ],
        ];

        $order = 0;
        $this->seedCompetencySet($dsdCompetencyItems, $dsdPositionIds, $order);

        if ($licensedNursePositionIds !== []) {
            $this->seedCompetencySet($licensedNurseCompetencyItems, $licensedNursePositionIds, $order);
        }

        if ($cnaPositionIds !== []) {
            $this->seedCompetencySet($cnaCompetencyItems, $cnaPositionIds, $order);
        }
    }
}