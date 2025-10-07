<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['title' => '24/7 Nursing Care', 'description' => 'Round-the-clock professional nursing services.'],
            ['title' => 'Activities', 'description' => 'Engaging daily activities and events.'],
            ['title' => 'Dining & Nutrition', 'description' => 'Nutritious meals and dietary support.'],
            ['title' => 'Hospice Care', 'description' => 'Compassionate end-of-life care and support.'],
            ['title' => 'Long-term Care', 'description' => 'Extended stay and support for residents.'],
            ['title' => 'Memory Care', 'description' => 'Specialized support for memory impairment.'],
            ['title' => 'Physical Therapy', 'description' => 'Personalized rehabilitation and therapy.'],
            ['title' => 'Rehabilitation', 'description' => 'Comprehensive rehabilitation services for recovery.'],
        ];
        foreach ($services as $service) {
            Service::firstOrCreate(['title' => $service['title']], $service);
        }
    }
}
