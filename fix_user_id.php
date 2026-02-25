<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Update the jclustre pre-employment record to have user_id = 7
$result = DB::table('job_applications')
    ->where('email', 'jclustre@gmail.com')
    ->where('status', 'pre-employment')
    ->update(['user_id' => 7]);

echo "Updated {$result} record(s)\n";

// Verify the update
$app = DB::table('job_applications')
    ->where('email', 'jclustre@gmail.com')
    ->where('status', 'pre-employment')
    ->first();

if ($app) {
    echo "Verified: jclustre@gmail.com now has user_id = {$app->user_id}\n";
} else {
    echo "ERROR: Record not found after update\n";
}
?>
