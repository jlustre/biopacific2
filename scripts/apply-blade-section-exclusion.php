<?php

$bladeDir = __DIR__ . '/../resources/views/livewire/admin/facilities/checklist/part-g-sections';

$configs = [
    'licensed-nurse-competency-skills.blade.php' => ['ln', 'lnc-summary-form'],
    'blood-administration-competency.blade.php' => ['blood', 'bac-summary-form'],
    'blood-glucose-system-skills-competency.blade.php' => ['blood-glucose', 'bgs-summary-form'],
    'nurse-treatment-skills-competency.blade.php' => ['nurse-treatment', 'nts-summary-form'],
    'hand-hygiene-competency-skills.blade.php' => ['hand-hygiene', 'hhc-summary-form'],
    'ventilator-management-skills-competency.blade.php' => ['ventilator', 'vmc-summary-form'],
    'personal-protective-equipment-competency.blade.php' => ['ppe', 'ppe-summary-form'],
    'medication-administration-competency.blade.php' => ['medication-admin', 'mac-summary-form'],
    'cna-skills-checklist-competency.blade.php' => ['cna-skills', 'csc-summary-form'],
    'perineal-care-competency.blade.php' => ['perineal-care', 'pcc-summary-form'],
    'use-of-hoyer-lift-training-competency.blade.php' => ['hoyer-lift', 'hlt-summary-form'],
];

$includePartial = "@include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => '%s',
                                    'summaryFormId' => '%s',
                                ])";

$buttonRegex = '/<button type="button"\s+class="go-to-summary-btn shrink-0[^"]*"[^>]*>[\s\S]*?Evaluation Summary ↓\s*<\/button>/';

$radioDisabledFrom = "                                            @disabled(\$assessmentLocked)\n                                            class=\"h-4 w-4";
$radioDisabledTo = "                                            @disabled(\$assessmentLocked || \$sectionExcluded)\n                                            class=\"h-4 w-4";

$summaryWrapRegex = '/(<div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures\.<\/motion\.div>\s*)(<div class="mb-3">\s*<div class="rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800">[\s\S]*?<\/motion\.motion\.div>\s*<\/motion\.div>\s*<\/motion\.div>)(\s*<div class="mb-3">\s*<label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS)/';

// Fix regex - files use </motion.div> or </div>? Check licensed nurse - uses </div> not motion

foreach ($configs as $file => [$accordionKey, $summaryFormId]) {
    $path = $bladeDir . '/' . $file;
    if (! is_file($path)) {
        echo "Skip missing: $file\n";
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    if (str_contains($content, 'section-header-actions')) {
        echo "Already updated: $file\n";
        continue;
    }

    $content = preg_replace(
        $buttonRegex,
        sprintf($includePartial, $accordionKey, $summaryFormId),
        $content,
        1,
        $count
    );

    if ($count !== 1) {
        echo "WARN button replace ($count): $file\n";
    }

    if (str_contains($content, $radioDisabledFrom)) {
        $content = str_replace($radioDisabledFrom, $radioDisabledTo, $content);
    }

    $intro = '<div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>';
    $reviewerStart = '<motion.div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS';

    if (! str_contains($content, 'section-excluded-notice') && str_contains($content, $intro)) {
        $pos = strpos($content, $intro);
        $reviewerPos = strpos($content, '<label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS', $pos);
        if ($reviewerPos !== false) {
            $middle = substr($content, $pos + strlen($intro), $reviewerPos - $pos - strlen($intro));
            $wrappedMiddle = "\n\n                @include('livewire.admin.facilities.checklist.part-g-sections.partials.section-excluded-notice')\n                @if(! \$sectionExcluded)".$middle.'                @endif'."\n\n                ";
            $content = substr($content, 0, $pos + strlen($intro)).$wrappedMiddle.substr($content, $reviewerPos);
        }
    }

    file_put_contents($path, $content);
    echo "Updated: $file\n";
}

// Tracheostomy: use header partial instead of inline exclude
$trachPath = $bladeDir . '/tracheostomy-care-competency.blade.php';
$trach = file_get_contents($trachPath);
if ($trach !== false && ! str_contains($trach, 'section-header-actions')) {
    $trach = preg_replace(
        '/<div class="flex shrink-0 flex-wrap items-center gap-2">[\s\S]*?Evaluation Summary ↓\s*<\/button>\s*<\/div>/',
        "@include('livewire.admin.facilities.checklist.part-g-sections.partials.section-header-actions', [
                                    'accordionKey' => 'tracheostomy',
                                    'summaryFormId' => 'trach-summary-form',
                                ])",
        $trach,
        1
    );

    $trachIntro = '<div class="text-sm text-gray-700 mb-3">Review the calculated result, add notes, and complete the signatures.</div>';
    if (str_contains($trach, '@if($sectionExcluded)')) {
        $trach = preg_replace(
            '/@if\(\$sectionExcluded\)\s*<p class="mb-3 rounded border border-amber-300[\s\S]*?@else\s*/',
            "@include('livewire.admin.facilities.checklist.part-g-sections.partials.section-excluded-notice')\n                @if(! \$sectionExcluded)\n                ",
            $trach,
            1
        );
        $trach = preg_replace(
            '/@endif\s*\n\s*<div class="mb-3">\s*<label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS/',
            "@endif\n\n                <div class=\"mb-3\">\n                    <label class=\"block text-xs font-semibold text-gray-700 mb-1\">REVIEWER COMMENTS",
            $trach,
            1
        );
    }

    file_put_contents($trachPath, $trach);
    echo "Updated: tracheostomy-care-competency.blade.php\n";
}
