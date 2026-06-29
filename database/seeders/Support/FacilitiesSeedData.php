<?php

namespace Database\Seeders\Support;

class FacilitiesSeedData
{
  private static ?array $items = null;

  public static function all(): array
  {
    if (self::$items !== null) {
      return self::$items;
    }

    $path = database_path('seeders/data/facilities.json');

    if (! is_file($path)) {
      self::$items = [];

      return self::$items;
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    self::$items = is_array($decoded) ? self::normalize(self::dedupeCorporate($decoded)) : [];

    return self::$items;
  }

  public static function corporateSlug(): string
  {
    return (string) config('member-portal.corporate_facility_slug', 'bio-pacific-corporate');
  }

  public static function isCorporate(array $item): bool
  {
    $slug = (string) ($item['slug'] ?? '');

    return $slug === self::corporateSlug()
      || $slug === 'bio-pacific-corporation'
      || ($item['facility_number'] ?? '') === 'CORP001'
      || (int) ($item['id'] ?? 0) === (int) config('import-mapping.global_facility_id', 99);
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  public static function nonCorporate(): array
  {
    return array_values(array_filter(self::all(), fn (array $item) => ! self::isCorporate($item)));
  }

  public static function corporate(): ?array
  {
    foreach (self::all() as $item) {
      if (self::isCorporate($item)) {
        return $item;
      }
    }

    return null;
  }

  public static function findBySlug(string $slug): ?array
  {
    foreach (self::all() as $item) {
      if (($item['slug'] ?? '') === $slug) {
        return $item;
      }
    }

    return null;
  }

  /**
   * @param  array<int, array<string, mixed>>  $items
   * @return array<int, array<string, mixed>>
   */
  private static function dedupeCorporate(array $items): array
  {
    $corporateSlug = self::corporateSlug();
    $corporateEntries = [];
    $others = [];

    foreach ($items as $item) {
      if (self::isCorporate($item)) {
        $corporateEntries[] = $item;
      } else {
        $others[] = $item;
      }
    }

    if ($corporateEntries === []) {
      return $items;
    }

    $preferred = null;
    foreach ($corporateEntries as $entry) {
      if (($entry['slug'] ?? '') === $corporateSlug) {
        $preferred = $entry;
        break;
      }
    }

    $corporate = $preferred ?? $corporateEntries[0];
    $corporate['slug'] = $corporateSlug;

    return array_merge([$corporate], $others);
  }

  /**
   * @param  array<int, array<string, mixed>>  $items
   * @return array<int, array<string, mixed>>
   */
  private static function normalize(array $items): array
  {
    return array_values(array_map(function (array $item) {
      if (isset($item['latitude'])) {
        $item['latitude'] = (float) $item['latitude'];
      }
      if (isset($item['longitude'])) {
        $item['longitude'] = (float) $item['longitude'];
      }

      return $item;
    }, $items));
  }
}
