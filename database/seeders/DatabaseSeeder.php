<?php

namespace Database\Seeders;

use App\Models\ColorScheme;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            ColorSchemesTableSeeder::class,
            FacilitySeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            WebContentsSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            ServiceSeeder::class,
        ]);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
