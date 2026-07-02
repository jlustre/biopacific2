<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class BackupDatabaseExporter
{
    /**
     * @param  list<string>  $tables
     * @return array{record_counts: array<string, int>, exported_tables: list<string>}
     */
    public function exportTables(string $workingDir, array $tables): array
    {
        $databaseDir = $workingDir . '/database';
        File::ensureDirectoryExists($databaseDir);

        $recordCounts = [];
        $exportedTables = [];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get();
            $payload = [
                'table' => $table,
                'exported_at' => now()->toIso8601String(),
                'row_count' => $rows->count(),
                'rows' => $rows->map(fn ($row) => (array) $row)->values()->all(),
            ];

            File::put(
                $databaseDir . '/' . $table . '.json',
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );

            $recordCounts[$table] = $rows->count();
            $exportedTables[] = $table;
        }

        return [
            'record_counts' => $recordCounts,
            'exported_tables' => $exportedTables,
        ];
    }

    /**
     * @param  list<string>  $tables
     */
    public function importTables(string $workingDir, array $tables): array
    {
        $imported = [];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                $file = $workingDir . '/database/' . $table . '.json';
                if (! is_file($file)) {
                    continue;
                }

                $payload = json_decode((string) file_get_contents($file), true);
                if (! is_array($payload) || ! isset($payload['rows']) || ! is_array($payload['rows'])) {
                    throw new \RuntimeException('Invalid database export for table: ' . $table);
                }

                if (! Schema::hasTable($table)) {
                    throw new \RuntimeException('Target table does not exist: ' . $table);
                }

                DB::table($table)->truncate();

                $chunks = array_chunk($payload['rows'], 200);
                foreach ($chunks as $chunk) {
                    if ($chunk !== []) {
                        DB::table($table)->insert($chunk);
                    }
                }

                $imported[$table] = count($payload['rows']);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return $imported;
    }

    /**
     * @return array<string, int>
     */
    public function currentRecordCounts(array $tables): array
    {
        $counts = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $counts[$table] = (int) DB::table($table)->count();
            }
        }

        return $counts;
    }
}
