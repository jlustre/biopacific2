<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use App\Models\WebContent;

class WebContentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allSections = [
            'topbar',
            'hero',
            'about',
            'services',
            'rooms',
            'gallery',
            'news',
            'testimonials',
            'careers',
            'contact',
            'faqs',
            'resources',
            'footer'
        ];

        $allVariances = [
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default',
            'default'
        ];

        foreach (Facility::all() as $facility) {
            WebContent::updateOrCreate(
                [
                    'facility_id' => $facility->id,
                    'layout_template' => 'default-template',
                ],
                [
                    'sections' => $allSections,
                    'variances' => $allVariances,
                    'is_active' => true,
                ]
            );
        }
    }
}
