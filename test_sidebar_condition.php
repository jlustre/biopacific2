<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Test for user 8 (jenessylustre@gmail.com)
$user = User::find(8);

if ($user) {
    echo "User: {$user->email} (ID: {$user->id})\n";
    echo "Total job applications: " . $user->jobApplications()->count() . "\n";
    
    $preEmpCheck = $user->jobApplications()->where('status', 'pre-employment')->exists();
    echo "Has pre-employment status: " . ($preEmpCheck ? 'YES' : 'NO') . "\n";
    
    $preEmpApps = $user->jobApplications()->where('status', 'pre-employment')->get();
    echo "Pre-employment records:\n";
    foreach ($preEmpApps as $app) {
        echo "  - ID: {$app->id}, Status: {$app->status}\n";
    }
    
    // Test the exact condition from sidebar
    echo "\n=== Testing sidebar condition ===\n";
    $condition = $user && $user->jobApplications()->where('status', 'pre-employment')->exists();
    echo "Condition result: " . ($condition ? 'TRUE - Link should show' : 'FALSE - Link should NOT show') . "\n";
} else {
    echo "User 8 not found!\n";
}

// Also check role
echo "\nUser roles: ";
var_dump($user->roles()->pluck('name')->toArray());
?>
