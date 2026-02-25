<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Check all users and their pre-employment status
echo "=== All Users and Pre-Employment Status ===\n\n";

$users = User::all();
foreach ($users as $user) {
    $hasPreEmp = $user->jobApplications()->where('status', 'pre-employment')->exists();
    $preEmpCount = $user->jobApplications()->count();
    
    echo "User ID: {$user->id}\n";
    echo "  Email: {$user->email}\n";
    echo "  Total Job Applications: {$preEmpCount}\n";
    echo "  Has Pre-Employment: " . ($hasPreEmp ? 'YES ✓' : 'NO ✗') . "\n";
    
    if ($hasPreEmp) {
        $apps = $user->jobApplications()->where('status', 'pre-employment')->get();
        foreach ($apps as $app) {
            echo "    - App ID: {$app->id}, Status: {$app->status}\n";
        }
    }
    echo "\n";
}

// Check if there's a sessions table to see who's logged in
echo "\n=== Active Sessions ===\n";
if (DB::getSchemaBuilder()->hasTable('sessions')) {
    $sessions = DB::table('sessions')->where('payload', '!=', '')->limit(5)->get();
    echo "Found " . count($sessions) . " sessions\n";
    foreach ($sessions as $session) {
        echo "Session ID: " . substr($session->id, 0, 10) . "..., Last Activity: {$session->last_activity}\n";
    }
} else {
    echo "Sessions table not found\n";
}

// Check users table for last_login or similar
echo "\n=== User Login Info ===\n";
$users = User::orderByDesc('updated_at')->limit(5)->get();
foreach ($users as $user) {
    echo "{$user->email} - Last updated: {$user->updated_at}\n";
}
?>
