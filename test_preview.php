<?php
// Simple test script to verify Layout Builder preview functionality
require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\LayoutBuilderController;

// Simulate a preview request
$testSections = [
    ['slug' => 'hero', 'variant' => 'video'],
    ['slug' => 'about', 'variant' => 'default'],
    ['slug' => 'services', 'variant' => 'grid'],
    ['slug' => 'contact', 'variant' => 'form']
];

echo "Testing Layout Builder Preview\n";
echo "Sections data: " . json_encode($testSections) . "\n";
echo "Sections is array: " . (is_array($testSections) ? 'yes' : 'no') . "\n";

// Test JSON encoding/decoding
$jsonString = json_encode($testSections);
echo "JSON string: " . $jsonString . "\n";

$decoded = json_decode($jsonString, true);
echo "Decoded is array: " . (is_array($decoded) ? 'yes' : 'no') . "\n";
echo "Decoded count: " . count($decoded) . "\n";

echo "Test completed successfully!\n";
