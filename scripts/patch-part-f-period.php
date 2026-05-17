<?php
$path = __DIR__ . '/../resources/views/admin/facilities/checklist/employee-checklist-part_f.blade.php';
$d = 'd' . 'i' . 'v';
$content = file_get_contents($path);

if (!str_contains($content, '@if(!$hasAssessmentPeriod)')) {
    $needle = '        <' . $d . ' class="mb-4 rounded-md border border-slate-400 bg-slate-100 px-3 py-2 text-[11px] font-semibold text-slate-800 shadow-sm">' . "\n"
        . '            Rating Legend:';
    $insert = '        @if(!$hasAssessmentPeriod)' . "\n"
        . '        <' . $d . ' class="mb-6 rounded-md border-2 border-yellow-400 bg-yellow-100 px-4 py-4 shadow-sm" role="alert">' . "\n"
        . '            <p class="text-lg font-bold leading-snug text-red-600">' . "\n"
        . '                Select or create an Assessment Period above before completing the performance appraisal.' . "\n"
        . '            </p>' . "\n"
        . '        </' . $d . '>' . "\n"
        . '        @else' . "\n"
        . $needle;
    $content = str_replace($needle, $insert, $content);
}

$historyNeedle = '        </form>' . "\n" . '        @endif' . "\n\n" . '        <' . $d . ' class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">';
$historyReplace = '        </form>' . "\n" . '        @endif' . "\n" . '        @endif' . "\n\n" . '        <' . $d . ' class="mt-5 rounded-md border border-slate-400 bg-slate-50 p-3 shadow-sm">';

if (str_contains($content, $historyNeedle) && !preg_match('/@endif\s+\n\s+@endif\s+\n\s+<div class="mt-5/s', $content)) {
    $content = str_replace($historyNeedle, $historyReplace, $content);
}

file_put_contents($path, $content);
echo "done\n";
