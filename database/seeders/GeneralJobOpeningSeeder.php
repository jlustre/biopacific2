<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobOpening;

class GeneralJobOpeningSeeder extends Seeder
{
    public function run()
    {
        $facilities = \App\Models\Facility::all();
        foreach ($facilities as $facility) {
            JobOpening::updateOrCreate([
                'title' => 'General Application',
                'facility_id' => $facility->id
            ], [
                'description' => 'Submit your application for future opportunities.',
                'active' => false, // Do not display in job openings
                'facility_id' => $facility->id,
                'employment_type' => 'Any',
            ]);
        }
    }
}
