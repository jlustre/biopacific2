<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$title = 'Registered Nurse';
$count = App\Models\EmployeePerformanceItem::query()->applicableToPositionTitle($title)->count();
$template = App\Support\PerformanceAppraisalTemplate::templateForPositionTitle($title);
$scorable = count(App\Support\PartFPerformanceScoring::scorableItemIds(null, $title));

echo "RN template: {$template}\n";
echo "RN items in DB: {$count}\n";
echo "RN scorable ids: {$scorable}\n";
