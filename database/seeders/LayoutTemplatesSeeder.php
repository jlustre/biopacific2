<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LayoutTemplate;
use App\Models\LayoutSection;

class LayoutTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create layout sections first
        $sections = [
            [
                'name' => 'Hero Section',
                'slug' => 'hero',
                'description' => 'Main hero section with headline and call-to-action',
                'variants' => [
                    [
                        'name' => 'default',
                        'title' => 'Classic Hero',
                        'description' => 'Traditional hero with background image and centered text',
                        'component_path' => 'partials.hero.default'
                    ],
                    [
                        'name' => 'video',
                        'title' => 'Video Hero',
                        'description' => 'Hero with video background',
                        'component_path' => 'partials.hero.video'
                    ],
                    [
                        'name' => 'split',
                        'title' => 'Split Hero',
                        'description' => 'Hero with text on one side and image on the other',
                        'component_path' => 'partials.hero.split'
                    ]
                ],
                'config_schema' => [
                    'background_type' => ['type' => 'select', 'options' => ['image', 'video', 'gradient']],
                    'text_alignment' => ['type' => 'select', 'options' => ['left', 'center', 'right']],
                    'show_cta' => ['type' => 'boolean', 'default' => true]
                ],
                'component_path' => 'partials.hero.default'
            ],
            [
                'name' => 'About Section',
                'slug' => 'about',
                'description' => 'About us section',
                'variants' => [
                    [
                        'name' => 'default',
                        'title' => 'Standard About',
                        'description' => 'Traditional about section with text and image',
                        'component_path' => 'partials.about.default'
                    ],
                    [
                        'name' => 'stats',
                        'title' => 'About with Stats',
                        'description' => 'About section with statistics',
                        'component_path' => 'partials.about.stats'
                    ],
                    [
                        'name' => 'timeline',
                        'title' => 'About with Timeline',
                        'description' => 'About section with timeline/history',
                        'component_path' => 'partials.about.timeline'
                    ]
                ],
                'config_schema' => [
                    'layout' => ['type' => 'select', 'options' => ['image-left', 'image-right', 'centered']],
                    'show_stats' => ['type' => 'boolean', 'default' => false]
                ],
                'component_path' => 'partials.about.default'
            ],
            [
                'name' => 'Services Section',
                'slug' => 'services',
                'description' => 'Services and amenities section',
                'variants' => [
                    [
                        'name' => 'grid',
                        'title' => 'Grid Layout',
                        'description' => 'Services in a grid layout',
                        'component_path' => 'partials.services.grid'
                    ],
                    [
                        'name' => 'cards',
                        'title' => 'Card Layout',
                        'description' => 'Services in card format',
                        'component_path' => 'partials.services.cards'
                    ],
                    [
                        'name' => 'tabs',
                        'title' => 'Tabbed Layout',
                        'description' => 'Services organized in tabs',
                        'component_path' => 'partials.services.tabs'
                    ]
                ],
                'config_schema' => [
                    'columns' => ['type' => 'select', 'options' => [2, 3, 4]],
                    'show_icons' => ['type' => 'boolean', 'default' => true]
                ],
                'component_path' => 'partials.services.grid'
            ],
            [
                'name' => 'Contact Section',
                'slug' => 'contact',
                'description' => 'Contact information and form',
                'variants' => [
                    [
                        'name' => 'form',
                        'title' => 'Contact Form',
                        'description' => 'Contact section with form',
                        'component_path' => 'partials.contact.form'
                    ],
                    [
                        'name' => 'info',
                        'title' => 'Contact Info',
                        'description' => 'Contact information only',
                        'component_path' => 'partials.contact.info'
                    ],
                    [
                        'name' => 'map',
                        'title' => 'Contact with Map',
                        'description' => 'Contact section with embedded map',
                        'component_path' => 'partials.contact.map'
                    ]
                ],
                'config_schema' => [
                    'show_form' => ['type' => 'boolean', 'default' => true],
                    'show_map' => ['type' => 'boolean', 'default' => false]
                ],
                'component_path' => 'partials.contact.form'
            ]
        ];

        foreach ($sections as $sectionData) {
            LayoutSection::create($sectionData);
        }

        // Create layout templates
        $templates = [
            [
                'name' => 'Classic Layout',
                'slug' => 'layout1',
                'description' => 'Traditional layout with all sections',
                'sections' => ['hero', 'about', 'services', 'contact'],
                'default_config' => [
                    'hero' => ['variant' => 'default', 'background_type' => 'image'],
                    'about' => ['variant' => 'default', 'layout' => 'image-left'],
                    'services' => ['variant' => 'grid', 'columns' => 3],
                    'contact' => ['variant' => 'form', 'show_form' => true]
                ]
            ],
            [
                'name' => 'Modern Layout',
                'slug' => 'layout2',
                'description' => 'Modern layout with video hero and card services',
                'sections' => ['hero', 'about', 'services', 'contact'],
                'default_config' => [
                    'hero' => ['variant' => 'video', 'background_type' => 'video'],
                    'about' => ['variant' => 'stats', 'layout' => 'centered'],
                    'services' => ['variant' => 'cards', 'columns' => 3],
                    'contact' => ['variant' => 'map', 'show_map' => true]
                ]
            ],
            [
                'name' => 'Minimal Layout',
                'slug' => 'layout3',
                'description' => 'Minimal layout focused on content',
                'sections' => ['hero', 'about', 'contact'],
                'default_config' => [
                    'hero' => ['variant' => 'split', 'text_alignment' => 'left'],
                    'about' => ['variant' => 'timeline', 'layout' => 'centered'],
                    'contact' => ['variant' => 'info', 'show_form' => false]
                ]
            ],
            [
                'name' => 'Service-Focused Layout',
                'slug' => 'layout4',
                'description' => 'Layout that emphasizes services',
                'sections' => ['hero', 'services', 'about', 'contact'],
                'default_config' => [
                    'hero' => ['variant' => 'default', 'background_type' => 'gradient'],
                    'services' => ['variant' => 'tabs', 'show_icons' => true],
                    'about' => ['variant' => 'default', 'layout' => 'image-right'],
                    'contact' => ['variant' => 'form', 'show_form' => true]
                ]
            ]
        ];

        foreach ($templates as $templateData) {
            LayoutTemplate::create($templateData);
        }
    }
}
