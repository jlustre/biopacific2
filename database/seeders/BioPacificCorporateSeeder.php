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
        \App\Models\Facility::create([
            'id' => 99,
            'name' => 'Bio-Pacific Corporate',
            'tagline' => 'Excellence in Healthcare Management',
            'headline' => 'Leading Healthcare Excellence Across California',
            'subheadline' => 'Corporate headquarters overseeing quality care and operations.',
            'address' => '123 Corporate Drive',
            'phone' => '8005551234',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90210',
            'beds' => 0,
            'color_scheme_id' => 1,
            'about_image_url' => 'corporate-office.jpg',
            'location_map' => 'https://maps.google.com/maps?q=123+Corporate+Drive,Los+Angeles,CA+90210&output=embed',
            'domain' => 'biopacific.com',
            'subdomain' => 'corporate.biopacific.com',
            'years' => 25,
            'facility_image' => 'biopacific-corporate.jpg',
            'hours' => '8:00 AM - 5:00 PM',
            'hero_image_url' => 'corporate-hero.jpg',
            'region' => 'socal',
            'facility_number' => 'CORP001',
            'legal_name' => 'Bio-Pacific Healthcare Corporation',
            'administrator' => 'Chief Executive Officer',
            'don' => 'Chief Nursing Officer',
            'dsd' => 'Chief Operating Officer',
            'staffer' => 'HR Director',
            'is_active' => true,
            'slug' => 'bio-pacific-corporate',
        ]);

        $this->command->info('Bio-Pacific Corporate facility created with ID 99');

        // Update all existing users without a facility_id to use Bio-Pacific Corporate
        DB::table('users')->whereNull('facility_id')->update(['facility_id' => 99]);
        $this->command->info('Updated existing users to use Bio-Pacific Corporate facility');
    }
}
