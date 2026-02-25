<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Get current authenticated user
$user = auth()->user();
echo "Current User: " . ($user ? ($user->id . " - " . $user->email) : "NOT LOGGED IN") . "\n";

if ($user) {
    $jobApps = \App\Models\JobApplication::where('user_id', $user->id)->get();
    echo "Total Job Applications: " . count($jobApps) . "\n";
    
    foreach ($jobApps as $app) {
        echo "  - ID: " . $app->id . ", Status: " . $app->status . ", User: " . $app->user_id . "\n";
    }
    
    $preEmpCount = \App\Models\JobApplication::where('user_id', $user->id)
        ->where('status', 'pre-employment')
        ->count();
    echo "Pre-Employment Jobs: " . $preEmpCount . "\n";
}
