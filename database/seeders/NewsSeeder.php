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
                'is_global' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mental Health Awareness Talk',
                'content' => 'Join our guest speaker for insights and resources on mental wellness.',
                'published_at' => now()->subDays(10),
                'status' => true,
                'is_global' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Flu Shot Clinic',
                'content' => 'On-site vaccinations for residents & staff by certified healthcare professionals.',
                'published_at' => now()->subDays(5),
                'status' => true,
                'is_global' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Family BBQ Day',
                'content' => 'Join us for food, music, and facility tours in a festive atmosphere.',
                'published_at' => now()->addDays(7),
                'status' => true,
                'is_global' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Thanksgiving Celebration',
                'content' => 'Special Thanksgiving dinner with family-style dining and entertainment.',
                'published_at' => now()->addDays(50),
                'status' => true,
                'is_global' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Create each example news only once, then attach to random facilities
        $newsRecords = [];
        foreach ($examples as $newsData) {
            // Use title as unique key
            $news = News::firstOrCreate([
                'title' => $newsData['title'],
            ], $newsData);
            $newsRecords[] = $news;
        }

        // Attach each news to random facilities
        foreach ($newsRecords as $news) {
            // Pick random facilities for this news
            $randomFacilities = $facilities->random(min(rand(2, 4), $facilities->count()));
            foreach ($randomFacilities as $facility) {
                $facility->news()->syncWithoutDetaching([$news->id]);
            }
        }

        // Add a global news example
        News::create([
            'title' => 'Company Holiday Closure',
            'content' => 'All facilities will be closed on December 25th for the holidays.',
            'published_at' => now()->addMonths(2),
            'status' => true,
            'is_global' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
