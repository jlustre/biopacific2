<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailRecipientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('email_recipients')->insert([
            [
                'facility_id' => 1,
                'category' => 'book-a-tour',
                'email' => 'joeycosep.lustre@marinerhealthcare.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'facility_id' => 1,
                'category' => 'contact',
                'email' => 'contact@almadenhrc.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'facility_id' => 1,
                'category' => 'hiring',
                'email' => 'hiring@almadenhrc.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}