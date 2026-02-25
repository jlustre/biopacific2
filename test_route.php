<?php
require 'bootstrap/app.php';

$app = app();
$router = $app->make('router');

echo "Looking for pre-employment route:\n";
$found = false;
foreach ($router->getRoutes() as $route) {
    if (strpos($route->uri(), 'pre') !== false) {
        echo "Found: " . $route->uri() . " => " . $route->getActionName() . "\n";
        $found = true;
    }
}

if (!$found) {
    echo "Pre-employment route not found. All routes:\n";
    foreach ($router->getRoutes() as $route) {
        if (strpos($route->uri(), '/') === 0 && strlen($route->uri()) < 40) {
            echo "  " . $route->uri() . "\n";
        }
    }
}
