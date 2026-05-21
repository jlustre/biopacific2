<?php

namespace App\Services;

use App\Http\Controllers\Admin\Facilities\FilesController;
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
}
