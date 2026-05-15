<?php

$dir = __DIR__ . '/../app/Livewire/Admin/Facilities/Checklist/PartGSections';
$files = glob($dir . '/*Competency*.php');

$skip = ['TracheostomyCareCompetency.php'];

foreach ($files as $path) {
    if (in_array(basename($path), $skip, true)) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    if (! str_contains($content, 'ManagesPartGSectionExclusion')) {
        $content = str_replace(
            'use Livewire\Component;',
            "use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;\nuse Livewire\Component;",
            $content
        );
        $content = preg_replace(
            '/(class \w+ extends Component\s*\{)/',
            "$1\n    use ManagesPartGSectionExclusion;",
            $content,
            1
        );
    }

    $content = preg_replace(
        '/if \(\$this->assessmentLocked\) \{\s+return;\s+\}\s+if \(! in_array\(\$rating/',
        "if (\$this->assessmentLocked || \$this->sectionExcluded) {\n            return;\n        }\n\n        if (! in_array(\$rating",
        $content,
        1
    );

    $content = preg_replace(
        '/protected function calculateScores\(\): array\s*\{\s+\$ratedCount = 0;/',
        "protected function calculateScores(): array\n    {\n        if (\$this->sectionExcluded) {\n            return \$this->sectionExcludedScores();\n        }\n\n        \$ratedCount = 0;",
        $content,
        1
    );

    $content = preg_replace(
        '/(\$this->validate\(\[[^\]]+\]\);\s+)foreach \(\$this->scorableItemIds\(\) as \$itemId\) \{/',
        "$1if (! \$this->sectionExcluded) {\n            foreach (\$this->scorableItemIds() as \$itemId) {",
        $content,
        1
    );

    $content = preg_replace(
        '/(addError\(\'responses\', \'Please rate all competency items before submitting\.\'\);\s+return;\s+\}\s+\})\s+(\$this->persistResponses\(\'submitted\'\);)/s',
        "$1\n        }\n\n        $2",
        $content,
        1
    );

    $content = preg_replace(
        '/if \(\$row && is_array\(\$row->snapshot_json\)\) \{\s+\$updateData\[\'snapshot_json\'\] = \$row->snapshot_json;\s+\}/',
        '$updateData = $this->withExcludedSnapshot($updateData, $row);',
        $content,
        1
    );

    if (! str_contains($content, 'loadSectionExcludedFromAssessment')) {
        $content = preg_replace(
            '/(\$this->employeeSignDate = \$assessment->employee_signed_at\?->format\(\'Y-m-d\'\) \?\? \'\';\s+\})/',
            "$1\n\n        \$this->loadSectionExcludedFromAssessment(\$assessment);",
            $content,
            1
        );
    }


    file_put_contents($path, $content);
    echo 'Updated: ' . basename($path) . PHP_EOL;
}

// Tracheostomy refactor
$trachPath = $dir . '/TracheostomyCareCompetency.php';
$trach = file_get_contents($trachPath);
if (! str_contains($trach, 'ManagesPartGSectionExclusion')) {
    $trach = str_replace('use Livewire\Component;', "use App\Livewire\Admin\Facilities\Checklist\PartGSections\Concerns\ManagesPartGSectionExclusion;\nuse Livewire\Component;", $trach);
    $trach = preg_replace('/(class TracheostomyCareCompetency extends Component\s*\{)/', "$1\n    use ManagesPartGSectionExclusion;", $trach, 1);
}
$trach = preg_replace('/\s+public bool \$sectionExcluded = false;/', '', $trach, 1);
$trach = preg_replace('/\s+public function updatedSectionExcluded\(\): void\s*\{\s*\$this->persistDraftIfPossible\(\);\s*\}/', '', $trach, 1);
$trach = preg_replace(
    '/\$excluded = collect\(\$snapshot\[\'excluded_section_labels\'\] \?\? \[\]\).*?\$this->sectionExcluded = in_array\(self::SECTION, \$excluded, true\);/s',
    '$this->loadSectionExcludedFromAssessment($assessment);',
    $trach,
    1
);
$trach = preg_replace(
    '/protected function buildExcludedSectionLabels\(array \$snapshot\): array\s*\{.*?return array_values\(array_unique\(\$labels\)\);\s*\}/s',
    '',
    $trach,
    1
);
$trach = str_replace(
    "return [\n                'totalPoints' => 0,\n                'average' => 0,\n                'overallRating' => 'Excluded',\n            ];",
    'return $this->sectionExcludedScores();',
    $trach
);
file_put_contents($trachPath, $trach);
echo 'Updated: TracheostomyCareCompetency.php' . PHP_EOL;
