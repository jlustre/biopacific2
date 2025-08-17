<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LayoutTemplate;

class SimpleTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'Classic Layout',
                'slug' => 'classic',
                'description' => 'Traditional layout with all sections',
                'sections' => ['hero', 'about', 'services', 'contact'],
                'default_config' => [
                    'hero' => ['variant' => 'default'],
                    'about' => ['variant' => 'default'],
                    'services' => ['variant' => 'default'],
                    'contact' => ['variant' => 'form']
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Modern Layout',
                'slug' => 'modern',
                'description' => 'Modern layout with split hero',
                'sections' => ['hero', 'about', 'services', 'testimonials', 'contact'],
                'default_config' => [
                    'hero' => ['variant' => 'split'],
                    'about' => ['variant' => 'default'],
                    'services' => ['variant' => 'grid'],
                    'testimonials' => ['variant' => 'default'],
                    'contact' => ['variant' => 'form']
                ],
                'is_active' => true,
            ]
        ];

        foreach ($templates as $template) {
            LayoutTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        $this->command->info('Layout templates created successfully!');
    }
}
