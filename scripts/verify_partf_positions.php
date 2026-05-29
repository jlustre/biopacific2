<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\PartFPerformanceScoring;

$expectations = [
    'Registered Nurse' => 39,
    'Certified Nursing Assistant' => 36,
    'Administrator' => 28,
    'Dietary Aide' => 36,
    'Housekeeper' => 37,
    'Maintenance Technician' => 39,
    'Laundry Staff' => 37,
    'Office Staff' => 37,
];

$failed = false;

foreach ($expectations as $title => $expected) {
    $count = count(PartFPerformanceScoring::scorableItemIds(null, $title));
    $ok = $count === $expected;
    echo $title.': '.$count.' (expected '.$expected.')'.($ok ? ' OK' : ' MISMATCH').PHP_EOL;
    $failed = $failed || ! $ok;
}

$none = count(PartFPerformanceScoring::scorableItemIds(null, null));
echo 'No position: '.$none.($none === 0 ? ' OK' : ' MISMATCH').PHP_EOL;
$failed = $failed || $none !== 0;

exit($failed ? 1 : 0);
