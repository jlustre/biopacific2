<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\User;

// Bootstrap Laravel
$app = app();

// Find and update the user
$user = User::where('name', 'facility-dsd')
    ->orWhere('email', 'like', '%dsd%')
    ->first();

if ($user) {
    echo "Found user: " . $user->email . "\n";
    $user->facility_id = 1;
    $user->save();
    echo "Updated facility_id to 1\n";
    echo "User facility_id is now: " . $user->facility_id . "\n";
} else {
    echo "No user found. Listing all users:\n";
    $users = User::select('id', 'name', 'email', 'facility_id')->get();
    foreach ($users as $u) {
        echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Facility ID: {$u->facility_id}\n";
    }
}
?>
