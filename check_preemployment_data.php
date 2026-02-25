<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PreEmploymentApplication;
use App\Models\HiringActivityLog;

// Check current user with pre-employment
$user = User::where('email', 'jenessylustre@gmail.com')->first();

if ($user) {
    echo "=== User Found ===\n";
    echo "Email: {$user->email}\n";
    echo "ID: {$user->id}\n\n";
    
    // Check pre-employment application
    $preEmp = PreEmploymentApplication::where('user_id', $user->id)->first();
    
    if ($preEmp) {
        echo "=== Pre-Employment Application Found ===\n";
        echo "ID: {$preEmp->id}\n";
        echo "Status: {$preEmp->status}\n\n";
        
        // Check hiring activity logs
        $activities = HiringActivityLog::where('pre_employment_application_id', $preEmp->id)
            ->where('activity_type', 'returned')
            ->orderByDesc('created_at')
            ->get();
        
        echo "=== Returned Activities ===\n";
        echo "Count: " . count($activities) . "\n";
        
        foreach ($activities as $activity) {
            echo "\nActivity ID: {$activity->id}\n";
            echo "Form Type: {$activity->form_type}\n";
            echo "Notes: {$activity->notes}\n";
            echo "Returned At: {$activity->created_at}\n";
        }
    } else {
        echo "No pre-employment application found for this user\n";
    }
} else {
    echo "User not found\n";
}
?>
