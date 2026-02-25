<?php
// Quick logout script
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = Illuminate\Http\Request::capture()
);

Illuminate\Support\Facades\Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();

echo "Logged out successfully! You can now close this and try the pre-employment page again.";
