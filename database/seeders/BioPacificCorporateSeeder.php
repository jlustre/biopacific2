<?php

namespace Database\Seeders;

use App\Models\Facility;
use Database\Seeders\Support\FacilitiesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BioPacificCorporateSeeder extends Seeder
{
  public function run(): void
  {
    $slug = FacilitiesSeedData::corporateSlug();
    $globalId = (int) config('import-mapping.global_facility_id', 99);
    $seed = FacilitiesSeedData::corporate();

    Facility::query()
      ->where(function ($query) {
        $query->where('slug', 'bio-pacific-corporation')
          ->orWhere('facility_number', 'CORP001');
      })
      ->where('slug', '!=', $slug)
      ->update(['slug' => $slug]);

    Facility::query()->where('id', $globalId)->where('slug', '!=', $slug)->delete();

    $attributes = $this->buildAttributes($slug, $seed);
    $facility = Facility::query()->where('slug', $slug)->first();

    if (! $facility && ! Facility::query()->where('id', $globalId)->exists()) {
      $attributes['id'] = $globalId;
    }

    $facility = Facility::updateOrCreate(['slug' => $slug], $attributes);

    if ($seed) {
      $facility->forceFill([
        'latitude' => $seed['latitude'] ?? 34.052235,
        'longitude' => $seed['longitude'] ?? -118.243683,
        'meta_description' => $seed['meta_description'] ?? null,
      ])->save();
    }

    $settings = $facility->settings ?? [];
    $settings['is_corporate_public_site'] = true;
    $settings['public_header_cta'] = $settings['public_header_cta'] ?? [
      'label' => 'Login',
      'route' => 'login',
    ];

    $facility->settings = $settings;
    $facility->save();

    DB::table('users')->whereNull('facility_id')->update(['facility_id' => $facility->id]);

    $this->command?->info("Bio-Pacific Corporate facility seeded (slug: {$slug}, id: {$facility->id})");
  }

  /**
   * @param  array<string, mixed>|null  $seed
   * @return array<string, mixed>
   */
  private function buildAttributes(string $slug, ?array $seed): array
  {
    if ($seed) {
      return array_filter([
        'name' => $seed['name'] ?? 'Bio-Pacific Corporation',
        'tagline' => $seed['tagline'] ?? null,
        'headline' => $seed['headline'] ?? null,
        'subheadline' => $seed['subheadline'] ?? null,
        'address' => $seed['address'] ?? null,
        'phone' => $seed['phone'] ?? null,
        'city' => $seed['city'] ?? null,
        'state' => $seed['state'] ?? null,
        'zip' => $seed['zip'] ?? null,
        'latitude' => $seed['latitude'] ?? null,
        'longitude' => $seed['longitude'] ?? null,
        'beds' => $seed['beds'] ?? 0,
        'color_scheme_id' => $seed['color_scheme_id'] ?? 33,
        'logo_url' => $seed['logo_url'] ?? 'bplogo.png',
        'about_image_url' => $seed['about_image_url'] ?? 'about-people.jpg',
        'location_map' => $seed['location_map'] ?? null,
        'domain' => $seed['domain'] ?? 'biopacificoperational.com',
        'subdomain' => $seed['subdomain'] ?? 'corporate.biopacific.com',
        'years' => $seed['years'] ?? 25,
        'facility_image' => $seed['facility_image'] ?? 'corporate-hero.jpg',
        'hours' => $seed['hours'] ?? '8:00 AM - 5:00 PM',
        'hero_image_url' => $seed['hero_image_url'] ?? 'hero17.jpg',
        'region' => $seed['region'] ?? 'socal',
        'facility_number' => $seed['facility_number'] ?? 'CORP001',
        'legal_name' => $seed['legal_name'] ?? 'Bio-Pacific Healthcare Corporation',
        'administrator' => $seed['administrator'] ?? 'President',
        'don' => $seed['don'] ?? 'Chief Nursing Officer',
        'dsd' => $seed['dsd'] ?? 'Chief Operating Officer',
        'staffer' => $seed['staffer'] ?? 'HR Director',
        'is_active' => $seed['is_active'] ?? true,
        'slug' => $slug,
        'about_text' => $seed['about_text'] ?? null,
        'settings' => $seed['settings'] ?? [
          'is_corporate_public_site' => true,
          'public_header_cta' => [
            'label' => 'Login',
            'route' => 'login',
          ],
        ],
        'layout_template' => $seed['layout_template'] ?? 'default-template',
        'email' => $seed['email'] ?? null,
        'hipaa_flags' => $seed['hipaa_flags'] ?? null,
        'npp_url' => $seed['npp_url'] ?? null,
      ], fn ($value) => $value !== null);
    }

    return [
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
      'layout_template' => 'default-template',
    ];
  }
}
