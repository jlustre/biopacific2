<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportMappingPresetValidator
{
    /** @var array<int, string> */
    protected array $targetTables;

    /**
     * @param array<int, string>|null $targetTables
     */
    public function __construct(?array $targetTables = null)
    {
        $this->targetTables = $targetTables ?? config('import-mapping.target_tables', []);
    }

    /**
     * @param array<int, array{name: string, columns?: array<int, string>}> $worksheets
     * @param array<int, array<string, mixed>> $mappings
     * @return array{
     *     valid: bool,
     *     summary: array{total: int, passed: int, failed: int},
     *     results: array<int, array<string, mixed>>,
     *     worksheets_in_file: array<int, string>
     * }
     */
    public function validate(array $worksheets, array $mappings): array
    {
        $mappings = array_values(array_filter($mappings, fn ($m) => is_array($m)));
        $worksheetIndex = $this->buildWorksheetIndex($worksheets);
        $tableColumnsCache = [];

        if ($mappings === []) {
            return [
                'valid' => false,
                'summary' => ['total' => 0, 'passed' => 0, 'failed' => 0],
                'results' => [],
                'worksheets_in_file' => array_keys($worksheetIndex),
                'message' => 'No mappings to validate.',
            ];
        }

        $results = [];
        $passed = 0;
        $failed = 0;

        foreach ($mappings as $index => $mapping) {
            $result = $this->validateMapping($index, $mapping, $worksheetIndex, $tableColumnsCache);
            $results[] = $result;
            if ($result['valid']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'valid' => $failed === 0,
            'summary' => [
                'total' => count($mappings),
                'passed' => $passed,
                'failed' => $failed,
            ],
            'results' => $results,
            'worksheets_in_file' => array_keys($worksheetIndex),
        ];
    }

    /**
     * @param array<string, array{columns: array<int, string>}> $worksheetIndex
     * @param array<string, array<int, string>> $tableColumnsCache
     * @return array<string, mixed>
     */
    protected function validateMapping(int $index, array $mapping, array $worksheetIndex, array &$tableColumnsCache): array
    {
        $worksheet = trim((string) ($mapping['worksheet'] ?? ''));
        $worksheetColumn = trim((string) ($mapping['worksheet_column'] ?? ''));
        $table = trim((string) ($mapping['table'] ?? ''));
        $tableColumn = trim((string) ($mapping['table_column'] ?? ''));

        $issues = [];
        $checks = [
            'worksheet' => ['ok' => false, 'label' => 'Worksheet in file'],
            'worksheet_column' => ['ok' => false, 'label' => 'Source column in worksheet'],
            'table' => ['ok' => false, 'label' => 'Target table'],
            'table_column' => ['ok' => false, 'label' => 'Target column in table'],
        ];

        $wsKey = $this->normalizeKey($worksheet);
        if ($worksheet === '') {
            $issues[] = 'Worksheet name is empty.';
        } elseif (!isset($worksheetIndex[$wsKey])) {
            $available = implode(', ', array_keys($worksheetIndex)) ?: '(none)';
            $issues[] = "Worksheet \"{$worksheet}\" was not found in the file. Available: {$available}.";
        } else {
            $checks['worksheet']['ok'] = true;

            if ($worksheetColumn === '') {
                $issues[] = 'Source column name is empty.';
            } elseif (!$this->columnExistsInList($worksheetColumn, $worksheetIndex[$wsKey]['columns'])) {
                $availableCols = implode(', ', $worksheetIndex[$wsKey]['columns']) ?: '(none)';
                $issues[] = "Column \"{$worksheetColumn}\" was not found on worksheet \"{$worksheet}\". Available: {$availableCols}.";
            } else {
                $checks['worksheet_column']['ok'] = true;
            }
        }

        if ($table === '') {
            $issues[] = 'Target table is empty.';
        } elseif (!in_array($table, $this->targetTables, true)) {
            $allowed = implode(', ', $this->targetTables);
            $issues[] = "Table \"{$table}\" is not an allowed import target. Allowed: {$allowed}.";
        } elseif (!Schema::hasTable($table)) {
            $issues[] = "Table \"{$table}\" does not exist in the database.";
        } else {
            $checks['table']['ok'] = true;

            if (!isset($tableColumnsCache[$table])) {
                $tableColumnsCache[$table] = Schema::getColumnListing($table);
            }

            if ($tableColumn === '') {
                $issues[] = 'Target column name is empty.';
            } elseif (!$this->columnExistsInList($tableColumn, $tableColumnsCache[$table])) {
                $availableCols = implode(', ', $tableColumnsCache[$table]) ?: '(none)';
                $issues[] = "Column \"{$tableColumn}\" was not found on table \"{$table}\". Available: {$availableCols}.";
            } else {
                $checks['table_column']['ok'] = true;
            }
        }

        return [
            'index' => $index,
            'worksheet' => $worksheet,
            'worksheet_column' => $worksheetColumn,
            'table' => $table,
            'table_column' => $tableColumn,
            'valid' => $issues === [],
            'checks' => $checks,
            'issues' => $issues,
        ];
    }

    /**
     * @param array<int, array{name: string, columns?: array<int, string>}> $worksheets
     * @return array<string, array{columns: array<int, string>}>
     */
    protected function buildWorksheetIndex(array $worksheets): array
    {
        $index = [];

        foreach ($worksheets as $worksheet) {
            $name = trim((string) ($worksheet['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $columns = [];
            foreach ($worksheet['columns'] ?? [] as $column) {
                $col = trim((string) $column);
                if ($col !== '') {
                    $columns[] = $col;
                }
            }

            if ($columns === [] && !empty($worksheet['data'][0]) && is_array($worksheet['data'][0])) {
                $columns = array_keys($worksheet['data'][0]);
            }

            $index[$this->normalizeKey($name)] = [
                'name' => $name,
                'columns' => $columns,
            ];
        }

        return $index;
    }

    protected function normalizeKey(string $value): string
    {
        $value = str_replace(["\xc2\xa0", "\xE2\x80\x8B", "\xEF\xBB\xBF"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);

        return strtolower($value);
    }

    /**
     * @param array<int, string> $columns
     */
    protected function columnExistsInList(string $column, array $columns): bool
    {
        $target = $this->normalizeKey($column);
        if ($target === '') {
            return false;
        }

        foreach ($columns as $col) {
            if ($this->normalizeKey((string) $col) === $target) {
                return true;
            }
        }

        return false;
    }
}
