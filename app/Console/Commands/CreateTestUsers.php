<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test users for facility access control testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create facility admin user
        $facilityAdmin = User::create([
            'name' => 'Test Facility Admin',
            'email' => 'facility-admin@test.com',
            'password' => bcrypt('password123'),
            'facility_id' => 1, // First facility
        ]);
        $facilityAdmin->assignRole('facility-admin');

        // Create facility editor user  
        $facilityEditor = User::create([
            'name' => 'Test Facility Editor',
            'email' => 'facility-editor@test.com',
            'password' => bcrypt('password123'),
            'facility_id' => 2, // Second facility (if exists), fallback to Corporate
        ]);
        $facilityEditor->assignRole('facility-editor');

        // Create regular user
        $regularUser = User::create([
            'name' => 'Test Regular User',
            'email' => 'regular@test.com',
            'password' => bcrypt('password123'),
            'facility_id' => 99, // Bio-Pacific Corporate
        ]);
        $regularUser->assignRole('regular-user');

        $this->info('Test users created successfully!');
        $this->info('Facility Admin: facility-admin@test.com (password: password123)');
        $this->info('Facility Editor: facility-editor@test.com (password: password123)');
        $this->info('Regular User: regular@test.com (password: password123)');
        
        return 0;
    }
}
