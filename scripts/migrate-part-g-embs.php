<?php

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';

$targets = array_merge(
    glob($root.'/app/Livewire/Admin/Facilities/Checklist/PartGSections/*.php') ?: [],
    glob($root.'/app/Livewire/Admin/Facilities/Checklist/PartGSections/Concerns/*.php') ?: [],
    [
        $root.'/app/Support/CompetencyAssessmentHistoryBuilder.php',
        $root.'/app/Http/Controllers/EmployeePerformanceAssessmentController.php',
    ],
);

$replacements = [
    "in_array(\$response, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($response)',
    "in_array(\$rating, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($rating)',
    "in_array(\$normalizedRating, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($normalizedRating)',
    "in_array(\$normalized, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($normalized)',
    "in_array(\$response, ['E', 'S', 'U'], true)" => 'PartGCompetencyScoring::numericScore($response) !== null',
    "in_array(\$rating, ['E', 'S', 'U'], true)" => 'PartGCompetencyScoring::numericScore($rating) !== null',
    "if (\$response === null || \$response === '' || \$response === 'N') {" => 'if ($response === null || $response === \'\' || ! PartGCompetencyScoring::isValidItemRating($response)) {',
    "if (\$rating === 'N') {\n                \$notApplicable++;\n\n                continue;\n            }\n\n            if (PartGCompetencyScoring::numericScore(\$rating) !== null) {" => 'if (PartGCompetencyScoring::numericScore($rating) !== null) {',
    "\$rating === 'U'" => "PartGCompetencyScoring::isBelowExpectationsItemRating(\$rating)",
    "\$rating === 'U' ? trim(\$this->reviewModalComments) : null" => 'PartGCompetencyScoring::isBelowExpectationsItemRating($rating) ? trim($this->reviewModalComments) : null',
    "strtoupper((string) \$latest->rating) === \$rating" => 'PartGCompetencyScoring::normalizeItemRating((string) $latest->rating) === PartGCompetencyScoring::normalizeItemRating($rating)',
    "->filter(fn (array \$item) => empty(\$item['is_parent']) && in_array(\$item['rating'] ?? '', ['E', 'S', 'U'], true))" => '->filter(fn (array $item) => empty($item[\'is_parent\']) && PartGCompetencyScoring::isValidItemRating($item[\'rating\'] ?? null))',
    "->filter(fn (\$item) => in_array((\$item['rating'] ?? null), ['E', 'S', 'U'], true))" => '->filter(fn ($item) => PartGCompetencyScoring::isValidItemRating($item[\'rating\'] ?? null))',
    "!in_array(\$normalizedRating, ['E', 'S', 'U'], true)" => '! PartGCompetencyScoring::isValidItemRating($normalizedRating)',
    "in_array(\$normalized, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($normalized)',
    "in_array(\$normalizedRating, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($normalizedRating)',
    "in_array(\$rating, ['E', 'S', 'U', 'N'], true)" => 'PartGCompetencyScoring::isValidItemRating($rating)',
    "self::ratingToScore(\$rating) !== null || strtoupper(trim((string) \$rating)) === 'N'" => 'PartGCompetencyScoring::isValidItemRating($rating)',
    "return match (\$rating) {\n            'E' => 3,\n            'S' => 2,\n            'U' => 1,\n            default => null,\n        };" => 'return PartGCompetencyScoring::numericScore($rating);',
    "return match (strtoupper((string) \$rating)) {\n            'E' => 3,\n            'S' => 2,\n            'U' => 1,\n            default => null,\n        };" => 'return PartGCompetencyScoring::numericScore($rating);',
];

foreach ($targets as $file) {
    if (! is_file($file)) {
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if (! str_contains($content, 'use App\\Support\\PartGCompetencyScoring;')) {
        if (preg_match('/namespace App\\\\Livewire\\\\Admin\\\\Facilities\\\\Checklist\\\\PartGSections(?:\\\\Concerns)?;\R/', $content)) {
            $content = preg_replace(
                '/(namespace App\\\\Livewire\\\\Admin\\\\Facilities\\\\Checklist\\\\PartGSections(?:\\\\Concerns)?;\R)/',
                "$1\nuse App\\Support\\PartGCompetencyScoring;\n",
                $content,
                1
            );
        } elseif (str_contains($file, 'CompetencyAssessmentHistoryBuilder.php')) {
            // already in same namespace group - add use if needed
        } elseif (str_contains($file, 'EmployeePerformanceAssessmentController.php') && ! str_contains($content, 'PartGCompetencyScoring')) {
            $content = str_replace(
                "use App\\Support\\PartFPerformanceScoring;\n",
                "use App\\Support\\PartFPerformanceScoring;\nuse App\\Support\\PartGCompetencyScoring;\n",
                $content
            );
        }
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo str_replace($root.DIRECTORY_SEPARATOR, '', $file).PHP_EOL;
    }
}
