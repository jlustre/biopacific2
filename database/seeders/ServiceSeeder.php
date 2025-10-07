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
            ['title' => 'Physical Therapy', 'description' => 'Personalized rehabilitation and therapy.'],
            ['title' => 'Memory Care', 'description' => 'Specialized support for memory impairment.'],
            ['title' => 'Long-term Care', 'description' => 'Extended stay and support for residents.'],
            ['title' => 'Dining & Nutrition', 'description' => 'Nutritious meals and dietary support.'],
            ['title' => 'Activities', 'description' => 'Engaging daily activities and events.'],
        ];
        foreach ($services as $service) {
            Service::firstOrCreate(['title' => $service['title']], $service);
        }
    }
}
