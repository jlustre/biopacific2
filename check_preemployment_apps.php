<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Check for all pre-employment job applications
echo "=== All Pre-Employment Job Applications ===\n";
$preEmpApps = DB::table('job_applications')->where('status', 'pre-employment')->get();
foreach ($preEmpApps as $app) {
    echo "App ID: {$app->id}\n";
    echo "  Email in app: {$app->email}\n";
    echo "  User ID: " . ($app->user_id ?? 'NULL') . "\n";
    
    // Check if this email exists as a user
    $user = User::where('email', $app->email)->first();
    if ($user) {
        echo "  User exists: YES (User ID: {$user->id}, Email: {$user->email})\n";
    } else {
        echo "  User exists: NO\n";
    }
    echo "\n";
}

// Also check the applicant_code in case they logged in with that
echo "=== Check applicant login info ===\n";
$apps = DB::table('job_applications')->where('status', 'pre-employment')->get();
foreach ($apps as $app) {
    echo "Email: {$app->email}, Applicant Code: {$app->applicant_code}, Access Token: " . substr($app->access_token, 0, 10) . "...\n";
}
?>
