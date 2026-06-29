<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityValue;
use Database\Seeders\Support\FacilitiesSeedData;
use Illuminate\Database\Seeder;

// Last exported: 2026-06-29 09:55:47
class FacilitySeeder extends Seeder
{
  public function run(): void
  {
    $items = FacilitiesSeedData::nonCorporate();

    if ($items === []) {
      $this->command?->warn('No facilities found in database/seeders/data/facilities.json.');

      return;
    }

    foreach ($items as $item) {
      $this->seedFacility($item);
    }

    $this->command?->info('Seeded ' . count($items) . ' facilities from facilities.json.');
  }

  /**
   * @param  array<string, mixed>  $item
   */
  private function seedFacility(array $item): void
  {
    $slug = (string) ($item['slug'] ?? '');
    $name = (string) ($item['name'] ?? '');
    $city = (string) ($item['city'] ?? '');

    $metaDescription = $item['meta_description'] ?? str_replace(
      ['{facilityName}', '{city}'],
      [$name, $city],
      '{facilityName} in {city} offers top-rated skilled nursing, rehabilitation therapy, long-term senior care, and memory care services. Our experienced clinical team provides personalized support, post-acute recovery, respite care, and 24/7 nursing in a safe, compassionate, and home-like environment—trusted by families seeking quality senior care and healthcare services.'
    );

    $attributes = array_filter([
      'name' => $name,
      'slug' => $slug,
      'tagline' => $item['tagline'] ?? null,
      'logo_url' => $item['logo_url'] ?? 'bplogo.png',
      'hero_image_url' => $item['hero_image_url'] ?? 'default-hero.jpg',
      'facility_image' => $item['facility_image'] ?? 'default-facility.jpg',
      'headline' => $item['headline'] ?? null,
      'subheadline' => $item['subheadline'] ?? null,
      'hero_video_id' => $item['hero_video_id'] ?? null,
      'about_image_url' => $item['about_image_url'] ?? 'about-people.jpg',
      'about_text' => $item['about_text'] ?? ($name . ' provides personalized care and support for seniors, ensuring comfort, dignity, and quality of life.'),
      'address' => $item['address'] ?? null,
      'city' => $item['city'] ?? null,
      'state' => $item['state'] ?? null,
      'zip' => $item['zip'] ?? null,
      'beds' => $item['beds'] ?? null,
      'hours' => $item['hours'] ?? null,
      'domain' => $item['domain'] ?? null,
      'subdomain' => $item['subdomain'] ?? null,
      'phone' => $item['phone'] ?? null,
      'email' => $item['email'] ?? (isset($item['domain']) ? 'info@' . $item['domain'] : null),
      'facebook' => $item['facebook'] ?? 'https://facebook.com',
      'twitter' => $item['twitter'] ?? 'https://twitter.com',
      'instagram' => $item['instagram'] ?? 'https://instagram.com',
      'location_map' => $item['location_map'] ?? null,
      'years' => $item['years'] ?? null,
      'is_active' => $item['is_active'] ?? true,
      'facility_number' => $item['facility_number'] ?? null,
      'legal_name' => $item['legal_name'] ?? null,
      'administrator' => $item['administrator'] ?? null,
      'don' => $item['don'] ?? null,
      'dsd' => $item['dsd'] ?? null,
      'staffer' => $item['staffer'] ?? null,
      'region' => $item['region'] ?? null,
      'color_scheme_id' => $item['color_scheme_id'] ?? 1,
      'settings' => $item['settings'] ?? null,
      'layout_template' => $item['layout_template'] ?? 'default-template',
      'hipaa_flags' => $item['hipaa_flags'] ?? [
        'npp_page' => true,
        'tls_hsts' => true,
        'baa_vendors' => true,
        'forms_secure' => true,
        'incident_plan' => true,
        'security_headers' => true,
        'tracking_controls' => true,
      ],
      'npp_url' => $item['npp_url'] ?? null,
    ], fn ($value) => $value !== null);

    $facility = Facility::updateOrCreate(['slug' => $slug], $attributes);

    $facility->forceFill([
      'latitude' => $item['latitude'] ?? null,
      'longitude' => $item['longitude'] ?? null,
      'meta_description' => $metaDescription,
    ])->save();

    $values = $item['values'] ?? [];
    if (! is_array($values) || $values === []) {
      $values = ['Compassion', 'Integrity', 'Respect', 'Excellence'];
    }

    foreach ($values as $value) {
      if (! is_string($value) || $value === '') {
        continue;
      }

      FacilityValue::firstOrCreate([
        'facility_id' => $facility->id,
        'value' => $value,
      ]);
    }
  }
}
