<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;
use App\Models\ColorScheme;

class BioPacificCorporateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing corporate facility if it exists
        \App\Models\Facility::where('id', 99)->delete();

    // Create Bio-Pacific Corporate facility with ID 99
    $facility = \App\Models\Facility::create([
            'id' => 99,
            'name' => 'Bio-Pacific Corporation',
            'tagline' => 'Excellence in Healthcare Management',
            'headline' => 'Leading Healthcare Excellence Across California',
            'subheadline' => 'Corporate headquarters overseeing quality care and operations.',
            'address' => '123 Corporate Drive',
            'phone' => '8005551234',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90210',
            'latitude' => 34.052235,
            'longitude' => -118.243683,
            'beds' => 0,
            'color_scheme_id' => 33,
            'logo_url' => 'bplogo.png',
            'about_image_url' => 'about-people.png',
            'location_map' => 'https://maps.google.com/maps?q=123+Corporate+Drive,Los+Angeles,CA+90210&output=embed',
            'domain' => 'biopacific.com',
            'subdomain' => 'corporate.biopacific.com',
            'years' => 25,
            'facility_image' => 'corporate-hero.jpg',
            'hours' => '8:00 AM - 5:00 PM',
            'hero_image_url' => 'hero17.png',
            'region' => 'socal',
            'facility_number' => 'CORP001',
            'legal_name' => 'Bio-Pacific Healthcare Corporation',
            'administrator' => 'Chief Executive Officer',
            'don' => 'Chief Nursing Officer',
            'dsd' => 'Chief Operating Officer',
            'staffer' => 'HR Director',
            'is_active' => false,
            'slug' => 'bio-pacific-corporate',
            'about_text' => 'Bio-Pacific Corporation is dedicated to providing top-tier management services to our network of healthcare facilities across California. Our corporate team ensures operational excellence, regulatory compliance, and the highest standards of patient care.',
        ]);

        // Activate all sections except book, testimonials, room
        $sections = [
            'about', 'services', 'news', 'events', 'faqs', 'gallery', 'contact', 'careers', 'values', 'staff', 'location', 'email', 'webcontent', 'social', 'admin', 'settings', 'privacy', 'security', 'hipaa', 'npp', 'map', 'hours', 'headline', 'subheadline', 'hero', 'facility_image', 'color_scheme', 'legal', 'administrator', 'don', 'dsd', 'staffer', 'region', 'facility_number', 'domain', 'subdomain', 'address', 'city', 'state', 'zip', 'phone', 'about_image_url', 'hero_image_url'
        ];
        $excluded = ['book', 'testimonials', 'room'];
        foreach ($sections as $section) {
            if (!in_array($section, $excluded)) {
                // Example: set a settings array or a DB table for section activation
                // Here, we use a settings array on the facility model
                $settings = $facility->settings ?? [];
                $settings[$section . '_active'] = true;
                $facility->settings = $settings;
                $facility->save();
            }
        }

        $this->command->info('Bio-Pacific Corporate facility created with ID 99');

        // Update all existing users without a facility_id to use Bio-Pacific Corporate
        DB::table('users')->whereNull('facility_id')->update(['facility_id' => 99]);
        $this->command->info('Updated existing users to use Bio-Pacific Corporate facility');
    }
}
