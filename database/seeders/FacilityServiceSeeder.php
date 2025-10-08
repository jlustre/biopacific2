<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;
use App\Models\Service;

class FacilityServiceSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::all();
        $facilitySpecificServices = Service::where('is_global', false)->get();

        // Pick random facilities for service_id = 10
        $service10 = $facilitySpecificServices->where('id', 10)->first();
        $otherServices = $facilitySpecificServices->where('id', '!=', 10);
        $facilityIds = $facilities->pluck('id')->toArray();
        $randomFacilityIdsFor10 = collect($facilityIds)->shuffle()->take(max(1, floor(count($facilityIds) / 2)))->toArray();

        foreach ($facilities as $facility) {
            $serviceCount = $otherServices->count();
            if ($serviceCount === 0) {
                continue;
            }
            $randomCount = min(rand(2, 4), $serviceCount);
            $randomServices = $otherServices->random($randomCount);
            // Optionally add service_id = 10 for random facilities
            if ($service10 && in_array($facility->id, $randomFacilityIdsFor10)) {
                $randomServices = $randomServices->push($service10);
            }
            foreach ($randomServices as $service) {
                DB::table('facility_service')->updateOrInsert([
                    'facility_id' => $facility->id,
                    'service_id' => $service->id,
                ], []);
            }
        }
    }
}
