<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Skilled Nursing',
                'short_description' => 'Round-the-clock professional nursing services.',
                'detailed_description' => 'Our Skilled Nursing services provide 24/7 care from licensed professionals, ensuring every resident receives the highest level of medical attention and support. We offer medication management, wound care, IV therapy, and pain management in a compassionate, home-like environment.',
                'is_global' => true,
                'order' => 1,
                'image' => 'images/skilled_nursing.png',
                'features' => [
                    '24/7 licensed nursing staff',
                    'Medication management',
                    'Wound care',
                    'IV therapy',
                    'Pain management',
                ],
            ],
            [
                'name' => 'Rehabilitation',
                'short_description' => 'Comprehensive rehabilitation services for recovery.',
                'detailed_description' => 'Our Rehabilitation program is designed to help residents regain strength, mobility, and independence after illness or injury. We provide physical, occupational, and speech therapy with personalized care plans tailored to each individual’s needs.',
                'is_global' => true,
                'order' => 2,
                'image' => 'images/rehab_care.png',
                'features' => [
                    'Physical therapy',
                    'Occupational therapy',
                    'Speech therapy',
                    'Personalized care plans',
                ],
            ],
            [
                'name' => 'Long-term Care',
                'short_description' => 'Extended stay and support for residents.',
                'detailed_description' => 'Our Long-term Care services offer a supportive environment for residents who need ongoing assistance with daily living. We focus on comfort, dignity, and quality of life, providing social and recreational activities as well as personalized care.',
                'is_global' => true,
                'order' => 3,
                'image' => 'images/long_term_care.png',
                'features' => [
                    'Daily living assistance',
                    'Social and recreational activities',
                    'Personalized care',
                ],
            ],
            [
                'name' => 'Memory Care',
                'short_description' => 'Specialized support for memory impairment.',
                'detailed_description' => 'Our Memory Care program is tailored for residents with Alzheimer’s, dementia, or other memory impairments. We provide a secure environment, specialized staff, and therapeutic activities to promote engagement and well-being.',
                'is_global' => true,
                'order' => 4,
                'image' => 'images/memory_care.png',
                'features' => [
                    'Secure environment',
                    'Specialized staff',
                    'Therapeutic activities',
                ],
            ],
            [
                'name' => 'Hospice Care',
                'short_description' => 'Compassionate end-of-life care and support.',
                'detailed_description' => 'Our Hospice Care services provide compassionate support for residents and their families during end-of-life care. We focus on pain and symptom management, emotional and spiritual support, and family counseling in a respectful, comforting setting.',
                'is_global' => true,
                'order' => 5,
                'image' => 'images/hospice_care.png',
                'features' => [
                    'Pain and symptom management',
                    'Emotional and spiritual support',
                    'Family counseling',
                ],
            ],
            [
                'name' => 'Dining & Nutrition',
                'short_description' => 'Nutritious meals and dietary support.',
                'detailed_description' => 'Our Dining & Nutrition services offer dietitian-approved menus, special dietary accommodations, and flexible meal times. We are committed to providing nutritious, delicious meals that support the health and preferences of every resident.',
                'is_global' => true,
                'order' => 6,
                'image' => 'images/dining_and_nutrition_care.png',
                'features' => [
                    'Dietitian-approved menus',
                    'Special dietary accommodations',
                    'Flexible meal times',
                ],
            ],
            [
                'name' => 'Recreation & Activities',
                'short_description' => 'Engaging daily activities and events.',
                'detailed_description' => 'Our Recreation & Activities program offers a variety of daily group activities, arts and crafts, music, and entertainment. We encourage socialization and engagement to enhance the quality of life for all residents.',
                'is_global' => true,
                'order' => 7,
                'image' => 'images/recreation_and_activities_care2.png',
                'features' => [
                    'Daily group activities',
                    'Arts and crafts',
                    'Music and entertainment',
                ],
            ],
            [
                'name' => 'Transportation',
                'short_description' => 'Safe and reliable transportation services for residents.',
                'detailed_description' => 'Our Transportation services ensure residents have access to scheduled outings, medical appointments, and community events. We provide safe, reliable, and wheelchair-accessible vehicles for all transportation needs.',
                'is_global' => true,
                'order' => 8,
                'image' => 'images/transportation_care2.png',
                'features' => [
                    'Scheduled outings',
                    'Medical appointment transport',
                    'Wheelchair accessible vehicles',
                ],
            ],
        ];
        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                [
                    'name' => $service['name'],
                    'short_description' => $service['short_description'] ?? null,
                    'is_global' => $service['is_global'],
                    'detailed_description' => $service['detailed_description'] ?? $service['short_description'] ?? null,
                    'order' => $service['order'],
                    'is_active' => true,
                    'image' => $service['image'] ?? null,
                    'features' => $service['features'] ?? [],
                ]
            );
        }
    }
}
