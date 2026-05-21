<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // HR Regional Director (rdhr)
        $rdhr = User::firstOrCreate([
            'email' => 'rdhr@example.com',
        ], [
            'name' => 'Liszel Justice',
            'password' => Hash::make('password'),
            'facility_id' => 99,
        ]);
        $rdhr->syncRoles(['rdhr']);

        // Facility Admin assigned to 'almaden-healthcare-and-rehabilitation-center'
        $facility = \App\Models\Facility::where('slug', 'almaden-healthcare-and-rehabilitation-center')->first();
        $facilityAdmin = User::firstOrCreate([
            'email' => 'facilityadmin@example.com',
        ], [
            'name' => 'Michael Monroe',
            'password' => Hash::make('password'),
            'facility_id' => 14,
        ]);
        $facilityAdmin->syncRoles(['facility-admin']);

        // Facility DSD
        $facilityDsd = User::firstOrCreate([
            'email' => 'facilitydsd@example.com',
        ], [
            'name' => 'Chimere Randell',
            'password' => Hash::make('password'),
            'facility_id' => 17,
        ]);
        $facilityDsd->syncRoles(['facility-dsd']);

        // Facility Editor
        $facilityEditor = User::firstOrCreate([
            'email' => 'facilityeditor@example.com',
        ], [
            'name' => 'Facility Editor',
            'password' => Hash::make('password'),
            'facility_id' => 1,
        ]);
        $facilityEditor->syncRoles(['facility-editor']);

        // Regular User
        $regularUser = User::firstOrCreate([
            'email' => 'regularuser@example.com',
        ], [
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'facility_id' => 1,
        ]);
        $regularUser->syncRoles(['regular-user']);
    }
}
