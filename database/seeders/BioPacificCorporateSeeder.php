<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BioPacificCorporateSeeder extends Seeder
{
    public function run(): void
    {
        $slug = config('member-portal.corporate_facility_slug', 'bio-pacific-corporate');
        $globalId = (int) config('import-mapping.global_facility_id', 99);

        Facility::query()
            ->where(function ($query) {
                $query->where('slug', 'bio-pacific-corporation')
                    ->orWhere('facility_number', 'CORP001');
            })
            ->where('slug', '!=', $slug)
            ->update(['slug' => $slug]);

        Facility::query()->where('id', $globalId)->where('slug', '!=', $slug)->delete();

        $attributes = [
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
            'about_image_url' => 'about-people.jpg',
            'location_map' => 'https://maps.google.com/maps?q=123+Corporate+Drive,Los+Angeles,CA+90210&output=embed',
            'domain' => 'biopacificoperational.com',
            'subdomain' => 'corporate.biopacific.com',
            'years' => 25,
            'facility_image' => 'corporate-hero.jpg',
            'hours' => '8:00 AM - 5:00 PM',
            'hero_image_url' => 'hero17.jpg',
            'region' => 'socal',
            'facility_number' => 'CORP001',
            'legal_name' => 'Bio-Pacific Healthcare Corporation',
            'administrator' => 'President',
            'don' => 'Chief Nursing Officer',
            'dsd' => 'Chief Operating Officer',
            'staffer' => 'HR Director',
            'is_active' => true,
            'slug' => $slug,
            'about_text' => 'Bio-Pacific Corporation is dedicated to providing top-tier management services to our network of healthcare facilities across California. Our corporate team ensures operational excellence, regulatory compliance, and the highest standards of patient care.',
            'settings' => [
                'is_corporate_public_site' => true,
                'public_header_cta' => [
                    'label' => 'Login',
                    'route' => 'login',
                ],
            ],
        ];

        $facility = Facility::query()->where('slug', $slug)->first();

        if (!$facility && !Facility::query()->where('id', $globalId)->exists()) {
            $attributes['id'] = $globalId;
        }

        $facility = Facility::updateOrCreate(['slug' => $slug], $attributes);

        $sections = [
            'about', 'services', 'news', 'events', 'faqs', 'gallery', 'contact', 'careers', 'values', 'staff',
            'location', 'email', 'webcontent', 'social', 'admin', 'settings', 'privacy', 'security', 'hipaa', 'npp',
            'map', 'hours', 'headline', 'subheadline', 'hero', 'facility_image', 'color_scheme', 'legal',
            'administrator', 'don', 'dsd', 'staffer', 'region', 'facility_number', 'domain', 'subdomain',
            'address', 'city', 'state', 'zip', 'phone', 'about_image_url', 'hero_image_url',
        ];
        $excluded = ['book', 'testimonials', 'room'];

        $settings = $facility->settings ?? [];
        foreach ($sections as $section) {
            if (!in_array($section, $excluded, true)) {
                $settings[$section . '_active'] = true;
            }
        }
        $settings['is_corporate_public_site'] = true;
        $settings['public_header_cta'] = [
            'label' => 'Login',
            'route' => 'login',
        ];

        $facility->settings = $settings;
        $facility->save();

        DB::table('users')->whereNull('facility_id')->update(['facility_id' => $facility->id]);

        $this->command?->info("Bio-Pacific Corporate facility seeded (slug: {$slug}, id: {$facility->id})");
    }
}
