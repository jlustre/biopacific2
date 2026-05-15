<?php

$bladeDir = __DIR__ . '/../resources/views/livewire/admin/facilities/checklist/part-g-sections';
$files = glob($bladeDir . '/*competency*.blade.php');

$pattern = '/                <\/div>\r?\n\r?\n                <div class="mb-3">\r?\n                                    @endif\r?\n\r?\n                <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS<\/label>/';

$replacement = <<<'TXT'
                </div>
                @endif

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">REVIEWER COMMENTS</label>
TXT;

foreach ($files as $path) {
    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    $orig = $content;
    $content = preg_replace($pattern, $replacement, $content, -1, $count);

    if (preg_match("/                        @endif\r?\n                    @error\('responses'\)/", $content)) {
        $content = preg_replace(
            "/(                        @endif)\r?\n(                    @error\('responses'\))/",
            "$1\n                    @endif\n\n$2",
            $content,
            1
        );
    }

    if ($content !== $orig) {
        file_put_contents($path, $content);
        echo 'Fixed: ' . basename($path) . " ($count endif blocks)\n";
    }
}
