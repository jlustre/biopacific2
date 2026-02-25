<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignFacilityCommand extends Command
{
    protected $signature = 'user:assign-facility {email} {facility_id}';
    protected $description = 'Assign a facility to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $facilityId = $this->argument('facility_id');

        $user = User::where('email', $email)
            ->orWhere('name', $email)
            ->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $user->update(['facility_id' => $facilityId]);
        $this->info("Assigned facility {$facilityId} to user {$user->name} ({$user->email})");
        return 0;
    }
}
