<?php

namespace Database\Seeders;

use App\Models\EmployeePerformanceItem;
use App\Support\PerformanceAppraisalTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeePerformanceItemsSeeder extends Seeder
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

    protected function seedPerformanceItems(array $items, int &$order): void
    {
        foreach ($items as $row) {
            $positionIds = [];
            foreach ($row['categories'] as $category) {
                $positionIds = array_merge(
                    $positionIds,
                    PerformanceAppraisalTemplate::positionIdsForTemplate($category)
                );
            }

            $positionIds = array_values(array_unique(array_map('intval', $positionIds)));

            EmployeePerformanceItem::query()->updateOrCreate(
                [
                    'section' => $row['section'],
                    'item' => $row['item'],
                ],
                [
                    'position_ids' => $positionIds,
                    'order' => $order,
                ]
            );

            $order++;
        }
    }

    public function run(): void
    {
        $exportedDataPath = database_path('seeders/data/employee_performance_items.php');
        if (is_file($exportedDataPath)) {
            $exported = require $exportedDataPath;
            if (is_array($exported)) {
                $this->seedExportedCatalog($exported);

                return;
            }
        }

        EmployeePerformanceItem::query()->delete();

        $order = 0;
        $items = [
            [
                'section' => 'Job Skills and Knowledge',
                'item' => 'Demonstrates proficiency in all phases of the job.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Job Skills and Knowledge',
                'item' => 'Initiates work projects without prompting, once briefed.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Job Skills and Knowledge',
                'item' => 'Produces complete and accurate work.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Job Skills and Knowledge',
                'item' => 'Understands the job role and duties.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Dependability',
                'item' => 'Maintains confidentiality.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Dependability',
                'item' => 'Remains at work area as required.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Dependability',
                'item' => 'Starts work promptly and can be depended upon to be available for work.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Dependability',
                'item' => 'Takes appropriate actions and follows instructions as directed.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Interpersonal Skills',
                'item' => 'Adapts to changing situations.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Interpersonal Skills',
                'item' => 'Considers viewpoints of others and accepts constructive feedback.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Interpersonal Skills',
                'item' => 'Cooperates with other employees in a positive, supportive, and courteous manner.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Interpersonal Skills',
                'item' => 'Willing to assist others in accomplishing additional work.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Organizational Skills',
                'item' => 'Coordinates and maintains current work flow.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Organizational Skills',
                'item' => 'Pays attention to detail.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Organizational Skills',
                'item' => 'Recognizes priorities and meets deadlines.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Organizational Skills',
                'item' => 'Works well under pressure.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Communication Skills',
                'item' => 'Clearly defines expectations of staff.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Communication Skills',
                'item' => 'Consistently fosters respect in the workplace and demonstrates Company\'s Guiding Principles and Values.',
                'categories' => ['cna', 'general_services'],
            ],
            [
                'section' => 'Communication Skills',
                'item' => 'Consistently fosters respect in the workplace and demonstrates Company’s Guiding Principles and Values.',
                'categories' => ['dietary_aide', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Communication Skills',
                'item' => 'Listens effectively and expresses understanding.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'management', 'rn_lvn'],
            ],
            [
                'section' => 'Communication Skills',
                'item' => 'Promotes understanding and acceptance of individual and cultural differences in the workplace.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Communication Skills',
                'item' => 'Provides clear, concise, and accurate verbal and written information in an appropriate and timely manner.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'management', 'rn_lvn'],
            ],
            [
                'section' => 'Problem-Solving Skills',
                'item' => 'Anticipates and identifies potential problems.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Problem-Solving Skills',
                'item' => 'Considers possible alternatives and makes thoughtful recommendations.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Problem-Solving Skills',
                'item' => 'Identifies existing problems.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Problem-Solving Skills',
                'item' => 'Knows how and where to obtain necessary information.',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Assure chemicals (solvents, degreasers, plumbing related substances, etc.) have Safety Data Sheets and follows related precautions',
                'categories' => ['maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Assures care plan includes use of patient handling equipment whenever possible, but a minimum of two persons per Company policy and Cal/OSHA regulations.',
                'categories' => ['rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Assures direct reports exhibit and practice safe work habits according to policy',
                'categories' => ['management'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Assures LPN\'s and nursing assistants follows Patient Profile/Care plan as written',
                'categories' => ['rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Attends to created and observed spills and other slip hazards immediately upon discovery',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Avoids “bunching” wet laundry that increases weight and stress on shoulders and backs',
                'categories' => ['laundry_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Conducts and documents regular observations to catch staff doing things right and to identify improvement opportunities',
                'categories' => ['management'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Conducts and records preventative maintenance on all equipment according to manufacturers\' guidelines, including patient handling lifts, etc.',
                'categories' => ['maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Considers possible alternatives and makes thoughtful recommendations concerning safety & health',
                'categories' => ['management'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Considers possible alternatives and makes thoughtful recommendations to safety committee, actively participates in training, and promotes safety culture',
                'categories' => ['dietary_aide', 'laundry_aide', 'maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Considers possible alternatives and makes thoughtful recommendations to safety committee, actively participates in training, and promotes safety culture.',
                'categories' => ['cna', 'general_services', 'housekeeper', 'management', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Disposes of trash and waste, including biohazardous waste that requires special handling, in accordance with Company bloodborne pathogen policies and procedures',
                'categories' => ['cna', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Disposes of trash and waste, including biohazardous waste that requires special handling, in accordance with Company policies and procedures',
                'categories' => ['general_services', 'housekeeper'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Exhibits concern for the safety & health of residents and colleagues',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'management', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Exhibits concern for the safety & health of residents, colleagues, and line staff',
                'categories' => ['management'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Follows best practices for workstation ergonomics as guided by management',
                'categories' => ['general_services'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Follows Patient Profile/Care plan as written',
                'categories' => ['cna', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Follows safe work practices as prescribed by Company policy',
                'categories' => ['cna', 'dietary_aide', 'laundry_aide', 'maintenance', 'management', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Follows universal biohazard precautions while handling contaminated laundry',
                'categories' => ['laundry_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Follows universal biohazard precautions while handling plumbing snakes and similar equipment',
                'categories' => ['maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Investigates incidents immediately, seeks root causation, and prescribes/implements effective and sustainable corrective measures',
                'categories' => ['management'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Keeps electrical cords and similar hazards out of walking paths',
                'categories' => ['general_services', 'housekeeper'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Maintains a clean and clutter-free environment',
                'categories' => ['cna', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Maintains good housekeeping in and around work area (clear aisles and cooridors, under desk clearance, etc.)',
                'categories' => ['general_services'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Monitors sharps containers for replacement',
                'categories' => ['cna', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Participates is safety and health initiatives as needed and/or requested',
                'categories' => ['general_services'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Properly posts CAUTION signs during mopping duties',
                'categories' => ['housekeeper'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Properly uses the correct ladders for the job',
                'categories' => ['maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Pushes food carts rather than pull',
                'categories' => ['dietary_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Removes or otherwise protects trip hazards',
                'categories' => ['dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Reports unsafe conditions and practices as observed',
                'categories' => ['dietary_aide', 'housekeeper', 'laundry_aide', 'maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Reports unsafe conditions and practices as observed. Corrects on the spot if possible.',
                'categories' => ['general_services'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Requests second person for assistance on all patient handling tasks (lifting, transferring, repositioning) when equipment use is not possible',
                'categories' => ['cna', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Sets an example for hazard ownership (correct or protect and report)',
                'categories' => ['management'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Takes ownership of observed hazards (correct or protect and report). Contribute sustainable ideas to help build and maintain a safety culture',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'management', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'To the extent possible, uses VanishPoint or similar safety syringes as prescribed by Company policy',
                'categories' => ['rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses and stores knives safely',
                'categories' => ['dietary_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses clean warm water for mopping and changes water out as needed',
                'categories' => ['housekeeper'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses cut gloves while cutting or chopping',
                'categories' => ['dietary_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses extender tools while reaching high surfaces for cleaning',
                'categories' => ['housekeeper'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses prescribed hand and eye protection while recharging chemical laundry products',
                'categories' => ['laundry_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses prescribed hand and eye protection while using chemical cleaners',
                'categories' => ['dietary_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses prescribed hand and eye protection while using chemical cleaners or when working overhead',
                'categories' => ['housekeeper'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses prescribed hand, ear, and eye protection as the job requires',
                'categories' => ['maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses proper and well maintained hand and power tools suitable for the job',
                'categories' => ['maintenance'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses proper equipment such as stools or proper ladder for tasks involving reaching overhead',
                'categories' => ['general_services'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses provided laundry carts equipped with spring-loaded false bottoms to reduce bending/stooping',
                'categories' => ['laundry_aide'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Uses provided patient handing equipment such as full body lifts, sit-to-stand, gait belts, etc. to enhance both resident and CNA safety and in accordance with Company’s “minimal/no lift” policy',
                'categories' => ['cna', 'rn_lvn'],
            ],
            [
                'section' => 'Safety & Health',
                'item' => 'Wears slip resistant footwear',
                'categories' => ['cna', 'dietary_aide', 'general_services', 'housekeeper', 'laundry_aide', 'maintenance', 'rn_lvn'],
            ],
            [
                'section' => 'Knowledge & Experience',
                'item' => 'Fully competent and knowledgeable in his/her job role.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Knowledge & Experience',
                'item' => 'Understands and applies technical and professional information and skills.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Knowledge & Experience',
                'item' => 'Understands Company’s business and applies operational knowledge to job.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Organization Skills',
                'item' => 'Establishes policies, procedures, and reports to monitor and assess staff activities, and areas of responsibility.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Organization Skills',
                'item' => 'Organizes work, establishes priorities, makes proper assignments of staff, and efficiently allocates resources',
                'categories' => ['management'],
            ],
            [
                'section' => 'Organization Skills',
                'item' => 'Plans appropriately to meet time commitments.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Decision Making Skills',
                'item' => 'Assumes personal responsibility to effectively address issues and problems.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Decision Making Skills',
                'item' => 'Identifies and analyzes problems in a timely manner and implements appropriate solutions.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Decision Making Skills',
                'item' => 'Uses appropriate resources to resolve problems and make decisions.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Leadership Skills',
                'item' => 'Develops staff by improving their skills and competencies for current and future jobs.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Leadership Skills',
                'item' => 'Establishes a vision for work group and helps staff to set objectives and goals.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Leadership Skills',
                'item' => 'Gives feedback and coaching to staff to encourage optimal performance.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Personal Integrity',
                'item' => 'Creates an environment in which diversity (style, gender, race, etc.) is seen as positive.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Personal Integrity',
                'item' => 'Demonstrates ethical business behavior and lives Company’s Guiding Principles and Values.',
                'categories' => ['management'],
            ],
            [
                'section' => 'Personal Integrity',
                'item' => 'Fosters respect in the workplace and treats coworkers/residents with dignity',
                'categories' => ['management'],
            ],
        ];

        $this->seedPerformanceItems($items, $order);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected function seedExportedCatalog(array $items): void
    {
        $positionIdsByTitle = DB::table('positions')
            ->orderBy('id')
            ->pluck('id', 'title');

        foreach ($items as $index => $row) {
            $section = trim((string) ($row['section'] ?? ''));
            $item = (string) ($row['item'] ?? '');
            if ($section === '' || $item === '') {
                continue;
            }

            $positionTitles = (array) ($row['position_titles'] ?? ['global']);
            $positionIds = in_array('global', $positionTitles, true)
                ? ['global']
                : collect($positionTitles)
                    ->map(fn ($title) => $positionIdsByTitle->get((string) $title))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

            if ($positionIds === []) {
                $positionIds = ['global'];
            }

            EmployeePerformanceItem::query()->updateOrCreate(
                [
                    'section' => $section,
                    'item' => $item,
                ],
                [
                    'position_ids' => $positionIds,
                    'order' => (int) ($row['order'] ?? $index),
                ]
            );
        }

        $this->command?->info('Seeded '.count($items).' exported employee performance items.');
    }
}
