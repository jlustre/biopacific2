<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

// Get the container
$app = require_once __DIR__ . '/bootstrap/app.php';

// Load User model
use App\Models\User;

// Find facility-dsd user
$user = User::where('name', 'facility-dsd')
    ->orWhere('email', 'like', '%dsd%')
    ->first();

if ($user) {
    echo "Found user: " . $user->email . "\n";
    $user->update(['facility_id' => 1]);
    echo "Updated facility_id to 1 for user: " . $user->name . "\n";
    echo "Current facility_id: " . $user->refresh()->facility_id . "\n";
} else {
    echo "No facility-dsd user found\n";
    echo "\nAll users:\n";
    User::all(['id', 'name', 'email', 'facility_id'])->each(function($u) {
        echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Facility ID: {$u->facility_id}\n";
    });
}
?>
