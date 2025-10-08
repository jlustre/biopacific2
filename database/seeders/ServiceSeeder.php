<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Global services
            ['title' => '24/7 Nursing Care', 'description' => 'Round-the-clock professional nursing services.', 'is_global' => true],
            ['title' => 'Recreation & Activities', 'description' => 'Engaging daily activities and events.', 'is_global' => true],
            ['title' => 'Dining & Nutrition', 'description' => 'Nutritious meals and dietary support.', 'is_global' => true],
            ['title' => 'Hospice Care', 'description' => 'Compassionate end-of-life care and support.', 'is_global' => true],
            ['title' => 'Long-term Care', 'description' => 'Extended stay and support for residents.', 'is_global' => true],
            ['title' => 'Memory Care', 'description' => 'Specialized support for memory impairment.', 'is_global' => true],
            ['title' => 'Physical Therapy', 'description' => 'Personalized rehabilitation and therapy.', 'is_global' => true],
            ['title' => 'Rehabilitation', 'description' => 'Comprehensive rehabilitation services for recovery.', 'is_global' => true],
            ['title' => 'Transportation', 'description' => 'Safe and reliable transportation services for residents.', 'is_global' => true],
            // Facility-specific services
            ['title' => 'Wound Care', 'description' => 'Advanced wound management and healing services.', 'is_global' => false],
            ['title' => 'Dialysis', 'description' => 'On-site dialysis treatment and support.', 'is_global' => false],
            ['title' => 'Respiratory Therapy', 'description' => 'Specialized respiratory care and therapy.', 'is_global' => false],
            ['title' => 'IV Therapy', 'description' => 'Intravenous therapy and medication management.', 'is_global' => false],
            ['title' => 'Pain Management', 'description' => 'Comprehensive pain assessment and management.', 'is_global' => false],
            ['title' => 'Palliative Care', 'description' => 'Comfort-focused care for serious illness.', 'is_global' => false],
            ['title' => 'Behavioral Health', 'description' => 'Mental health and behavioral support services.', 'is_global' => false],
            ['title' => 'Short-Term Rehab', 'description' => 'Intensive rehabilitation for short-term recovery.', 'is_global' => false],
            ['title' => 'Speech Therapy', 'description' => 'Speech and language therapy services.', 'is_global' => false],
            ['title' => 'Occupational Therapy', 'description' => 'Therapy to improve daily living skills.', 'is_global' => false],
        ];
        foreach ($services as $service) {
            Service::updateOrCreate(['title' => $service['title']], $service);
        }
    }
}
