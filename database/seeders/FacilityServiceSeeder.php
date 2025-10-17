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
        $globalServices = Service::where('is_global', true)->get();

        foreach ($facilities as $facility) {
            // Attach all global services to every facility
            foreach ($globalServices as $service) {
                DB::table('facility_service')->updateOrInsert([
                    'facility_id' => $facility->id,
                    'service_id' => $service->id,
                ], []);
            }
        }
    }
}
