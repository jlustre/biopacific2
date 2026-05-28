<?php

$patterns = [
    '/Average Legend: <span class="font-normal">Below 1\.5 = Unsatisfactory[^<]*<\/span>/',
    '/Average Legend: <span class="font-normal">Below 1\.5 = Unsatisfactory &nbsp;[^<]*<\/span>/',
];

$replacement = "@include('admin.facilities.checklist.partials.part-g-average-legend')";

$roots = [
    __DIR__.'/../resources/views/livewire/admin/facilities/checklist/part-g-sections',
    __DIR__.'/../resources/views/admin/facilities/checklist/partg_sections',
    __DIR__.'/../resources/views/components',
];

foreach ($roots as $root) {
    if (! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo str_replace(__DIR__.'/../', '', $path).PHP_EOL;
        }
    }
}

$summaryForm = __DIR__.'/../resources/views/admin/facilities/checklist/employee-assessment-summary-form.blade.php';
$content = file_get_contents($summaryForm);
$content = str_replace(
    "\$assessmentLegendText = \$assessmentLegendText ?? 'Average Legend: Below 1.5 = Unsatisfactory   1.5 to 2.49 = Satisfactory   2.5 and above = Excellent';",
    "\$assessmentLegendText = \$assessmentLegendText ?? ('Average Legend: ' . \\App\\Support\\PartGCompetencyScoring::averageLegendText());",
    $content
);
file_put_contents($summaryForm, $content);
echo 'employee-assessment-summary-form.blade.php'.PHP_EOL;
