<?php

$root = dirname(__DIR__);
$dir = $root.'/app/Livewire/Admin/Facilities/Checklist/PartGSections';

$patterns = [
    "/\\\$points \\+= match \\(\\\$response\\) \\{\R\\s+'E' => 3,\R\\s+'S' => 2,\R\\s+'U' => 1,\R\\s+default => 0,\R\\s+\\};/" => '$points += PartGCompetencyScoring::numericScore($response) ?? 0;',
    "/\\\$points \\+= match \\(\\\$rating\\) \\{\R\\s+'E' => 3,\R\\s+'S' => 2,\R\\s+'U' => 1,\R\\s+default => 0,\R\\s+\\};/" => '$points += PartGCompetencyScoring::numericScore($rating) ?? 0;',
    "/\\\$ratedCount\\+\\+;\\R\\s+\\\$points \\+= match \\(\\\$response\\) \\{[^}]+\};/s" => '$ratedCount++;
            $points += PartGCompetencyScoring::numericScore($response) ?? 0;',
];

foreach (glob($dir.'/*.php') as $file) {
    $content = file_get_contents($file);
    $original = $content;

    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }

    $content = str_replace(
        "if (in_array(\$legacyRating, ['E', 'S', 'U'], true))",
        'if (PartGCompetencyScoring::isValidItemRating($legacyRating))',
        $content
    );
    $content = str_replace(
        "if (\$normalizedKey === '' || ! in_array(\$normalizedRating, ['E', 'S', 'U'], true))",
        "if (\$normalizedKey === '' || ! PartGCompetencyScoring::isValidItemRating(\$normalizedRating))",
        $content
    );
    $content = str_replace(
        "/** @var array<int,string> source_item_id => 'E'|'S'|'U'|'N' */",
        "/** @var array<int,string> source_item_id => 'E'|'M'|'B' */",
        $content
    );

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo basename($file).PHP_EOL;
    }
}
