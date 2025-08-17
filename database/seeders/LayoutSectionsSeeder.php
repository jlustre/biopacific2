<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LayoutSection;

class LayoutSectionsSeeder extends Seeder
{
    public function run()
    {
        $sections = [
            // Hero Sections
            [
                'name' => 'Hero Section',
                'slug' => 'hero',
                'description' => 'Main banner section with hero content',
                'variants' => [
                    'default' => 'Traditional hero with background image and centered text',
                    'split' => 'Hero with text on one side and image on the other'
                ],
                'config_schema' => [
                    'variant' => ['type' => 'select', 'options' => ['default', 'split'], 'default' => 'default', 'label' => 'Hero Style'],
                    'show_cta' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Call-to-Action Buttons'],
                    'background_type' => ['type' => 'select', 'options' => ['image', 'gradient'], 'default' => 'image', 'label' => 'Background Type'],
                    'text_alignment' => ['type' => 'select', 'options' => ['left', 'center', 'right'], 'default' => 'center', 'label' => 'Text Alignment'],
                ],
                'component_path' => 'partials.hero',
                'is_active' => true,
            ],

            // About Sections
            [
                'name' => 'About Section',
                'slug' => 'about',
                'description' => 'About us section with facility information',
                'variants' => [
                    'default' => 'Standard about section with text and image',
                    'default2' => 'Alternative about section layout with different styling'
                ],
                'config_schema' => [
                    'variant' => ['type' => 'select', 'options' => ['default', 'default2'], 'default' => 'default', 'label' => 'About Style'],
                    'image_position' => ['type' => 'select', 'options' => ['left', 'right'], 'default' => 'right', 'label' => 'Image Position'],
                    'show_stats' => ['type' => 'boolean', 'default' => false, 'label' => 'Show Statistics'],
                    'layout' => ['type' => 'select', 'options' => ['standard', 'centered'], 'default' => 'standard', 'label' => 'Layout Style'],
                ],
                'component_path' => 'partials.about',
                'is_active' => true,
            ],

            // Services Sections
            [
                'name' => 'Services Section',
                'slug' => 'services',
                'description' => 'Services and amenities showcase',
                'variants' => [
                    'default' => 'Standard services section with detailed cards',
                    'grid' => 'Services displayed in a responsive grid format'
                ],
                'config_schema' => [
                    'variant' => ['type' => 'select', 'options' => ['default', 'grid'], 'default' => 'default', 'label' => 'Services Style'],
                    'columns' => ['type' => 'select', 'options' => [2, 3, 4], 'default' => 3, 'label' => 'Number of Columns'],
                    'show_icons' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Service Icons'],
                    'show_details' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Detailed Descriptions'],
                ],
                'component_path' => 'partials.services',
                'is_active' => true,
            ],

            // Contact Sections
            [
                'name' => 'Contact Section',
                'slug' => 'contact',
                'description' => 'Contact information and inquiry form',
                'variants' => [
                    'form' => 'Contact section with inquiry form',
                    'info' => 'Contact information display only'
                ],
                'config_schema' => [
                    'variant' => ['type' => 'select', 'options' => ['form', 'info'], 'default' => 'form', 'label' => 'Contact Style'],
                    'show_map' => ['type' => 'boolean', 'default' => false, 'label' => 'Show Map'],
                    'form_position' => ['type' => 'select', 'options' => ['left', 'right'], 'default' => 'right', 'label' => 'Form Position'],
                    'show_contact_info' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Contact Information'],
                ],
                'component_path' => 'partials.contact',
                'is_active' => true,
            ],

            // Testimonials Section
            [
                'name' => 'Testimonials Section',
                'slug' => 'testimonials',
                'description' => 'Customer testimonials and reviews',
                'variants' => [
                    'default' => 'Standard testimonials display',
                    'carousel' => 'Testimonials in carousel format'
                ],
                'config_schema' => [
                    'variant' => ['type' => 'select', 'options' => ['default', 'carousel'], 'default' => 'default', 'label' => 'Testimonials Style'],
                    'columns' => ['type' => 'select', 'options' => [1, 2, 3], 'default' => 2, 'label' => 'Number of Columns'],
                    'show_photos' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Customer Photos'],
                    'auto_rotate' => ['type' => 'boolean', 'default' => false, 'label' => 'Auto-rotate Testimonials'],
                ],
                'component_path' => 'partials.testimonials',
                'is_active' => true,
            ],

            // Footer Section
            [
                'name' => 'Footer Section',
                'slug' => 'footer',
                'description' => 'Website footer with links and information',
                'variants' => [
                    'default' => 'Standard website footer',
                    'minimal' => 'Minimal footer layout'
                ],
                'config_schema' => [
                    'variant' => ['type' => 'select', 'options' => ['default', 'minimal'], 'default' => 'default', 'label' => 'Footer Style'],
                    'show_social_links' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Social Media Links'],
                    'show_quick_links' => ['type' => 'boolean', 'default' => true, 'label' => 'Show Quick Links'],
                    'layout' => ['type' => 'select', 'options' => ['simple', 'detailed'], 'default' => 'detailed', 'label' => 'Footer Layout'],
                ],
                'component_path' => 'partials.footer',
                'is_active' => true,
            ],
        ];

        foreach ($sections as $sectionData) {
            LayoutSection::updateOrCreate(
                ['slug' => $sectionData['slug']],
                $sectionData
            );
        }

        $this->command->info('Layout sections seeded successfully!');
    }
}
