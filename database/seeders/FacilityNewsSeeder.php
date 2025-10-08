<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;

class FacilityNewsSeeder extends Seeder
{
    public function run()
    {
        $facilities = Facility::all();
        $companyNews = News::where('scope', 'company')->get();
        $facilityNews = News::where('scope', 'facility')->get();

        // Attach company-wide news to every facility
        foreach ($companyNews as $news) {
            foreach ($facilities as $facility) {
                $exists = DB::table('facility_news')
                    ->where('facility_id', $facility->id)
                    ->where('news_id', $news->id)
                    ->exists();
                if (!$exists) {
                    DB::table('facility_news')->insert([
                        'facility_id' => $facility->id,
                        'news_id' => $news->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Randomly assign some facility-scoped news to facilities
        foreach ($facilityNews as $news) {
            // Pick 1-3 random facilities for each news
            $randomFacilities = $facilities->random(rand(1, min(3, $facilities->count())));
            foreach ($randomFacilities as $facility) {
                $exists = DB::table('facility_news')
                    ->where('facility_id', $facility->id)
                    ->where('news_id', $news->id)
                    ->exists();
                if (!$exists) {
                    DB::table('facility_news')->insert([
                        'facility_id' => $facility->id,
                        'news_id' => $news->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
