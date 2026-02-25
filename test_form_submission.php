<?php
// Quick test to simulate form submission
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a test POST request
$request = Illuminate\Http\Request::create(
    '/my-pre-employment/employee-application',
    'POST',
    [
        '_token' => 'test-token',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'phone_number' => '1234567890',
        'position_applied_for' => 1,
        'has_drivers_license' => 1,
        'drivers_license_number' => 'DL123456',
        'drivers_license_state' => 'CA',
        'drivers_license_expiration' => '2027-12-31',
        'authorized_to_work_usa' => 1,
        'worked_here_before' => 0,
        'relatives_work_here' => 0,
        'action' => 'save'
    ]
);

try {
    $response = $kernel->handle($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . substr($response->getContent(), 0, 500) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response ?? null);
