<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Facility;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run()
    {
        $facilities = Facility::all();
        $examples = [
            [
                'title' => 'Fall Prevention Workshop',
                'content' => 'Learn safety techniques for residents and families with expert guidance.',
                'published_at' => now()->subDays(20),
                'status' => true,
                'scope' => 'facility',
            ],
            [
                'title' => 'Mental Health Awareness Talk',
                'content' => 'Join our guest speaker for insights and resources on mental wellness.',
                'published_at' => now()->subDays(10),
                'status' => true,
                'scope' => 'facility',
            ],
            [
                'title' => 'Flu Shot Clinic',
                'content' => 'On-site vaccinations for residents & staff by certified healthcare professionals.',
                'published_at' => now()->subDays(5),
                'status' => true,
                'scope' => 'facility',
            ],
            [
                'title' => 'Family BBQ Day',
                'content' => 'Join us for food, music, and facility tours in a festive atmosphere.',
                'published_at' => now()->addDays(7),
                'status' => true,
                'scope' => 'facility',
            ],
            [
                'title' => 'Thanksgiving Celebration',
                'content' => 'Special Thanksgiving dinner with family-style dining and entertainment.',
                'published_at' => now()->addDays(50),
                'status' => true,
                'scope' => 'facility',
            ],
        ];

            foreach ($examples as $data) {
                $news = News::create($data);
            }

        // Add a company-wide example
        News::create([
            'title' => 'Company Holiday Closure',
            'content' => 'All facilities will be closed on December 25th for the holidays.',
            'published_at' => now()->addMonths(2),
            'status' => true,
            'scope' => 'company',
        ]);
    }
}
