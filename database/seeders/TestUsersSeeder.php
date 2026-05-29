<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\SeedsUserEmployeeRecords;
use Illuminate\Database\Seeder;
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
        SeedsUserEmployeeRecords::seed($rdhr, [
            'facility_id' => 99,
            'position_index' => 3,
        ]);

        // Facility Admin assigned to 'almaden-healthcare-and-rehabilitation-center'
        $facilityAdmin = User::firstOrCreate([
            'email' => 'facilityadmin@example.com',
        ], [
            'name' => 'Michael Monroe',
            'password' => Hash::make('password'),
            'facility_id' => 14,
        ]);
        $facilityAdmin->syncRoles(['facility-admin']);
        SeedsUserEmployeeRecords::seed($facilityAdmin, [
            'facility_id' => 14,
            'position_index' => 3,
        ]);

        // Facility DSD
        $facilityDsd = User::firstOrCreate([
            'email' => 'facilitydsd@example.com',
        ], [
            'name' => 'Chimere Randell',
            'password' => Hash::make('password'),
            'facility_id' => 17,
        ]);
        $facilityDsd->syncRoles(['facility-dsd']);
        SeedsUserEmployeeRecords::seed($facilityDsd, [
            'facility_id' => 17,
            'position_index' => 4,
        ]);

        // Facility Editor
        $facilityEditor = User::firstOrCreate([
            'email' => 'facilityeditor@example.com',
        ], [
            'name' => 'Facility Editor',
            'password' => Hash::make('password'),
            'facility_id' => 1,
        ]);
        $facilityEditor->syncRoles(['facility-editor']);
        SeedsUserEmployeeRecords::seed($facilityEditor, [
            'facility_id' => 1,
            'position_index' => 5,
        ]);

        // Regular User
        $regularUser = User::firstOrCreate([
            'email' => 'regularuser@example.com',
        ], [
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'facility_id' => 1,
        ]);
        $regularUser->syncRoles(['regular-user']);
        SeedsUserEmployeeRecords::seed($regularUser, [
            'facility_id' => 1,
            'position_index' => 0,
        ]);
    }
}
