<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use App\Models\WebContent;
use Illuminate\Support\Facades\Log;

class WebContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSections = [
            'topbar', 'hero', 'about', 'services', 'rooms', 'gallery', 'news',
            'testimonials', 'careers', 'book', 'contact', 'faqs', 'resources', 'footer'
        ];
        $defaultVariances = array_fill(0, count($defaultSections), 'default');

        // Example customizations for each facility
        $facilityCustomizations = [
            1 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero6', 'default', 'services2', 'default', 'default', 'default', 'default', 'book1', 'contact1', 'faqs1', 'default'],
            ],
            2 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero2', 'about1', 'default', 'default', 'default', 'testimonials1', 'default', 'book2', 'contact1', 'faqs1', 'default'],
            ],
            3 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero3', 'about2', 'services3', 'default', 'default', 'testimonials2', 'default', 'book2', 'default', 'default', 'default'],
            ],
            4 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero5', 'default', 'services4', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            5 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero4', 'about3', 'services5', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            6 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero1', 'default', 'services6', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            7 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero6', 'about4', 'services7', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            8 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'default', 'about5', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            9 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero4', 'default', 'services2', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            10 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero2', 'about6', 'services3', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            11 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero3', 'about7', 'services4', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            12 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero4', 'default', 'services5', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            13 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero5', 'about2', 'services7', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            14 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero6', 'about3', 'services6', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            15 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'default', 'about4', 'services5', 'default', 'default', 'default', 'default', 'default', 'default', 'default', 'default'],
            ],
            16 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero1', 'about5', 'services2', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            17 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero2', 'about6', 'services3', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            18 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'testimonials', 'careers', 'book', 'contact', 'faqs', 'footer'],
                'variances' => ['default', 'hero3', 'about7', 'services4', 'default', 'default', 'default', 'default', 'default', 'default', 'default',  'default'],
            ],
            99 => [
                'sections' => ['topbar', 'hero', 'about', 'services', 'gallery', 'news', 'careers',  'contact', 'faqs', 'footer'],
                'variances' => ['default', 'default', 'default', 'default', 'default', 'default',  'default',  'default', 'default', 'default'],
            ],

        ];

        foreach (Facility::all() as $facility) {
            $custom = $facilityCustomizations[$facility->id] ?? null;
            $sections = $custom['sections'] ?? $defaultSections;
            $variances = $custom['variances'] ?? $defaultVariances;

            // Log the values for debugging
            Log::debug('Facility ID: ' . $facility->id, [
                'sections' => $sections,
                'variances' => $variances,
            ]);

            // Ensure $sections and $variances have the same number of elements
            if (count($sections) !== count($variances)) {
                throw new \Exception("Mismatch in sections and variances count for facility ID: {$facility->id}");
            }

            WebContent::updateOrCreate(
                [
                    'facility_id' => $facility->id,
                    'layout_template' => $facility->layout_template ?? 'default-template',
                ],
                [
                    'sections' => $sections,
                    'variances' => array_combine($sections, $variances),
                    'is_active' => true,
                ]
            );
        }
    }
}
