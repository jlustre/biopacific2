<?php
// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache cleared<br>";
} else {
    echo "✗ OPcache not available<br>";
}

// Clear APCu cache if available
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✓ APCu cache cleared<br>";
}

// Show PHP version
echo "✓ PHP Version: " . phpversion() . "<br>";

// Show loaded extensions
echo "✓ OPcache enabled: " . (extension_loaded('Zend OPcache') ? 'Yes' : 'No') . "<br>";

echo "<br><strong>Done! Delete this file now for security.</strong>";
