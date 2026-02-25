<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== All Hiring Activity Logs in Database ===\n";
$activities = DB::table('hiring_activity_logs')->get();
echo "Total records: " . count($activities) . "\n\n";

foreach ($activities as $activity) {
    echo "ID: {$activity->id} | Type: {$activity->activity_type} | Recipient: {$activity->recipient_id} | Pre-Emp: {$activity->pre_employment_application_id} | Form: {$activity->form_type}\n";
    echo "  Notes: " . (substr($activity->notes ?? '', 0, 50)) . "\n";
}
?>
