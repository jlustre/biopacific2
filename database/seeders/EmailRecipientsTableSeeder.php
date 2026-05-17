<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;

class EmailRecipientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facilities = Facility::all();

        $data = [];
        foreach ($facilities as $facility) {
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'book-a-tour',
                'email' => 'admission@' . $facility->domain,
                'email_alt_1' => 'tours@' . $facility->domain,
                'email_alt_2' => 'info@' . $facility->domain,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'inquiry',
                'email' => 'contact@' . $facility->domain,
                'email_alt_1' => 'support@' . $facility->domain,
                'email_alt_2' => 'help@' . $facility->domain,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $data[] = [
                'facility_id' => $facility->id,
                'category' => 'hiring',
                'email' => 'hiring@' . $facility->domain,
                'email_alt_1' => 'jobs@' . $facility->domain,
                'email_alt_2' => 'careers@' . $facility->domain,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('email_recipients')->insert($data);
    }
}