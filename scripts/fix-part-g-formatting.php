<?php

$dir = __DIR__.'/../app/Livewire/Admin/Facilities/Checklist/PartGSections';

foreach (glob($dir.'/*.php') as $file) {
    $content = file_get_contents($file);
    $updated = str_replace(
        "'average' => \$average,            'overallRating'",
        "'average' => \$average,\n            'overallRating'",
        $content
    );

    if ($updated !== $content) {
        file_put_contents($file, $updated);
    }
}
