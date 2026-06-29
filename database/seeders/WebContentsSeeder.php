<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\WebContent;
use Database\Seeders\Support\FacilitiesSeedData;
use Illuminate\Database\Seeder;

class WebContentsSeeder extends Seeder
{
  public function run(): void
  {
    $defaultSections = [
      'topbar', 'hero', 'about', 'services', 'rooms', 'gallery', 'news',
      'testimonials', 'careers', 'book', 'contact', 'faqs', 'resources', 'footer',
    ];

    foreach (Facility::all() as $facility) {
      $seed = FacilitiesSeedData::findBySlug((string) $facility->slug);

      $sections = $seed['sections'] ?? $defaultSections;
      if (! is_array($sections) || $sections === []) {
        $sections = $defaultSections;
      }

      $variances = $this->resolveVariances($sections, $seed['variances'] ?? null);
      $layoutTemplate = $seed['layout_template'] ?? $facility->layout_template ?? 'default-template';

      WebContent::updateOrCreate(
        [
          'facility_id' => $facility->id,
          'layout_template' => $layoutTemplate,
        ],
        [
          'sections' => array_values($sections),
          'variances' => $variances,
          'is_active' => true,
        ]
      );
    }
  }

  /**
   * @param  array<int, string>  $sections
   * @param  mixed  $variances
   * @return array<string, string>
   */
  private function resolveVariances(array $sections, mixed $variances): array
  {
    if (is_array($variances) && $variances !== [] && ! array_is_list($variances)) {
      $map = [];
      foreach ($sections as $section) {
        $map[$section] = (string) ($variances[$section] ?? 'default');
      }

      return $map;
    }

    if (is_array($variances) && array_is_list($variances)) {
      $map = [];
      foreach ($sections as $index => $section) {
        $map[$section] = (string) ($variances[$index] ?? 'default');
      }

      return $map;
    }

    return array_combine($sections, array_fill(0, count($sections), 'default')) ?: [];
  }
}
