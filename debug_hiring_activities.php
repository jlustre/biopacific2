<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\HiringActivityLog;

// Get the currently logged-in user (use user 8 from earlier)
$user = User::find(8);

if ($user) {
    echo "=== Checking for User: {$user->email} (ID: {$user->id}) ===\n\n";
    
    // Check all hiring activity logs for this user
    echo "=== All Hiring Activity Logs for this user ===\n";
    $activities = HiringActivityLog::where('recipient_id', $user->id)
        ->orderByDesc('created_at')
        ->get();
    
    echo "Total activities: " . count($activities) . "\n\n";
    
    foreach ($activities as $activity) {
        echo "Activity ID: {$activity->id}\n";
        echo "  Type: {$activity->activity_type}\n";
        echo "  Form Type: {$activity->form_type}\n";
        echo "  Description: {$activity->description}\n";
        echo "  Notes: " . ($activity->notes ? substr($activity->notes, 0, 100) . "..." : "NULL") . "\n";
        echo "  Pre-Emp App ID: {$activity->pre_employment_application_id}\n";
        echo "  Created: {$activity->created_at}\n";
        echo "\n";
    }
    
    // Check if pre-employment app exists
    echo "=== Pre-Employment Application ===\n";
    $preEmp = \App\Models\PreEmploymentApplication::where('user_id', $user->id)->first();
    if ($preEmp) {
        echo "Pre-Employment App ID: {$preEmp->id}\n";
        echo "Status: {$preEmp->status}\n";
    } else {
        echo "No pre-employment application found for this user\n";
    }
} else {
    echo "User 8 not found\n";
}
?>
