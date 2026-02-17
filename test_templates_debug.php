<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$title = 'Registered Nurse';

$position = \App\Models\Position::where('title', $title)->first();

if (!$position) {
    echo "Position not found for title: {$title}\n";
    exit(1);
}

echo "Position found:\n";
echo "ID: {$position->id}\n";
echo "Title: {$position->title}\n";
echo "Department ID: {$position->department_id}\n\n";

$templates = \App\Models\JobDescriptionTemplate::where('position_id', $position->id)
    ->orderByDesc('updated_at')
    ->get(['id', 'name', 'contents', 'job_descriptions']);

echo "Templates found: " . $templates->count() . "\n\n";

foreach ($templates as $template) {
    echo "Template ID: {$template->id}\n";
    echo "Template Name: {$template->name}\n";
    echo "---\n";
}

$templatesArray = $templates->map(fn ($template) => [
    'id' => $template->id,
    'name' => $template->name,
    'contents' => $template->contents,
    'job_descriptions' => $template->job_descriptions,
])->toArray();

echo "\nArray format:\n";
print_r($templatesArray);
