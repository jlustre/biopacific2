<?php

namespace App\Services;

use App\Http\Controllers\Admin\Facilities\FilesController;
use App\Models\BPEmployee;
use App\Models\ImportLog;
use App\Models\ImportMappingPreset;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ImportPresetImportRunner
{
    public function __construct(
        protected ExcelWorkbookParser $parser,
        protected FilesController $filesController,
    ) {}

    public function run(
        ImportMappingPreset $preset,
        UploadedFile $file,
        int $importFacilityId,
        bool $confirmOverwrite = false,
        ?string $primaryWorksheet = null,
        ?int $importLogId = null,
    ): JsonResponse {
        if (!ImportMappingPresetAccess::canUse()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to import facility data.',
                'message' => 'Your account does not have the "use import mapping presets" permission.',
            ], 403);
        }

        $mappings = $preset->mappings;
        if (!is_array($mappings) || count($mappings) === 0) {
            return response()->json([
                'success' => false,
                'error' => 'This preset has no column mappings.',
                'message' => 'Edit the preset and add at least one column mapping before importing.',
            ], 422);
        }

        try {
            $parsed = $this->parser->parseUploadedFile($file);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to read workbook',
                'message' => 'Could not open or parse the Excel file: ' . $e->getMessage(),
            ], 422);
        }

        $worksheets = $parsed['worksheets'] ?? [];
        if (empty($worksheets)) {
            return response()->json([
                'success' => false,
                'error' => 'No worksheets found',
                'message' => 'The uploaded file does not contain any worksheets with data.',
            ], 422);
        }

        $worksheetDataMap = [];
        foreach ($worksheets as $ws) {
            $worksheetDataMap[$ws['name']] = $ws['data'] ?? [];
        }

        $primaryWs = $primaryWorksheet ?: ($mappings[0]['worksheet'] ?? null);
        $dataRows = $primaryWs && isset($worksheetDataMap[$primaryWs])
            ? $worksheetDataMap[$primaryWs]
            : null;

        if (!is_array($dataRows) || count($dataRows) === 0) {
            $fallback = collect($worksheets)->first(fn ($ws) => !empty($ws['data']));
            if ($fallback) {
                $primaryWs = $fallback['name'];
                $dataRows = $fallback['data'];
            }
        }

        if ($importLogId) {
            $log = ImportLog::query()->findOrFail($importLogId);
            $log->update(['total_rows' => count($dataRows)]);

            if ($log->cancel_requested_at) {
                return response()->json([
                    'success' => false,
                    'cancelled' => true,
                    'message' => 'Import cancelled before processing began.',
                ], 409);
            }

            if (! $confirmOverwrite) {
                $duplicates = $this->duplicateEmployeeNumbers($mappings, $dataRows);
                if ($duplicates !== []) {
                    $log->update([
                        'status' => ImportLog::STATUS_AWAITING_CONFIRMATION,
                        'summary' => array_merge($log->summary ?? [], [
                            'duplicates' => $duplicates,
                            'duplicates_found' => count($duplicates),
                        ]),
                    ]);

                    return response()->json([
                        'success' => false,
                        'duplicates' => $duplicates,
                        'message' => 'Duplicate employee IDs found. Confirm overwrite?',
                    ], 409);
                }
            }
        }

        if (!is_array($dataRows) || count($dataRows) === 0) {
            $expected = $mappings[0]['worksheet'] ?? 'the mapped worksheet';
            return response()->json([
                'success' => false,
                'error' => 'No data rows found',
                'message' => "No data rows were found for worksheet \"{$expected}\". Available worksheets: "
                    . implode(', ', array_keys($worksheetDataMap))
                    . '. Choose a different worksheet or update the preset mappings.',
                'available_worksheets' => array_keys($worksheetDataMap),
            ], 422);
        }

        $worksheetPayload = array_map(
            fn ($ws) => ['name' => $ws['name'], 'data' => $ws['data'] ?? []],
            $worksheets
        );

        $importRequest = Request::create(
            '/admin/facility/' . $importFacilityId . '/files/import-data',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'mappings' => array_values($mappings),
                'data' => $dataRows,
                'worksheets' => $worksheetPayload,
                'confirm_overwrite' => $confirmOverwrite,
                'import_log' => [
                    'import_log_id' => $importLogId,
                    'preset_id' => $preset->id,
                    'preset_facility_id' => (int) $preset->facility_id,
                    'source' => 'admin_preset',
                    'source_filename' => $file->getClientOriginalName(),
                ],
            ])
        );
        $importRequest->headers->set('Accept', 'application/json');

        return $this->filesController->importData($importRequest, $importFacilityId);
    }

    private function duplicateEmployeeNumbers(array $mappings, array $dataRows): array
    {
        $employeeNumberMapping = collect($mappings)->first(
            fn (array $mapping) => ($mapping['table'] ?? null) === 'bp_employees'
                && ($mapping['table_column'] ?? null) === 'employee_num'
        );
        $sourceColumn = $employeeNumberMapping['worksheet_column'] ?? null;

        if (! $sourceColumn) {
            return [];
        }

        $numbers = collect($dataRows)
            ->map(function (array $row) use ($sourceColumn) {
                if (array_key_exists($sourceColumn, $row)) {
                    return trim((string) $row[$sourceColumn]);
                }

                $matchedKey = collect(array_keys($row))->first(
                    fn ($key) => strcasecmp(trim((string) $key), trim((string) $sourceColumn)) === 0
                );

                return $matchedKey !== null ? trim((string) $row[$matchedKey]) : '';
            })
            ->filter()
            ->unique()
            ->values();

        if ($numbers->isEmpty()) {
            return [];
        }

        return BPEmployee::query()
            ->whereIn('employee_num', $numbers->all())
            ->pluck('employee_num')
            ->map(fn ($number) => (string) $number)
            ->sort()
            ->values()
            ->all();
    }
}
