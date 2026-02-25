<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\JobApplication;

// First, let's check what we have in job_applications
echo "=== Job Applications with pre-employment status ===\n";
$preEmpApps = JobApplication::where('status', 'pre-employment')->get();
foreach ($preEmpApps as $app) {
    echo "ID: {$app->id}, User ID: {$app->user_id}, Status: {$app->status}, Email: {$app->email}\n";
}

// Now check if User ID 8 exists
echo "\n=== Checking User ID 8 ===\n";
$user = User::find(8);
if ($user) {
    echo "User found: {$user->email}\n";
    
    // Check the relationship
    echo "\n=== Checking jobApplications relationship for User 8 ===\n";
    $jobApps = $user->jobApplications;
    echo "Total job applications for user 8: " . count($jobApps) . "\n";
    foreach ($jobApps as $app) {
        echo "  - ID: {$app->id}, Status: {$app->status}\n";
    }
    
    // Check with where query
    echo "\n=== Checking where('status', 'pre-employment') for User 8 ===\n";
    $preEmpCheck = $user->jobApplications()->where('status', 'pre-employment')->exists();
    echo "Pre-employment exists: " . ($preEmpCheck ? 'YES' : 'NO') . "\n";
    
    $preEmpApps = $user->jobApplications()->where('status', 'pre-employment')->get();
    echo "Pre-employment records found: " . count($preEmpApps) . "\n";
} else {
    echo "User ID 8 NOT found\n";
}

// Check all users
echo "\n=== All users in system ===\n";
$allUsers = User::all();
echo "Total users: " . count($allUsers) . "\n";
foreach ($allUsers as $user) {
    echo "  - ID: {$user->id}, Email: {$user->email}\n";
}
?>
