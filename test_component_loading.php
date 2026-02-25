<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->boot();

try {
    $class = 'App\Livewire\JobOpeningsForm';
    if (class_exists($class)) {
        echo "✓ Component class found: $class\n";
        $instance = new $class();
        echo "✓ Component instantiated successfully\n";
    } else {
        echo "✗ Component class NOT found: $class\n";
    }
    
    // Check if view exists
    $viewFile = resource_path('views/livewire/job-openings-form.blade.php');
    if (file_exists($viewFile)) {
        echo "✓ View file exists: $viewFile\n";
    } else {
        echo "✗ View file NOT found: $viewFile\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
