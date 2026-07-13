<?php

namespace Database\Seeders;

use App\Models\EmployeeTrainingItem;
use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * Skilled nursing / long-term care compliance trainings for Part H.
 *
 * Active catalogs:
 * - California mandatory state compliance (Relias-coded / all-employee)
 * - CNA CDPH 278B in-service topics (Certified Nursing Assistant / Nursing Assistant)
 *
 * Legacy catalog items remain inactive until enabled in Training Configuration.
 *
 * Run: php artisan db:seed --class=EmployeeTrainingItemsSeeder
 */
class EmployeeTrainingItemsSeeder extends Seeder
{
    public function run(): void
    {
        $byTitle = Position::query()
            ->orderBy('id')
            ->get(['id', 'title'])
            ->keyBy('title');

        $ids = function (array $titles) use ($byTitle): array {
            return collect($titles)
                ->map(fn (string $title) => $byTitle->get($title)?->id)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        };

        $nursingClinical = $ids([
            'Certified Nursing Assistant',
            'Nursing Assistant',
            'Licensed Vocational Nurse',
            'Licensed Nurse',
            'Registered Nurse',
            'Charge Nurse',
            'Director of Nursing',
            'IP Nurse',
            'Staff Development Coordinator',
            'Director of Staff Development',
            'MDS Coordinator',
            'Unit Clerk',
        ]);

        $licensedNurses = $ids([
            'Licensed Vocational Nurse',
            'Licensed Nurse',
            'Registered Nurse',
            'Charge Nurse',
            'Director of Nursing',
            'IP Nurse',
            'MDS Coordinator',
        ]);

        $cnaStaff = $ids([
            'Certified Nursing Assistant',
            'Nursing Assistant',
        ]);

        $dietary = $ids([
            'Dietary Manager',
            'Dietary Aide',
            'Cook',
            'Food Services Director',
        ]);

        $environmental = $ids([
            'Housekeeper',
            'Housekeeping Supervisor',
            'Janitor',
            'Laundry Staff',
            'Maintenance Director',
            'Maintenance Technician',
        ]);

        $leadership = $ids([
            'Administrator',
            'Director of Nursing',
            'Director of Staff Development',
            'Staff Development Coordinator',
            'Business Office Manager',
            'Social Services Director',
            'Food Services Director',
            'Maintenance Director',
            'Housekeeping Supervisor',
            'Activities Director',
            'Rehab Manager',
            'Medical Records Director',
            'Marketing Director',
        ]);

        $socialActivities = $ids([
            'Social Services Director',
            'Social Worker',
            'Resident Liaison',
            'Case Manager',
            'Activities Director',
            'Activity Assistant',
        ]);

        $rehab = $ids([
            'Occupational Therapist',
            'Physical Therapist',
            'OT/PT Assistant',
            'Rehab Manager',
        ]);

        $order = 0;
        $trainings = [
            // —— California mandatory state compliance (ALL employees unless noted) ——
            [
                'name' => 'California Required Sexual Harassment Training for ALL Employees',
                'description' => 'Relias code: REL-ALL-0-CRSHTE. Mandatory California sexual harassment training for all employees. Provide for new hires within 6 months of hire, then every 2 years.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_BIENNIAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Relias (REL-ALL-0-CRSHTE)',
                'is_active' => true,
            ],
            [
                'name' => 'Understanding Sexual Harassment for Supervisors CA',
                'description' => 'Relias code: REL-ALL-0-CRSHTS. California sexual harassment training for supervisors. Provide within 6 months of assuming supervisory duties, then every 2 years.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_BIENNIAL,
                'position_ids' => $leadership !== [] ? $leadership : ['global'],
                'content_url' => null,
                'provider_label' => 'Relias (REL-ALL-0-CRSHTS)',
                'is_active' => true,
            ],
            [
                'name' => 'Workplace Violence Prevention in CA',
                'description' => 'Relias code: REL-PAC-0-WVPC. Completed upon hire and every year, and whenever new hazards are introduced. Required for all employees.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Relias (REL-PAC-0-WVPC)',
                'is_active' => true,
            ],
            [
                'name' => 'Ethics and Corporate Compliance',
                'description' => 'Relias code: REL-ALL-0-ETHCC. Completed upon hire and every year. Required for all employees.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Relias (REL-ALL-0-ETHCC)',
                'is_active' => true,
            ],
            [
                'name' => 'HIPAA: Basics',
                'description' => 'Relias code: REL-ALL-0-HBAS. Completed upon hire and every year. Required for all employees.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Relias (REL-ALL-0-HBAS)',
                'is_active' => true,
            ],
            [
                'name' => 'Caring for LGBTQIA+ Residents in California',
                'description' => 'Relias code: REL-PAC-0-CLFBRC. Completed upon hire and every year. Required for all employees, including the Medical Director for each facility.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Relias (REL-PAC-0-CLFBRC)',
                'is_active' => true,
            ],
            [
                'name' => 'WVPP (Workplace Violence Prevention Plan)',
                'description' => 'Facility Workplace Violence Prevention Plan acknowledgement/training. Completed upon hire and every year. Required for all employees.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Internal',
                'is_active' => true,
            ],
            [
                'name' => 'SB 294: California Workplace – Know Your Rights',
                'description' => 'California SB 294 workplace Know Your Rights training. Completed upon hire and every year. Required for all employees.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => null,
                'provider_label' => 'Internal / State Compliance',
                'is_active' => true,
            ],

            // —— CNA required in-service (CDPH 278B / 24-hour annual program) ——
            // Positions: Certified Nursing Assistant, Nursing Assistant
            // Elder abuse: 4 hrs every 2 years; Dementia: 5 hrs annually; remaining topics support the 24-hr annual total.
            ...array_map(
                static function (array $row) use ($cnaStaff): array {
                    return [
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'frequency' => $row['frequency'],
                        'position_ids' => $cnaStaff !== [] ? $cnaStaff : ['global'],
                        'content_url' => null,
                        'provider_label' => 'CDPH 278B / CNA In-Service',
                        'is_active' => true,
                    ];
                },
                [
                    [
                        'name' => 'CNA: Problem Needs of the Aged',
                        'description' => 'CDPH CNA in-service — 2 hours. Problem and care needs of the aged.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Prevention and Control of Infection',
                        'description' => 'CDPH CNA in-service — 2 hours. Prevention and control of infection.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Fire Prevention / Disaster Preparedness',
                        'description' => 'CDPH CNA in-service — 4 hours. Fire prevention and disaster preparedness.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Accident Prevention and Safety',
                        'description' => 'CDPH CNA in-service — 2 hours. Accident prevention and safety.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Residents’ Rights',
                        'description' => 'CDPH CNA in-service — 2 hours. Residents’ rights.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Bloodborne Pathogens',
                        'description' => 'CDPH CNA in-service — 2 hours. Bloodborne pathogens.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Choking Prevention',
                        'description' => 'CDPH CNA in-service — 2 hours. Choking prevention.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Theft and Loss',
                        'description' => 'CDPH CNA in-service — 2 hours. Theft and loss prevention/reporting.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: TB Facts',
                        'description' => 'CDPH CNA in-service — 1 hour. Tuberculosis facts.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: HIPAA',
                        'description' => 'CDPH CNA in-service — 1 hour. HIPAA privacy for CNAs.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Hazard Communication / SDS / PPE',
                        'description' => 'CDPH CNA in-service — 1 hour. Hazard communication, SDS, and PPE.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Interpersonal Relationships / Communication Skills',
                        'description' => 'CDPH CNA in-service — 1 hour. Interpersonal relationships and communication skills.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Advance Directives',
                        'description' => 'CDPH CNA in-service — 1 hour. Advance directives.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Bowel and Bladder Retraining',
                        'description' => 'CDPH CNA in-service — 1 hour. Bowel and bladder (B&B) retraining.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Wandering Resident / Elopement',
                        'description' => 'CDPH CNA in-service — 1 hour. Wandering resident and elopement prevention.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Elder Abuse and Neglect',
                        'description' => 'CDPH CNA in-service — 4 hours. Resident abuse training required every 2 years (each renewal period).',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_BIENNIAL,
                    ],
                    [
                        'name' => 'CNA: Fall Prevention',
                        'description' => 'CDPH CNA in-service — 2 hours. Fall prevention.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Pressure Ulcer Prevention',
                        'description' => 'CDPH CNA in-service — 2 hours. Pressure ulcer prevention.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Nutrition and Hydration',
                        'description' => 'CDPH CNA in-service — 1 hour. Nutrition and hydration.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Dementia',
                        'description' => 'CDPH CNA in-service — 5 hours. Dementia-specific training required every year.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Multiple Sclerosis',
                        'description' => 'CDPH CNA in-service — 1 hour. Multiple sclerosis care awareness.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Dignity and Privacy',
                        'description' => 'CDPH CNA in-service — 1 hour. Dignity and privacy.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: How to Deal with Combative Residents',
                        'description' => 'CDPH CNA in-service — 1 hour. Working with combative residents.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Care of Residents with CVA and Diabetes',
                        'description' => 'CDPH CNA in-service — 1 hour. Care of residents with CVA and diabetes.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Restraint Reduction / ROM',
                        'description' => 'CDPH CNA in-service — 1 hour. Restraint reduction and range of motion (ROM).',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Signs/Symptoms of Cardiopulmonary Disease',
                        'description' => 'CDPH CNA in-service — 1 hour. Signs and symptoms of cardiopulmonary disease.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Turning and Repositioning (Actual Demo)',
                        'description' => 'CDPH CNA in-service — 1 hour. Turning and repositioning with actual demonstration.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Safe Transfers',
                        'description' => 'CDPH CNA in-service — 2 hours. Safe transfers.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Sexual Harassment',
                        'description' => 'CDPH CNA in-service — 1 hour. Sexual harassment training for CNAs (facility in-service program).',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                    [
                        'name' => 'CNA: Pain Management',
                        'description' => 'CDPH CNA in-service — 1 hour. Pain management.',
                        'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                    ],
                ]
            ),

            // —— Legacy Part H catalog (kept inactive; activate as needed) ——
            // —— Global hiring (all positions) ——
            [
                'name' => 'New Hire Orientation & Facility Tour',
                'description' => 'Facility orientation covering mission, chain of command, emergency exits, and resident rights overview.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => '/trainings/new-hire-orientation',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'HIPAA Privacy & Security Awareness',
                'description' => 'Protected health information (PHI), minimum necessary standard, and breach reporting.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/hipaa-privacy',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Abuse, Neglect & Exploitation Reporting',
                'description' => 'Mandated reporting, recognition of abuse/neglect, and immediate notification procedures (SNF compliance).',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/abuse-neglect-reporting',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Infection Prevention Basics (Standard Precautions)',
                'description' => 'Hand hygiene, PPE, transmission-based precautions, and bloodborne pathogens overview.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => '/trainings/infection-prevention-basics',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'Emergency Preparedness & Fire Safety',
                'description' => 'RACE/PASS, evacuation routes, disaster roles, and facility emergency codes.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => '/trainings/emergency-preparedness',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'Corporate Compliance & Code of Conduct',
                'description' => 'Fraud/waste/abuse awareness, false claims, and how to report compliance concerns.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/corporate-compliance',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Workplace Violence Prevention',
                'description' => 'De-escalation, reporting workplace violence, and staff safety expectations.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/workplace-violence',
                'provider_label' => 'LMS Provider',
            ],

            // —— Global annual ——
            [
                'name' => 'Annual HIPAA Refresher',
                'description' => 'Annual privacy/security refresher required for all workforce members with PHI access.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/hipaa-annual',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Annual Abuse Prevention & Mandated Reporting',
                'description' => 'Annual refresher on abuse prevention, elder justice, and reporting timelines.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/abuse-prevention-annual',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Annual Infection Control & Bloodborne Pathogens',
                'description' => 'OSHA bloodborne pathogens and facility infection prevention annual update.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/infection-control-annual',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Annual Fire Safety & Disaster Drill Acknowledgement',
                'description' => 'Document participation/acknowledgement of annual fire and disaster preparedness training.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => '/trainings/fire-safety-annual',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'Annual Compliance & Ethics Refresher',
                'description' => 'Annual compliance program training including reporting hotline and non-retaliation.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => ['global'],
                'content_url' => 'https://lms.example.com/modules/compliance-annual',
                'provider_label' => 'LMS Provider',
            ],

            // —— Nursing clinical hiring ——
            [
                'name' => 'Resident Rights & Person-Centered Care',
                'description' => 'Dignity, privacy, choice, and person-centered care expectations for clinical staff.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $nursingClinical !== [] ? $nursingClinical : ['global'],
                'content_url' => '/trainings/resident-rights',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'Fall Prevention & Safe Resident Handling',
                'description' => 'Fall risk interventions, gait belts, and safe patient handling equipment use.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $nursingClinical !== [] ? $nursingClinical : ['global'],
                'content_url' => 'https://lms.example.com/modules/fall-prevention',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Pressure Injury Prevention',
                'description' => 'Skin assessment, repositioning, and pressure injury prevention protocols.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $nursingClinical !== [] ? $nursingClinical : ['global'],
                'content_url' => 'https://lms.example.com/modules/pressure-injury-prevention',
                'provider_label' => 'LMS Provider',
            ],

            // —— CNA ——
            [
                'name' => 'CNA Skills Competency Orientation',
                'description' => 'Core CNA skills verification orientation (ADL care, vitals assist, documentation basics).',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $cnaStaff !== [] ? $cnaStaff : ['global'],
                'content_url' => '/trainings/cna-skills-orientation',
                'provider_label' => 'Internal / DSD',
            ],
            [
                'name' => 'CNA 12-Hour Annual In-Service',
                'description' => 'California/SNF CNA annual in-service hours tracking acknowledgement.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $cnaStaff !== [] ? $cnaStaff : ['global'],
                'content_url' => '/trainings/cna-annual-inservice',
                'provider_label' => 'Internal / DSD',
            ],

            // —— Licensed nurses ——
            [
                'name' => 'Medication Administration & eMAR Safety',
                'description' => 'Five/six rights, eMAR documentation, controlled substances, and med error reporting.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $licensedNurses !== [] ? $licensedNurses : ['global'],
                'content_url' => 'https://lms.example.com/modules/medication-administration',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Antibiotic Stewardship Awareness',
                'description' => 'Appropriate antibiotic use and infection prevention collaboration for licensed nurses.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $licensedNurses !== [] ? $licensedNurses : ['global'],
                'content_url' => 'https://lms.example.com/modules/antibiotic-stewardship',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Change of Condition & SBAR Communication',
                'description' => 'Recognizing change of condition and structured SBAR communication with providers.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $licensedNurses !== [] ? $licensedNurses : ['global'],
                'content_url' => '/trainings/sbar-change-of-condition',
                'provider_label' => 'Internal',
            ],

            // —— Dietary ——
            [
                'name' => 'Food Safety & Sanitation (ServSafe Basics)',
                'description' => 'Temperature control, cross-contamination prevention, and dietary sanitation standards.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $dietary !== [] ? $dietary : ['global'],
                'content_url' => 'https://lms.example.com/modules/food-safety',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Texture-Modified Diets & Aspiration Precautions',
                'description' => 'IDDSI/texture diets, thickening liquids, and aspiration risk precautions.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $dietary !== [] ? $dietary : ['global'],
                'content_url' => '/trainings/texture-modified-diets',
                'provider_label' => 'Internal',
            ],

            // —— EVS / Maintenance ——
            [
                'name' => 'Environmental Cleaning & Disinfection Protocols',
                'description' => 'Terminal cleaning, EPA-registered disinfectants, and isolation room cleaning.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $environmental !== [] ? $environmental : ['global'],
                'content_url' => 'https://lms.example.com/modules/environmental-cleaning',
                'provider_label' => 'LMS Provider',
            ],
            [
                'name' => 'Hazard Communication (GHS / SDS)',
                'description' => 'Chemical labeling, Safety Data Sheets, and PPE for facility chemicals.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $environmental !== [] ? $environmental : ['global'],
                'content_url' => 'https://lms.example.com/modules/hazard-communication',
                'provider_label' => 'LMS Provider',
            ],

            // —— Social / Activities ——
            [
                'name' => 'Resident Rights Advocacy & Grievance Process',
                'description' => 'Grievance procedures, ombudsman role, and psychosocial well-being support.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $socialActivities !== [] ? $socialActivities : ['global'],
                'content_url' => '/trainings/resident-rights-advocacy',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'Trauma-Informed & Dementia Care Basics',
                'description' => 'Communication strategies for dementia and trauma-informed approaches.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $socialActivities !== [] ? $socialActivities : ['global'],
                'content_url' => 'https://lms.example.com/modules/dementia-care',
                'provider_label' => 'LMS Provider',
            ],

            // —— Rehab ——
            [
                'name' => 'Rehab Documentation & Medicare Compliance Basics',
                'description' => 'Therapy documentation standards and medical necessity awareness for rehab staff.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_HIRING,
                'position_ids' => $rehab !== [] ? $rehab : ['global'],
                'content_url' => 'https://lms.example.com/modules/rehab-documentation',
                'provider_label' => 'LMS Provider',
            ],

            // —— Leadership annual ——
            [
                'name' => 'QAPI & Survey Readiness Overview',
                'description' => 'Quality Assurance and Performance Improvement roles and survey readiness expectations for leaders.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $leadership !== [] ? $leadership : ['global'],
                'content_url' => '/trainings/qapi-survey-readiness',
                'provider_label' => 'Internal',
            ],
            [
                'name' => 'Staffing, Payroll Accuracy & Timekeeping Compliance',
                'description' => 'Accurate timekeeping, overtime awareness, and staffing documentation expectations for supervisors.',
                'frequency' => EmployeeTrainingItem::FREQUENCY_ANNUAL,
                'position_ids' => $leadership !== [] ? $leadership : ['global'],
                'content_url' => 'https://lms.example.com/modules/staffing-timekeeping',
                'provider_label' => 'LMS Provider',
            ],
        ];

        foreach ($trainings as $training) {
            $order++;
            EmployeeTrainingItem::query()->updateOrCreate(
                ['name' => $training['name']],
                [
                    'description' => $training['description'],
                    'frequency' => $training['frequency'],
                    'position_ids' => $training['position_ids'] === [] ? ['global'] : $training['position_ids'],
                    'content_url' => $training['content_url'],
                    'provider_label' => $training['provider_label'],
                    'order' => $order,
                    'is_active' => (bool) ($training['is_active'] ?? false),
                ]
            );
        }

        $activeCount = collect($trainings)->where('is_active', true)->count();
        $this->command?->info(
            'Seeded '.count($trainings).' employee training items ('.$activeCount.' active: CA state compliance + CDPH CNA in-service; legacy catalog inactive).'
        );
    }
}
