<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobOpening;
use App\Models\Facility;

class JobOpeningSeeder extends Seeder
{
    public function run()
    {
        $facilities = Facility::all();

        foreach ($facilities as $facility) {
            JobOpening::create([
                'facility_id' => $facility->id,
                'title' => 'Registered Nurse',
                'description' => 'We are seeking a compassionate and skilled Registered Nurse to join our healthcare team.',
                'department' => 'Nursing',
                'employment_type' => 'Full-time',
                'posted_at' => now(),
                'expires_at' => now()->addDays(30),
                'active' => true,
            ]);

            JobOpening::create([
                'facility_id' => $facility->id,
                'title' => 'Physical Therapist',
                'description' => 'Join our rehabilitation team as a Physical Therapist helping patients recover and improve their quality of life.',
                'department' => 'Rehabilitation',
                'employment_type' => 'Full-time',
                'posted_at' => now(),
                'expires_at' => now()->addDays(45),
                'active' => true,
            ]);
        }
    }
}