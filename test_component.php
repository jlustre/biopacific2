<?php
require 'vendor/autoload.php';

try {
    $class = new \App\Livewire\JobOpeningsForm();
    echo "✓ Component class instantiated successfully\n";
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
