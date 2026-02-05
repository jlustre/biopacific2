<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobDescriptionsSeeder extends Seeder
{
    public function run(): void
    {
        // Get Registered Nurse position id
        $rn = DB::table('positions')->where('title', 'Registered Nurse')->first();
        if (!$rn) return;
        $position_id = $rn->id;

        $jobDescriptions = [
            [
                'title' => 'Job Summary',
                'description' => 'Registered Nurse provides direct and indirect resident care, supervises nursing staff, and ensures compliance with facility policies and state regulations.',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Key Responsibilities',
                'description' => 'Perform comprehensive nursing assessments and ongoing evaluations of resident conditions|Administer medications, treatments, and IV therapies in accordance with physician orders|Develop, implement, and update individualized care plans|Administer medications, treatments, and IV therapies in accordance with physician orders|Develop, implement, and update individualized care plans|Monitor residents for changes in condition and initiate appropriate interventions|Supervise and support Licensed Vocational Nurses (LVNs) and Certified Nursing Assistants (CNAs)|Ensure accurate and timely documentation in the electronic health record (EHR)|Communicate effectively with physicians, interdisciplinary team members, residents, and families|Participate in admissions, discharges, and transfers|Respond promptly to medical emergencies and changes in resident status|Promote infection prevention and control practices|Maintain resident rights, privacy, and confidentiality|Administer medications, treatments, and IV therapies in accordance with physician orders|Develop, implement, and update individualized care plans
|Monitor residents for changes in condition and initiate appropriate interventions|Supervise and support Licensed Vocational Nurses (LVNs) and Certified Nursing Assistants (CNAs)|Ensure accurate and timely documentation in the electronic health record (EHR)|Communicate effectively with physicians, interdisciplinary team members, residents, and families|Participate in admissions, discharges, and transfers|Respond promptly to medical emergencies and changes in resident status|Promote infection prevention and control practices|Maintain resident rights, privacy, and confidentiality',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Required Qualifications',
                'description' => 'Active Registered Nurse (RN) license in the State of California|Graduation from an accredited nursing program|Minimum of 1 year nursing experience (SNF or long-term care preferred)|Strong clinical assessment and critical-thinking skills|Ability to supervise and delegate effectively|Excellent communication and documentation skills',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Preferred Qualifications',
                'description' => 'Experience in skilled nursing or long-term care|Knowledge of California Title 22 regulations|Experience with electronic health record (EHR) systems|ACLS or geriatric nursing experience',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Licensure & Certificates',
                'description' => 'Current California RN license (required)|BLS (CPR) certification (required)|ACLS (preferred, facility-dependent)',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Physical Requirements',
                'description' => 'Ability to stand, walk, bend, and lift up to 50 lbs with or without assistance|Ability to respond quickly in emergency situations|Manual dexterity to administer medications and operate medical equipment',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Work Environment',
                'description' => 'Skilled Nursing Facility / Long-Term Care environment|Exposure to infectious diseases and bodily fluids|Fast-paced clinical setting requiring multitasking and teamwork',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Compliance & Regulatory Expectations',
                'description' => 'Adhere to all applicable federal and California healthcare regulations|Maintain compliance with CMS guidelines and state survey standards|Follow HIPAA privacy and confidentiality requirements|Observe OSHA safety standards and infection control protocols',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Clinical Duties',
                'description' => 'Conduct head-to-toe nursing assessments and document findings|Administer medications via oral, topical, subcutaneous, intramuscular, and intravenous routes
|Perform wound care, catheter care, enteral feeding management, and IV therapy|Monitor vital signs, lab results, and physician orders|Identify changes in resident condition and initiate timely interventions',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Documentation & Charting',
                'description' => 'Complete accurate, timely charting in compliance with legal and regulatory standards|Document assessments, care plans, medication administration, and incident reports|Ensure MDS documentation supports clinical findings and care delivery',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Supervision & Team Collaboration',
                'description' => 'Supervise LVNs and CNAs, ensuring proper delegation of tasks|Provide clinical guidance, coaching, and support to nursing staff|Participate in interdisciplinary team meetings and care conferences',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Resident Safety & Infection Control',
                'description' => 'Enforce infection prevention protocols, including isolation precautions|Promote fall prevention, skin integrity, and pressure injury prevention|Ensure proper use of PPE and compliance with infection control policies',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Family & Physician Communication',
                'description' => 'Communicate resident status changes to physicians promptly|Educate residents and families regarding care plans and treatments|Address family concerns professionally and compassionately',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Emergency & Incident Response',
                'description' => 'Respond to medical emergencies, including code situations|Initiate emergency protocols and notify appropriate personnel|Complete incident and accident reports accurately',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Quality Assurance & Compliance',
                'description' => 'Participate in quality assurance and performance improvement (QAPI) activities|Assist with survey readiness and regulatory inspections|Support corrective action plans as directed by nursing leadership',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Technology & EHR Usage (MatrixCare, PointClickCare, etc.)',
                'description' => 'Utilize EHR systems for documentation and care coordination|Maintain data accuracy and confidentiality within electronic systems',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Performance Metrics & KPIs',
                'description' => 'Medication error rates|Documentation accuracy and timeliness|Compliance with care plans and infection control standards|Resident outcomes and satisfaction indicators',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Ethical Standards & Professional Conduct',
                'description' => 'Uphold ethical nursing standards and professional boundaries|Advocate for resident rights and dignity|Maintain professionalism at all times',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Why Join Our Facility',
                'description' => 'Supportive leadership and collaborative care environment|Commitment to quality care and regulatory excellence|Opportunities for professional growth in long-term care nursing',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
            [
                'title' => 'Career Growth Opportunities',
                'description' => 'Advancement into Charge Nurse, Unit Manager, or Assistant Director of Nursing roles|Ongoing training and continuing education support',
                'position_id' => $position_id,
                'version' => 1.0,
            ],
        ];

        DB::table('job_descriptions')->insert($jobDescriptions);
    }
}
