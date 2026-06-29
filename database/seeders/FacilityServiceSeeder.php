<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Service;
use Database\Seeders\Support\FacilitiesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilityServiceSeeder extends Seeder
{
  public function run(): void
  {
    $globalServices = Service::where('is_global', true)->get();
    $globalServiceIds = $globalServices->pluck('id')->map(fn ($id) => (int) $id)->all();

    foreach (Facility::all() as $facility) {
      $seed = FacilitiesSeedData::findBySlug((string) $facility->slug);
      $requestedIds = is_array($seed['service_ids'] ?? null) ? $seed['service_ids'] : [];
      $requestedIds = array_values(array_map('intval', $requestedIds));

      $serviceIds = $requestedIds !== []
        ? array_values(array_intersect($requestedIds, $globalServiceIds))
        : $globalServiceIds;

      foreach ($serviceIds as $serviceId) {
        DB::table('facility_service')->updateOrInsert([
          'facility_id' => $facility->id,
          'service_id' => $serviceId,
        ], []);
      }
    }
  }
}
