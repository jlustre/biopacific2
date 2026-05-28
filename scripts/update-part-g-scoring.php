<?php

$pattern = "/\s+'overallRating' => match \\(true\\) \\{\R\\s+\\\$(ratedCount|rated) === 0 => '—',\R\\s+\\\$average >= 2\\.5 => 'Excellent',\R\\s+\\\$average >= 1\\.5 => 'Satisfactory',\R\\s+\\\$average > 0 => 'Unsatisfactory',\R\\s+default => 'Needs Improvement',\R\\s+\\},/";

$dir = __DIR__.'/../app/Livewire/Admin/Facilities/Checklist/PartGSections';

foreach (glob($dir.'/*.php') as $file) {
    $content = file_get_contents($file);
    $original = $content;

    $content = preg_replace_callback($pattern, function (array $matches) {
        $countVar = $matches[1];

        return "            'overallRating' => PartGCompetencyScoring::overallLabel(\$average, \$$countVar),";
    }, $content);

    if ($content === null || $content === $original) {
        continue;
    }

    if (! str_contains($content, 'use App\\Support\\PartGCompetencyScoring;')) {
        $content = preg_replace(
            '/namespace App\\\\Livewire\\\\Admin\\\\Facilities\\\\Checklist\\\\PartGSections;\R/',
            "namespace App\\Livewire\\Admin\\Facilities\\Checklist\\PartGSections;\n\nuse App\\Support\\PartGCompetencyScoring;\n",
            $content,
            1
        );
    }

    file_put_contents($file, $content);
    echo basename($file).PHP_EOL;
}
