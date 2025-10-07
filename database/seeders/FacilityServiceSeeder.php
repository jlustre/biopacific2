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
        $services = Service::all();

        foreach ($facilities as $facility) {
            foreach ($services as $service) {
                DB::table('facility_service')->updateOrInsert([
                    'facility_id' => $facility->id,
                    'service_id' => $service->id,
                ], []);
            }
        }
    }
}
