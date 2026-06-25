<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Controlled by SEED_DEMO_DATA in .env (see config/seeding.php).
     *
     * Structure only:
     *   php artisan db:seed --class=StructuralSeeder
     *
     * Demo data only (after structure):
     *   php artisan db:seed --class=DemoDataSeeder
     */
    public function run(): void
    {
        $this->call([
            StructuralSeeder::class,
        ]);

        if (config('seeding.demo_data')) {
            $this->call([
                DemoDataSeeder::class,
            ]);
        } else {
            $this->command?->info('Skipping DemoDataSeeder (SEED_DEMO_DATA=false).');
        }
    }
}
