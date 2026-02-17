<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Livewire\Livewire;

$component = Livewire::getClass('job-openings-form');

echo "Component class for 'job-openings-form': ";
var_export($component);
echo PHP_EOL;

$instance = app($component);

echo "Instance class: " . get_class($instance) . PHP_EOL;
