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

        // HR Regional Director (hrrd)
        $hrrd = User::firstOrCreate([
            'email' => 'hrrd@example.com',
        ], [
            'name' => 'HR Regional Director',
            'password' => Hash::make('password'),
        ]);
        $hrrd->syncRoles(['hrrd']);

        // Facility Admin assigned to 'almaden-healthcare-and-rehabilitation-center'
        $facility = \App\Models\Facility::where('slug', 'almaden-healthcare-and-rehabilitation-center')->first();
        $facilityAdmin = User::firstOrCreate([
            'email' => 'facilityadmin@example.com',
        ], [
            'name' => 'Facility Admin',
            'password' => Hash::make('password'),
            'facility_id' => $facility ? $facility->id : null,
        ]);
        $facilityAdmin->syncRoles(['facility-admin']);

        // Facility DSD
        $facilityDsd = User::firstOrCreate([
            'email' => 'facilitydsd@example.com',
        ], [
            'name' => 'Facility DSD',
            'password' => Hash::make('password'),
        ]);
        $facilityDsd->syncRoles(['facility-dsd']);

        // Facility Editor
        $facilityEditor = User::firstOrCreate([
            'email' => 'facilityeditor@example.com',
        ], [
            'name' => 'Facility Editor',
            'password' => Hash::make('password'),
        ]);
        $facilityEditor->syncRoles(['facility-editor']);

        // Regular User
        $regularUser = User::firstOrCreate([
            'email' => 'regularuser@example.com',
        ], [
            'name' => 'Regular User',
            'password' => Hash::make('password'),
        ]);
        $regularUser->syncRoles(['regular-user']);
    }
}
