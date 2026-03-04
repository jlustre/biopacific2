<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PreEmploymentApplication;
use App\Models\HiringActivityLog;

// Irene Marie Villahermosa (jclustre@gmail.com)
$user = User::where('email', 'jclustre@gmail.com')->first();

if (!$user) {
    echo "User not found.\n";
    exit(1);
}

$preEmployment = PreEmploymentApplication::where('user_id', $user->id)->first();
if (!$preEmployment) {
    echo "Pre-employment application not found for user {$user->email}.\n";
    exit(1);
}

$activity = HiringActivityLog::where('recipient_id', $user->id)
    ->where('activity_type', 'returned')
    ->orderByDesc('created_at')
    ->first();

if (!$activity) {
    echo "No returned activity found for user {$user->email}.\n";
    exit(1);
}

echo "Before update:\n";
echo "  Activity ID: {$activity->id}\n";
echo "  Pre-Employment App ID: {$activity->pre_employment_application_id}\n";
echo "  Form Type: {$activity->form_type}\n";

$activity->pre_employment_application_id = $preEmployment->id;
if (empty($activity->form_type)) {
    $activity->form_type = 'application_form';
}
$activity->save();

$activity->refresh();

echo "After update:\n";
echo "  Activity ID: {$activity->id}\n";
echo "  Pre-Employment App ID: {$activity->pre_employment_application_id}\n";
echo "  Form Type: {$activity->form_type}\n";
?>
