<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Facility;

class FacilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Vale Health Care Center',
                'slug' => 'vale-health',
                'domain' => 'valehealthcare.com',
                'subdomain' => 'vale',
                'layout_template' => 'layout1',
                'address' => '123 Care Lane',
                'city' => 'Springfield',
                'state' => 'IL',
                'phone' => '(555) 123-4567',
                'email' => 'info@valehealthcare.com',
                'headline' => 'Compassionate Care, Every Day',
                'subheadline' => 'Providing exceptional healthcare services with dignity and respect',
                'about_text' => 'Vale Health Care Center has been serving our community for over 25 years...',
                'beds' => 120,
                'primary_color' => '#047857',
                'secondary_color' => '#1f2937',
                'accent_color' => '#06b6d4',
                'is_active' => true
            ],
            [
                'name' => 'Pacific Manor Care',
                'slug' => 'pacific-manor',
                'domain' => 'pacificmanorcare.com',
                'subdomain' => 'pacific',
                'layout_template' => 'layout2',
                'address' => '456 Pacific Ave',
                'city' => 'San Diego',
                'state' => 'CA',
                'phone' => '(555) 234-5678',
                'email' => 'info@pacificmanorcare.com',
                'headline' => 'Ocean-Side Living with Premium Care',
                'subheadline' => 'Modern facilities with breathtaking ocean views',
                'about_text' => 'Pacific Manor Care offers luxury senior living by the ocean...',
                'beds' => 95,
                'primary_color' => '#0ea5e9',
                'secondary_color' => '#0f172a',
                'accent_color' => '#f59e0b',
                'is_active' => true
            ],
            [
                'name' => 'Sunrise Gardens Healthcare',
                'slug' => 'sunrise-gardens',
                'domain' => 'sunrisegardens.com',
                'subdomain' => 'sunrise',
                'layout_template' => 'layout3',
                'address' => '789 Garden Way',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'phone' => '(555) 345-6789',
                'email' => 'info@sunrisegardens.com',
                'headline' => 'Where Every Sunrise Brings New Hope',
                'subheadline' => 'Dedicated to enriching lives through compassionate care',
                'about_text' => 'Sunrise Gardens Healthcare specializes in memory care...',
                'beds' => 85,
                'primary_color' => '#f59e0b',
                'secondary_color' => '#7c2d12',
                'accent_color' => '#10b981',
                'is_active' => true
            ],
            [
                'name' => 'Mountain View Assisted Living',
                'slug' => 'mountain-view',
                'domain' => 'mountainviewal.com',
                'subdomain' => 'mountain',
                'layout_template' => 'layout4',
                'address' => '321 Mountain Rd',
                'city' => 'Denver',
                'state' => 'CO',
                'phone' => '(555) 456-7890',
                'email' => 'info@mountainviewal.com',
                'headline' => 'Elevated Care in the Heart of the Rockies',
                'subheadline' => 'Independent living with stunning mountain views',
                'about_text' => 'Mountain View Assisted Living provides independent seniors...',
                'beds' => 150,
                'primary_color' => '#7c3aed',
                'secondary_color' => '#374151',
                'accent_color' => '#ec4899',
                'is_active' => true
            ],
            [
                'name' => 'Oakwood Senior Community',
                'slug' => 'oakwood',
                'domain' => 'oakwoodsenior.com',
                'subdomain' => 'oakwood',
                'layout_template' => 'layout1',
                'address' => '555 Oak Street',
                'city' => 'Portland',
                'state' => 'OR',
                'phone' => '(555) 567-8901',
                'email' => 'info@oakwoodsenior.com',
                'headline' => 'Rooted in Community, Growing in Care',
                'subheadline' => 'A place where relationships flourish and memories are made',
                'about_text' => 'Oakwood Senior Community has been a cornerstone...',
                'beds' => 110,
                'primary_color' => '#059669',
                'secondary_color' => '#1f2937',
                'accent_color' => '#f97316',
                'is_active' => true
            ]
        ];

        foreach ($facilities as $facilityData) {
            Facility::create($facilityData);
        }
    }
}
