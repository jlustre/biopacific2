<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeePerformanceItemsSeeder extends Seeder
{
    public function run()
    {
        // Use plain section names for DB, Roman numerals for display only
        $sections = [
            'Job Skills and Knowledge' => [
                'Understands the job role and duties.',
                'Initiates work projects without prompting, once briefed.',
                'Demonstrates proficiency in all phases of the job.',
                'Produces complete and accurate work.',
            ],
            'Dependability' => [
                'Starts work promptly and can be depended upon to be available for work.',
                'Remains at work area as required.',
                'Maintains confidentiality.',
                'Takes appropriate actions and follows instructions as directed.',
            ],
            'Interpersonal Skills' => [
                'Adapts to changing situations.',
                'Willing to assist others in accomplishing additional work.',
                'Considers viewpoints of others and accepts constructive feedback.',
                'Cooperates with other employees in a positive, supportive, and courteous manner.',
            ],
            'Organizational Skills' => [
                'Coordinates and maintains current work flow.',
                'Recognizes priorities and meets deadlines.',
                'Pays attention to detail.',
                'Works well under pressure.',
            ],
            'Communication Skills' => [
                'Listens effectively and expresses understanding.',
                'Consistently fosters respect in the workplace and demonstrates Company\'s Guiding Principles and Values.',
                'Promotes understanding and acceptance of individual and cultural differences in the workplace.',
                'Provides clear, concise, and accurate verbal and written information in an appropriate and timely manner.',
            ],
            'Problem-Solving Skills' => [
                'Identifies existing problems.',
                'Anticipates and identifies potential problems.',
                'Knows how and where to obtain necessary information.',
                'Considers possible alternatives and makes thoughtful recommendations.',
            ],
            'Safety & Health' => [
                'Follows best practices for workstation ergonomics as guided by management',
                'Maintains good housekeeping in and around work area (clear aisles and cooridors, under desk clearance, etc.)',
                'Participates is safety and health initiatives as needed and/or requested',
                'Attends to created and observed spills and other slip hazards immediately upon discovery',
                'Wears slip resistant footwear',
                'Keeps electrical cords and similar hazards out of walking paths',
                'Removes or otherwise protects trip hazards',
                'Uses proper equipment such as stools or proper ladder for tasks involving reaching overhead',
                'Disposes of trash and waste, including biohazardous waste that requires special handling, in accordance with Company policies and procedures',
                'Reports unsafe conditions and practices as observed. Corrects on the spot if possible.',
                'Takes ownership of observed hazards (correct or protect and report). Contribute sustainable ideas to help build and maintain a safety culture',
                'Exhibits concern for the safety & health of residents and colleagues',
                'Considers possible alternatives and makes thoughtful recommendations to safety committee, actively participates in training, and promotes safety culture.',
            ],
        ];
        $order = 0;
        foreach ($sections as $section => $items) {
            foreach ($items as $item) {
                DB::table('employee_performance_items')->insert([
                    'section' => $section,
                    'item' => $item,
                    'order' => $order++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
