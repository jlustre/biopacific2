<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Support\Carbon;

class PositionDocumentRequirementsSeederExporter
{
    public function dataFilePath(): string
    {
        return database_path('seeders/data/position_document_requirements.php');
    }

    /**
     * @return array{
     *     positions_exported: int,
     *     custom_sets_added: int,
     *     path: string
     * }
     */
    public function writeSeederFile(): array
    {
        $existing = $this->loadExistingConfig();
        $sets = $existing['sets'] ?? [];
        $positionSets = [];
        $customSetsAdded = 0;

        $positions = Position::query()
            ->with(['requiredUploadTypes' => fn ($query) => $query
                ->generalPositionAssignable()
                ->wherePivot('is_required', true)
                ->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('title')
            ->get();

        foreach ($positions as $position) {
            $documentNames = $position->requiredUploadTypes
                ->pluck('name')
                ->map(fn ($name) => (string) $name)
                ->unique()
                ->sort()
                ->values()
                ->all();

            if ($documentNames === []) {
                continue;
            }

            $inferredSetNames = $this->inferSetNamesForDocuments($documentNames, $sets);

            if ($inferredSetNames === []) {
                $setKey = $this->customSetKeyForDocuments($documentNames);
                if (! isset($sets[$setKey])) {
                    $sets[$setKey] = $documentNames;
                    $customSetsAdded++;
                }
                $inferredSetNames = [$setKey];
            }

            $positionSets[$position->title] = array_values(array_unique($inferredSetNames));
        }

        ksort($sets);
        ksort($positionSets);

        $content = $this->buildDataFileContents($sets, $positionSets);
        $path = $this->dataFilePath();

        if (! is_dir(dirname($path))) {
            throw new \RuntimeException('Seeders data directory not found.');
        }

        $written = file_put_contents($path, $content);

        if ($written === false) {
            throw new \RuntimeException('Could not write seeder data file.');
        }

        return [
            'positions_exported' => count($positionSets),
            'custom_sets_added' => $customSetsAdded,
            'path' => $path,
        ];
    }

    /**
     * @param  list<string>  $documentNames
     * @param  array<string, list<string>>  $sets
     * @return list<string>
     */
    public function inferSetNamesForDocuments(array $documentNames, array $sets): array
    {
        $remaining = collect($documentNames)->unique()->sort()->values();
        $selectedSetNames = [];

        $setNames = collect(array_keys($sets))
            ->sortByDesc(fn (string $setName) => count($sets[$setName] ?? []))
            ->values();

        $progress = true;

        while ($progress && $remaining->isNotEmpty()) {
            $progress = false;

            foreach ($setNames as $setName) {
                $setDocuments = collect($sets[$setName] ?? [])
                    ->map(fn ($name) => (string) $name)
                    ->unique()
                    ->sort()
                    ->values();

                if ($setDocuments->isEmpty()) {
                    continue;
                }

                if ($setDocuments->every(fn (string $name) => $remaining->contains($name))) {
                    $selectedSetNames[] = $setName;
                    $remaining = $remaining->diff($setDocuments)->values();
                    $progress = true;
                }
            }
        }

        if ($remaining->isNotEmpty()) {
            return [];
        }

        return collect($selectedSetNames)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $documentNames
     */
    protected function customSetKeyForDocuments(array $documentNames): string
    {
        $sorted = collect($documentNames)->sort()->values()->all();
        $hash = substr(md5(implode("\0", $sorted)), 0, 8);

        return 'custom_' . $hash;
    }

    /**
     * @return array{sets: array<string, list<string>>, position_sets: array<string, list<string>>}
     */
    protected function loadExistingConfig(): array
    {
        $path = $this->dataFilePath();

        if (! is_file($path)) {
            return ['sets' => [], 'position_sets' => []];
        }

        $config = require $path;

        return [
            'sets' => is_array($config['sets'] ?? null) ? $config['sets'] : [],
            'position_sets' => is_array($config['position_sets'] ?? null) ? $config['position_sets'] : [],
        ];
    }

    /**
     * @param  array<string, list<string>>  $sets
     * @param  array<string, list<string>>  $positionSets
     */
    protected function buildDataFileContents(array $sets, array $positionSets): string
    {
        $exportedAt = Carbon::now()->toDateTimeString();
        $setsExport = $this->exportArray($sets, 2);
        $positionSetsExport = $this->exportArray($positionSets, 2);

        return <<<PHP
<?php

/**
 * Default general document requirements per position title.
 *
 * Document type names must match general upload_types (Documents Management),
 * not checklist-synced PART A–D types.
 *
 * Auto-generated from Documents Management → Position requirements → Update seeder on {$exportedAt}.
 *
 * @return array{
 *     sets: array<string, list<string>>,
 *     position_sets: array<string, list<string>>
 * }
 */
return [
    'sets' => {$setsExport},

    /*
     * Map each position title to requirement sets.
     * Positions not listed receive only `all_staff` when the seeder runs with defaults.
     */
    'position_sets' => {$positionSetsExport},
];

PHP;
    }

    /**
     * @param  array<mixed>  $value
     */
    protected function exportArray(array $value, int $indent = 0): string
    {
        $pad = str_repeat('    ', $indent);
        $childPad = str_repeat('    ', $indent + 1);
        $lines = ['['];

        foreach ($value as $key => $item) {
            $exportedKey = is_int($key)
                ? $key
                : var_export((string) $key, true);

            if (is_array($item)) {
                $exportedItem = $this->exportArray($item, $indent + 1);
                $lines[] = "{$childPad}{$exportedKey} => {$exportedItem},";
            } else {
                $lines[] = "{$childPad}{$exportedKey} => " . var_export($item, true) . ',';
            }
        }

        $lines[] = "{$pad}]";

        return implode(PHP_EOL, $lines);
    }
}
