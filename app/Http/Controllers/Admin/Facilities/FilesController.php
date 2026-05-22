<?php

namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Services\ExcelWorkbookParser;
use App\Services\ImportLogRecorder;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SelectOption;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FilesController extends Controller
{
    public function __construct(
        protected ImportLogRecorder $importLogRecorder,
    ) {}

    /**
     * Handle import of Excel file for facility data.
     */
    public function import(Request $request, $facility)
    {
        if (!ImportMappingPresetAccess::canUse()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to import facility data.',
            ], 403);
        }

        Log::info('FilesController@import hit', ['facility' => $facility]);
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            return response()->json(
                app(ExcelWorkbookParser::class)->parseUploadedFile($request->file('file'))
            );
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

  /** Common spreadsheet abbreviations mapped to positions.title values. */
    protected array $positionTitleAliases = [
        'cna' => 'Certified Nursing Assistant',
        'na' => 'Nursing Assistant',
        'rn' => 'Registered Nurse',
        'lpn' => 'Licensed Vocational Nurse',
        'lvn' => 'Licensed Vocational Nurse',
        'don' => 'Director of Nursing',
        'adm' => 'Administrator',
        'admin' => 'Administrator',
    ];

    /**
     * Helper to resolve position_id from a title, numeric id, or common abbreviation.
     */
    protected function resolvePositionIdByTitle($title)
    {
        if ($title === null || $title === '') {
            return null;
        }

        if (is_numeric($title)) {
            $id = (int) $title;

            return \App\Models\Position::whereKey($id)->exists() ? $id : null;
        }

        $normalized = strtolower(trim((string) $title));
        if ($normalized === '') {
            return null;
        }

        if (isset($this->positionTitleAliases[$normalized])) {
            $title = $this->positionTitleAliases[$normalized];
            $normalized = strtolower(trim($title));
        }

        $resolved = \App\Models\Position::whereRaw('LOWER(TRIM(title)) = ?', [$normalized])->first();
        if ($resolved) {
            return $resolved->id;
        }

        if (!empty($title)) {
            $resolved = \App\Models\Position::whereRaw('LOWER(TRIM(position_code)) = ?', [$normalized])->first();
            if ($resolved) {
                return $resolved->id;
            }
        }

        $resolved = \App\Models\Position::whereRaw('LOWER(TRIM(title)) LIKE ?', ['%' . $normalized . '%'])->first();

        return $resolved ? $resolved->id : null;
    }

    /**
     * Normalize spreadsheet / user text for label lookups (trim, collapse whitespace, strip zero-width chars).
     */
    protected function normalizeImportLabel(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = $this->normalizeCellValue($value);
        if (!is_string($value)) {
            $value = trim((string) $value);
        }

        $value = str_replace(["\xc2\xa0", "\xE2\x80\x8B", "\xEF\xBB\xBF"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    /**
     * Resolve facility using the complete facilities.name only (exact, case-insensitive, trimmed).
     * Never uses partial/LIKE matching to assign an ID.
     */
    /**
     * Common spreadsheet typos mapped to normalized names before exact DB lookup.
     *
     * @return array<string, string> lowercase typo => lowercase canonical fragment
     */
    protected function facilityImportAliases(): array
    {
        return [
            'pineridge healthcare center' => 'pine ridge healthcare center',
        ];
    }

    protected function resolveFacilityIdByExactName(mixed $name): ?int
    {
        $normalized = $this->normalizeImportLabel($name);
        if ($normalized === '') {
            return null;
        }

        $aliasKey = strtolower($normalized);
        if (isset($this->facilityImportAliases()[$aliasKey])) {
            $normalized = $this->facilityImportAliases()[$aliasKey];
        }

        $resolved = \App\Models\Facility::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($normalized)])->first();
        if ($resolved) {
            return (int) $resolved->id;
        }

        foreach (\App\Models\Facility::query()->pluck('name', 'id') as $id => $facilityName) {
            if (strcasecmp($this->normalizeImportLabel($facilityName), $normalized) === 0) {
                return (int) $id;
            }
        }

        if (is_numeric($normalized)) {
            $id = (int) $normalized;

            return \App\Models\Facility::whereKey($id)->exists() ? $id : null;
        }

        return null;
    }

    protected function resolveFacilityIdByName($name): ?int
    {
        return $this->resolveFacilityIdByExactName($name);
    }

    /**
     * True only for short ambiguous fragments (e.g. "Healthcare"), not full facility names.
     */
    protected function isAmbiguousShortFacilityFragment(string $name): bool
    {
        $normalized = strtolower($this->normalizeImportLabel($name));
        if ($normalized === '') {
            return false;
        }

        if ($this->resolveFacilityIdByExactName($normalized) !== null) {
            return false;
        }

        $wordCount = count(preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return $wordCount < 3
            && \App\Models\Facility::whereRaw('LOWER(TRIM(name)) LIKE ?', ['%' . $normalized . '%'])->exists();
    }

    protected function getFacilityFullNameById(?int $facilityId): ?string
    {
        if (!$facilityId) {
            return null;
        }

        return \App\Models\Facility::whereKey($facilityId)->value('name');
    }

    /**
     * Facility the user intended for this import (facility-specific preset, else route / modal selection).
     */
    protected function importTargetFacilityId(?int $routeFacilityId, ?int $presetFacilityId = null): ?int
    {
        $globalId = (int) config('import-mapping.global_facility_id', 99);

        if ($presetFacilityId && $presetFacilityId !== $globalId) {
            return $presetFacilityId;
        }

        return $routeFacilityId;
    }

    /**
     * Resolve facility for import using the full facilities.name only (exact match, case-insensitive).
     * When an import target facility is set, a spreadsheet name for a different facility is ignored.
     */
    protected function resolveFacilityIdForImport(mixed $name, ?int $routeFacilityId, ?int $presetFacilityId = null): ?int
    {
        $routeFacilityName = $this->getFacilityFullNameById($routeFacilityId);
        $presetFacilityName = $this->getFacilityFullNameById($presetFacilityId);
        $globalId = (int) config('import-mapping.global_facility_id', 99);
        $importTargetId = $this->importTargetFacilityId($routeFacilityId, $presetFacilityId);
        $normalized = $this->normalizeImportLabel($name);

        if ($normalized === '') {
            if ($importTargetId) {
                return $importTargetId;
            }

            return $routeFacilityId;
        }

        $exactId = $this->resolveFacilityIdByExactName($normalized);
        if ($exactId !== null) {
            if ($importTargetId && (int) $exactId !== (int) $importTargetId) {
                Log::warning('Import: spreadsheet Facility name maps to a different facility than this import; using import target', [
                    'spreadsheet_value' => $normalized,
                    'resolved_facility_id' => $exactId,
                    'import_target_facility_id' => $importTargetId,
                    'import_target_facility_name' => $this->getFacilityFullNameById($importTargetId),
                    'route_facility_id' => $routeFacilityId,
                    'preset_facility_id' => $presetFacilityId,
                ]);

                return $importTargetId;
            }

            return $exactId;
        }

        if ($this->isAmbiguousShortFacilityFragment($normalized)) {
            Log::warning('Import: Facility value looks like a partial name, not a full facilities.name match', [
                'raw' => $normalized,
                'route_facility_id' => $routeFacilityId,
                'route_facility_name' => $routeFacilityName,
                'preset_facility_id' => $presetFacilityId,
                'hint' => 'Use the complete facility name (e.g. "Vale Healthcare Center"), not a fragment like "' . $normalized . '"',
            ]);

            if ($importTargetId) {
                return $importTargetId;
            }

            return $routeFacilityId;
        }

        if (is_numeric($normalized)) {
            $id = (int) $normalized;
            if ($importTargetId && $id !== (int) $importTargetId) {
                Log::warning('Import: numeric Facility value does not match import target; using import target', [
                    'raw' => $normalized,
                    'import_target_facility_id' => $importTargetId,
                    'route_facility_id' => $routeFacilityId,
                    'preset_facility_id' => $presetFacilityId,
                ]);

                return $importTargetId;
            }
            if ($routeFacilityId && $id === $routeFacilityId) {
                return $id;
            }
            if (!$routeFacilityId && \App\Models\Facility::whereKey($id)->exists()) {
                return $id;
            }
            Log::warning('Import: ignoring numeric Facility value — use full facility name instead', [
                'raw' => $normalized,
                'route_facility_id' => $routeFacilityId,
                'route_facility_name' => $routeFacilityName,
                'preset_facility_id' => $presetFacilityId,
            ]);

            if ($importTargetId) {
                return $importTargetId;
            }

            return $routeFacilityId;
        }

        Log::warning('Import: Facility column did not match any facility name exactly (full name required)', [
            'raw' => $normalized,
            'raw_length' => strlen($normalized),
            'route_facility_id' => $routeFacilityId,
            'route_facility_name' => $routeFacilityName,
            'preset_facility_id' => $presetFacilityId,
            'preset_facility_name' => $presetFacilityName,
            'hint' => $presetFacilityName
                ? 'Use the full name exactly: "' . $presetFacilityName . '"'
                : ($routeFacilityName ? 'Use the full name exactly: "' . $routeFacilityName . '"' : 'Enter the exact facility name as stored in the system'),
        ]);

        if ($importTargetId) {
            return $importTargetId;
        }

        return $routeFacilityId;
    }

    protected function importPresetFacilityId(Request $request): ?int
    {
        $id = (int) ($request->input('import_log.preset_facility_id') ?? 0);

        return $id > 0 ? $id : null;
    }

    /**
     * Read Facility from the preset-mapped worksheet first, then the row, then fallbacks.
     */
    protected function resolveAssignmentFacilityId(
        array $row,
        array $assignmentData,
        array $map,
        array $worksheetDataMap,
        ?int $routeFacilityId,
        ?int $presetFacilityId = null,
        array $employeeData = [],
    ): ?int {
        $lookupContext = array_merge($employeeData, $assignmentData);
        $globalId = (int) config('import-mapping.global_facility_id', 99);
        $importTargetId = $this->importTargetFacilityId($routeFacilityId, $presetFacilityId);
        $hasMappedWorksheet = !empty($map['worksheet']) && !empty($map['worksheet_column']) && !empty($worksheetDataMap);

        if ($presetFacilityId && $presetFacilityId !== $globalId) {
            return $importTargetId;
        }

        if ($hasMappedWorksheet) {
            $mappedRaw = $this->resolveCrossWorksheetValue($row, $lookupContext, $map, $worksheetDataMap);
            if ($mappedRaw !== null && $mappedRaw !== '') {
                $resolved = $this->resolveFacilityIdForImport($mappedRaw, $routeFacilityId, $presetFacilityId);
                if ($resolved !== null) {
                    return $resolved;
                }
            }

            if ($importTargetId) {
                return $importTargetId;
            }
        }

        if (isset($assignmentData['facility_id']) && $assignmentData['facility_id'] !== '' && is_numeric($assignmentData['facility_id'])) {
            return (int) $assignmentData['facility_id'];
        }

        $aliases = ['Facility', 'Facilities', 'Facility Name', 'Site'];
        $raw = $this->getSpreadsheetColumnValueAliases($row, $aliases);

        if ($raw === null || $raw === '') {
            $raw = $assignmentData['facility_id'] ?? null;
        }

        return $this->resolveFacilityIdForImport($raw, $routeFacilityId, $presetFacilityId);
    }

    protected function resolveDepartmentIdByName($name)
    {
        if ($name === null || $name === '') {
            return null;
        }
        if (is_numeric($name)) {
            $id = (int) $name;

            return \App\Models\Department::whereKey($id)->exists() ? $id : null;
        }
        $normalized = strtolower(trim((string) $name));
        $resolved = \App\Models\Department::whereRaw('LOWER(TRIM(name)) = ?', [$normalized])->first();
        if ($resolved) {
            return $resolved->id;
        }

        $resolved = \App\Models\Department::whereRaw('LOWER(TRIM(name)) LIKE ?', ['%' . $normalized . '%'])->first();

        return $resolved ? $resolved->id : null;
    }

    protected function normalizeColumnKey(string $key): string
    {
        return strtolower($this->normalizeImportLabel($key));
    }

    protected function normalizeCellValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $value = $value->getPlainText();
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                $normalized = $this->normalizeCellValue($item);
                if ($normalized !== null && $normalized !== '') {
                    return $normalized;
                }
            }

            return null;
        }
        if (is_string($value)) {
            $value = str_replace("\xc2\xa0", ' ', $value);

            return trim($value);
        }

        return $value;
    }

    /**
     * Read a spreadsheet cell by column header (case-insensitive, whitespace-safe).
     */
    protected function getSpreadsheetColumnValue(array $row, string $columnName): mixed
    {
        $target = $this->normalizeColumnKey($columnName);

        foreach ($row as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if ($this->normalizeColumnKey($key) === $target) {
                return $this->normalizeCellValue($value);
            }
        }

        return null;
    }

    protected function getSpreadsheetColumnValueAliases(array $row, array $columnNames): mixed
    {
        foreach ($columnNames as $columnName) {
            $value = $this->getSpreadsheetColumnValue($row, $columnName);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function getEmployeeNumFromRow(array $row): ?string
    {
        $empNum = $this->getSpreadsheetColumnValueAliases($row, ['Employee Num', 'Employee ID', 'employee_num']);

        return $empNum !== null && $empNum !== '' ? (string) $empNum : null;
    }

    protected function resolveEmployeeNum(array $row, array $employeeData): ?string
    {
        $empNum = $employeeData['employee_num'] ?? $this->getEmployeeNumFromRow($row);
        if ($empNum === null || $empNum === '') {
            return null;
        }

        return trim((string) $empNum);
    }

    protected function isBlankImportRow(array $row): bool
    {
        foreach ($row as $value) {
            $normalized = $this->normalizeCellValue($value);
            if ($normalized !== null && $normalized !== '') {
                return false;
            }
        }

        return true;
    }

    protected function assignmentColumnAliases(): array
    {
        return [
            'Facility', 'Facilities', 'Positions', 'Position', 'Job Title', 'Job',
            'Departments', 'Department', 'Dept', 'Reports To', 'Report To', 'Supervisor',
            'Effective Date', 'EffDt', 'effdt', 'Reg_Temp', 'Full Part PerDiem', 'Full Part Time',
        ];
    }

    protected function isAssignmentColumnName(string $columnName): bool
    {
        $target = $this->normalizeColumnKey($columnName);
        foreach ($this->assignmentColumnAliases() as $alias) {
            if ($this->normalizeColumnKey($alias) === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Worksheets referenced by bp_emp_job_data mappings (e.g. JobData) — merged first so
     * assignment columns are not overwritten by Profile or other sheets.
     *
     * @return array<int, string>
     */
    protected function preferredAssignmentWorksheetsFromMappings(array $mappings): array
    {
        return collect($mappings)
            ->filter(fn ($map) => ($map['table'] ?? '') === 'bp_emp_job_data')
            ->pluck('worksheet')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Merge assignment-related columns from all worksheets for the same employee_num.
     * Preset-mapped job worksheets (e.g. JobData) are merged last so Facility/Position/etc. win over Profile.
     *
     * @param  array<int, string>  $preferredWorksheets
     */
    protected function mergeRowsForAssignmentLookup(
        array $primaryRow,
        string $employeeNum,
        array $worksheetDataMap,
        array $preferredWorksheets = [],
    ): array {
        $merged = $primaryRow;

        if (empty($worksheetDataMap)) {
            return $merged;
        }

        $otherSheets = array_values(array_diff(array_keys($worksheetDataMap), $preferredWorksheets));
        $sheetOrder = array_values(array_unique(array_merge($otherSheets, $preferredWorksheets)));

        foreach ($sheetOrder as $sheetName) {
            $rows = $worksheetDataMap[$sheetName] ?? null;
            if (!is_array($rows)) {
                continue;
            }
            $isPreferredSheet = in_array($sheetName, $preferredWorksheets, true);

            foreach ($rows as $srcRow) {
                if (!is_array($srcRow)) {
                    continue;
                }
                $srcEmpNum = $this->getEmployeeNumFromRow($srcRow);
                if ($srcEmpNum === null || (string) $srcEmpNum !== (string) $employeeNum) {
                    continue;
                }
                foreach ($srcRow as $key => $value) {
                    if (!is_string($key)) {
                        continue;
                    }
                    $normalizedValue = $this->normalizeCellValue($value);
                    if ($normalizedValue === null || $normalizedValue === '') {
                        continue;
                    }
                    $primaryValue = array_key_exists($key, $merged) ? $this->normalizeCellValue($merged[$key]) : null;

                    if ($isPreferredSheet && $this->isAssignmentColumnName($key)) {
                        $merged[$key] = $normalizedValue;
                    } elseif ($primaryValue === null || $primaryValue === '') {
                        $merged[$key] = $normalizedValue;
                    }
                }
            }
        }

        return $merged;
    }

    /**
     * @return array<int, string>
     */
    protected function findWorksheetsWithAssignmentColumns(array $worksheetDataMap): array
    {
        $sheets = [];
        foreach ($worksheetDataMap as $sheetName => $rows) {
            if (!is_array($rows)) {
                continue;
            }
            foreach ($rows as $srcRow) {
                if (!is_array($srcRow)) {
                    continue;
                }
                if ($this->rowHasAssignmentColumns($srcRow)) {
                    $sheets[] = $sheetName;
                    break;
                }
            }
        }

        return array_values(array_unique($sheets));
    }

    /**
     * @return array{value: mixed, source: string|null}
     */
    protected function rawAssignmentLookupValue(
        array $row,
        array $assignmentData,
        string $targetCol,
        array $columnAliases,
        array $map = [],
        array $worksheetDataMap = [],
        array $employeeData = [],
    ): array {
        if (
            $targetCol === 'facility_id'
            && !empty($map['worksheet'])
            && !empty($map['worksheet_column'])
            && !empty($worksheetDataMap)
        ) {
            $mappedRaw = $this->resolveCrossWorksheetValue(
                $row,
                array_merge($employeeData, $assignmentData),
                $map,
                $worksheetDataMap
            );
            if ($mappedRaw !== null && $mappedRaw !== '') {
                return [
                    'value' => $mappedRaw,
                    'source' => 'worksheet:' . $map['worksheet'] . '.' . $map['worksheet_column'],
                ];
            }
        }

        foreach ($columnAliases as $alias) {
            $fromRow = $this->getSpreadsheetColumnValue($row, $alias);
            if ($fromRow !== null && $fromRow !== '') {
                return ['value' => $fromRow, 'source' => 'column:' . $alias];
            }
        }

        if (isset($assignmentData[$targetCol])) {
            $fromAssignment = $this->normalizeCellValue($assignmentData[$targetCol]);
            if ($fromAssignment !== null && $fromAssignment !== '') {
                return ['value' => $fromAssignment, 'source' => 'mapping:' . $targetCol];
            }
        }

        return ['value' => null, 'source' => null];
    }

    protected function getLookupSuggestions(string $type, string $raw): array
    {
        $normalized = strtolower(trim($raw));
        if ($normalized === '') {
            return [];
        }

        return match ($type) {
            'facility' => \App\Models\Facility::query()
                ->when(
                    \App\Models\Facility::whereRaw('LOWER(TRIM(name)) = ?', [$normalized])->doesntExist(),
                    fn ($q) => $q->whereRaw('LOWER(TRIM(name)) LIKE ?', ['%' . $normalized . '%'])
                )
                ->orderBy('name')
                ->limit(8)
                ->pluck('name')
                ->all(),
            'department' => \App\Models\Department::query()
                ->whereRaw('LOWER(TRIM(name)) LIKE ?', ['%' . $normalized . '%'])
                ->orderBy('name')
                ->limit(5)
                ->pluck('name')
                ->all(),
            'position' => \App\Models\Position::query()
                ->whereRaw('LOWER(TRIM(title)) LIKE ?', ['%' . $normalized . '%'])
                ->orderBy('title')
                ->limit(5)
                ->pluck('title')
                ->all(),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function logImportLookup(
        string $field,
        ?string $raw,
        ?string $source,
        mixed $resolvedId,
        string $lookupType,
        int $rowNumber,
        string $employeeNum,
        array $row
    ): array {
        $entry = [
            'field' => $field,
            'raw_value' => $raw,
            'source' => $source,
            'resolved_id' => $resolvedId,
            'status' => $resolvedId ? 'resolved' : ($raw === null || $raw === '' ? 'missing' : 'not_found'),
        ];

        if (!$resolvedId && $raw !== null && $raw !== '') {
            $entry['suggestions'] = $this->getLookupSuggestions($lookupType, (string) $raw);
            if ($field === 'facility_id' && $this->isAmbiguousShortFacilityFragment((string) $raw)) {
                $entry['hint'] = 'Use the full facility name exactly (not a partial name like "' . $raw . '").';
            }
        }

        $logContext = [
            'row' => $rowNumber,
            'employee_num' => $employeeNum,
            'lookup' => $entry,
            'spreadsheet_columns' => array_keys($row),
            'assignment_columns_present' => array_values(array_filter(array_keys($row), fn ($key) => is_string($key) && $this->isAssignmentColumnName($key))),
        ];

        if ($entry['status'] === 'resolved') {
            Log::info('Employee import lookup resolved', $logContext);
        } elseif ($entry['status'] === 'missing') {
            Log::info('Employee import lookup missing value', $logContext);
        } else {
            Log::warning('Employee import lookup could not resolve ID', $logContext);
        }

        return $entry;
    }

    /**
     * Resolve assignment FK fields from spreadsheet text using lookup tables.
     *
     * @return array<int, array<string, mixed>> Lookup debug entries for API response
     */
    protected function resolveAssignmentFromSpreadsheet(
        array $row,
        array &$assignmentData,
        int $rowNumber,
        string $employeeNum,
        ?int $routeFacilityId = null,
        ?int $presetFacilityId = null,
        array $facilityMap = [],
        array $worksheetDataMap = [],
        array $employeeData = [],
    ): array {
        $lookupDebug = [];

        $facilityResolved = $this->resolveAssignmentFacilityId(
            $row,
            $assignmentData,
            $facilityMap,
            $worksheetDataMap,
            $routeFacilityId,
            $presetFacilityId,
            $employeeData
        );
        $facilityLookup = $this->rawAssignmentLookupValue(
            $row,
            $assignmentData,
            'facility_id',
            ['Facility', 'Facilities', 'Facility Name', 'Site'],
            $facilityMap,
            $worksheetDataMap,
            $employeeData
        );
        $facilityRaw = $facilityLookup['value'];
        if ($facilityResolved !== null) {
            $assignmentData['facility_id'] = $facilityResolved;
        }
        $facilitySource = $facilityLookup['source'];
        if ($facilityRaw === null || $facilityRaw === '') {
            $facilitySource = $routeFacilityId ? 'route_facility_fallback' : null;
        } elseif ($facilityResolved === $routeFacilityId && $facilityRaw !== null && $facilityRaw !== '') {
            $byName = \App\Models\Facility::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $this->normalizeCellValue($facilityRaw)))])->exists();
            if (!$byName) {
                $facilitySource = ($facilitySource ? $facilitySource . ';' : '') . 'route_facility_fallback';
            }
        }
        $lookupDebug[] = $this->logImportLookup(
            'facility_id',
            $facilityRaw !== null ? (string) $facilityRaw : null,
            $facilitySource,
            $facilityResolved,
            'facility',
            $rowNumber,
            $employeeNum,
            $row
        );

        $positionLookup = $this->rawAssignmentLookupValue($row, $assignmentData, 'position_id', ['Positions', 'Position', 'Job Title', 'Job']);
        $positionRaw = $positionLookup['value'];
        $positionResolved = null;
        if ($positionRaw !== null && $positionRaw !== '') {
            $positionResolved = $this->resolvePositionIdByTitle($positionRaw);
            $assignmentData['position_id'] = $positionResolved;
        }
        $lookupDebug[] = $this->logImportLookup(
            'position_id',
            $positionRaw !== null ? (string) $positionRaw : null,
            $positionLookup['source'],
            $positionResolved,
            'position',
            $rowNumber,
            $employeeNum,
            $row
        );

        $departmentLookup = $this->rawAssignmentLookupValue($row, $assignmentData, 'dept_id', ['Departments', 'Department', 'Dept']);
        $departmentRaw = $departmentLookup['value'];
        $departmentResolved = null;
        if ($departmentRaw !== null && $departmentRaw !== '') {
            $departmentResolved = $this->resolveDepartmentIdByName($departmentRaw);
            $assignmentData['dept_id'] = $departmentResolved;
        }
        $lookupDebug[] = $this->logImportLookup(
            'dept_id',
            $departmentRaw !== null ? (string) $departmentRaw : null,
            $departmentLookup['source'],
            $departmentResolved,
            'department',
            $rowNumber,
            $employeeNum,
            $row
        );

        $reportsToLookup = $this->rawAssignmentLookupValue($row, $assignmentData, 'reports_to', ['Reports To', 'Report To', 'Supervisor']);
        $reportsToRaw = $reportsToLookup['value'];
        $reportsToResolved = null;
        if ($reportsToRaw !== null && $reportsToRaw !== '') {
            $reportsToResolved = $this->resolvePositionIdByTitle($reportsToRaw);
            $assignmentData['reports_to'] = $reportsToResolved;
        }
        $lookupDebug[] = $this->logImportLookup(
            'reports_to',
            $reportsToRaw !== null ? (string) $reportsToRaw : null,
            $reportsToLookup['source'],
            $reportsToResolved,
            'position',
            $rowNumber,
            $employeeNum,
            $row
        );

        if (!empty($assignmentData['position_id'])) {
            $position = \App\Models\Position::find($assignmentData['position_id']);
            if ($position) {
                if (empty($assignmentData['dept_id']) && $position->department_id) {
                    $assignmentData['dept_id'] = $position->department_id;
                }
                if (empty($assignmentData['reports_to']) && $position->reports_to_position_id) {
                    $assignmentData['reports_to'] = $position->reports_to_position_id;
                }
            }
        }

        $effectiveDateLookup = $this->rawAssignmentLookupValue($row, $assignmentData, 'effdt', ['Effective Date', 'EffDt', 'effdt']);
        $effectiveDateRaw = $effectiveDateLookup['value'];
        if ($effectiveDateRaw !== null && $effectiveDateRaw !== '') {
            $assignmentData['effdt'] = $this->convertExcelDate($effectiveDateRaw);
        }

        if (array_key_exists('reg_temp', $assignmentData)) {
            $assignmentData['reg_temp'] = $this->normalizeRegTemp($assignmentData['reg_temp']);
        }
        if (array_key_exists('full_part_time', $assignmentData)) {
            $assignmentData['full_part_time'] = $this->normalizeFullPartTime($assignmentData['full_part_time']);
        }

        Log::info('Employee import assignment resolution summary', [
            'row' => $rowNumber,
            'employee_num' => $employeeNum,
            'resolved' => [
                'facility_id' => $assignmentData['facility_id'] ?? null,
                'position_id' => $assignmentData['position_id'] ?? null,
                'dept_id' => $assignmentData['dept_id'] ?? null,
                'reports_to' => $assignmentData['reports_to'] ?? null,
            ],
            'lookups' => $lookupDebug,
        ]);

        return $lookupDebug;
    }

    protected function buildAssignmentPayload(
        array $assignmentData,
        string $empId,
        int $userId,
        ?int $routeFacilityId = null,
        ?int $presetFacilityId = null,
    ): array {
        $effdtRaw = $this->normalizeCellValue($assignmentData['effdt'] ?? null);
        $effdt = $effdtRaw ? $this->convertExcelDate($effdtRaw) : date('Y-m-d');
        if (!is_string($effdt) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $effdt)) {
            $effdt = date('Y-m-d');
        }

        $effseq = (int) $this->normalizeCellValue($assignmentData['effseq'] ?? 0);

        $payload = [
            'employee_num' => $empId,
            'effdt' => $effdt,
            'effseq' => $effseq,
            'created_by' => $userId,
            'updated_by' => $userId,
            'reg_temp' => $this->normalizeRegTemp($this->normalizeCellValue($assignmentData['reg_temp'] ?? 'r')),
            'full_part_time' => $this->normalizeFullPartTime($this->normalizeCellValue($assignmentData['full_part_time'] ?? 'ft')),
        ];

        $importTargetId = $this->importTargetFacilityId($routeFacilityId, $presetFacilityId);

        foreach (['facility_id', 'dept_id', 'position_id', 'reports_to', 'bargaining_unit_id'] as $column) {
            $value = $this->normalizeCellValue($assignmentData[$column] ?? null);
            if ($value === null || $value === '') {
                continue;
            }

            if ($column === 'facility_id') {
                if ($importTargetId) {
                    $payload[$column] = $importTargetId;
                } elseif (is_numeric($value)) {
                    $payload[$column] = (int) $value;
                } else {
                    $resolved = $this->resolveFacilityIdForImport($value, $routeFacilityId, $presetFacilityId);
                    if ($resolved !== null) {
                        $payload[$column] = $resolved;
                    }
                }
                continue;
            }

            if (is_numeric($value)) {
                $payload[$column] = (int) $value;
            }
        }

        foreach (['hourly_status_id', 'std_hrs_week', 'compensation_rate_id', 'amount'] as $column) {
            if (!array_key_exists($column, $assignmentData)) {
                continue;
            }
            $mapped = $this->mapJobField($column, $assignmentData[$column]);
            if ($mapped !== null && $mapped !== '') {
                $payload[$column] = $mapped;
            }
        }

        return $payload;
    }

    /**
     * Update existing assignment when employee_num + effdt + facility_id + position_id match;
     * otherwise insert a new row (next effseq for that employee/effdt).
     *
     * @return array{assignment: \App\Models\BPEmpJobData, action: string}
     */
    protected function upsertEmployeeAssignment(array $payload): array
    {
        $query = \App\Models\BPEmpJobData::query()
            ->where('employee_num', $payload['employee_num'])
            ->where('effdt', $payload['effdt']);

        if (!empty($payload['facility_id'])) {
            $query->where('facility_id', $payload['facility_id']);
        } else {
            $query->whereNull('facility_id');
        }

        if (!empty($payload['position_id'])) {
            $query->where('position_id', $payload['position_id']);
        } else {
            $query->whereNull('position_id');
        }

        $existing = $query->first();

        if (
            !$existing
            && !empty($payload['facility_id'])
            && !empty($payload['employee_num'])
            && !empty($payload['effdt'])
        ) {
            $fallbackQuery = \App\Models\BPEmpJobData::query()
                ->where('employee_num', $payload['employee_num'])
                ->where('effdt', $payload['effdt']);

            if (!empty($payload['position_id'])) {
                $fallbackQuery->where('position_id', $payload['position_id']);
            } else {
                $fallbackQuery->whereNull('position_id');
            }

            $existing = $fallbackQuery->orderByDesc('effseq')->first();
        }

        if (
            $existing
            && !empty($payload['facility_id'])
            && (int) $existing->facility_id !== (int) $payload['facility_id']
        ) {
            Log::info('Import: existing job row is for a different facility; inserting new assignment row', [
                'employee_num' => $payload['employee_num'],
                'existing_facility_id' => $existing->facility_id,
                'import_facility_id' => $payload['facility_id'],
                'effdt' => $payload['effdt'],
            ]);
            $existing = null;
        }

        if ($this->importLogRecorder->isActive()) {
            $assignment = $this->importLogRecorder->trackUpsert(
                'bp_emp_job_data',
                $existing,
                function () use ($existing, $payload) {
                    if ($existing) {
                        $updateData = $payload;
                        $updateData['effseq'] = $existing->effseq;
                        $existing->fill($updateData);
                        $existing->save();

                        return $existing;
                    }

                    $maxEffseq = \App\Models\BPEmpJobData::query()
                        ->where('employee_num', $payload['employee_num'])
                        ->where('effdt', $payload['effdt'])
                        ->max('effseq');

                    $payload['effseq'] = $maxEffseq !== null ? ((int) $maxEffseq + 1) : 0;

                    return \App\Models\BPEmpJobData::create($payload);
                },
                $payload['employee_num'] ?? null
            );

            return [
                'assignment' => $assignment,
                'action' => $existing ? 'updated' : 'inserted',
            ];
        }

        if ($existing) {
            $updateData = $payload;
            $updateData['effseq'] = $existing->effseq;
            $existing->fill($updateData);
            $existing->save();

            return ['assignment' => $existing, 'action' => 'updated'];
        }

        $maxEffseq = \App\Models\BPEmpJobData::query()
            ->where('employee_num', $payload['employee_num'])
            ->where('effdt', $payload['effdt'])
            ->max('effseq');

        $payload['effseq'] = $maxEffseq !== null ? ((int) $maxEffseq + 1) : 0;

        return [
            'assignment' => \App\Models\BPEmpJobData::create($payload),
            'action' => 'inserted',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function legacyEmployeeJobColumnMap(): array
    {
        return [
            'hourly_status_id' => 'hourly_status_id',
            'std_hrs_week' => 'std_hrs_week',
            'compensation_rate_id' => 'compensation_rate_id',
            'amount' => 'amount',
        ];
    }

    protected function mapJobField(string $column, mixed $value): mixed
    {
        $value = $this->normalizeCellValue($value);
        if ($value === null || $value === '') {
            return null;
        }

        if ($column === 'hourly_status_id') {
            return $this->resolveSelectOptionIdForType('Hourly Status', $value);
        }

        if ($column === 'compensation_rate_id') {
            return $this->resolveSelectOptionIdForType('Compensation Rate', $value);
        }

        if ($column === 'std_hrs_week') {
            return is_numeric($value) ? (int) $value : $value;
        }

        if ($column === 'amount') {
            return $this->parseDecimalImportValue($value);
        }

        return $value;
    }

    protected function resolveSelectOptionIdForType(string $optionTypeName, mixed $value): ?int
    {
        $value = $this->normalizeCellValue($value);
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $typeId = \Illuminate\Support\Facades\DB::table('optionstypes')
            ->where('name', $optionTypeName)
            ->value('id');

        if (!$typeId) {
            return null;
        }

        $option = SelectOption::query()
            ->where('type_id', $typeId)
            ->where('name', $value)
            ->first();

        if ($option) {
            return (int) $option->id;
        }

        $nextSort = (int) (SelectOption::query()->where('type_id', $typeId)->max('sort_order') ?? 0) + 1;

        return (int) SelectOption::create([
            'name' => $value,
            'value' => $value,
            'type_id' => $typeId,
            'isActive' => 1,
            'sort_order' => $nextSort,
        ])->id;
    }

    /**
     * Parse spreadsheet decimals that may include currency symbols, commas, or percent signs.
     */
    protected function parseDecimalImportValue(mixed $value): ?float
    {
        $value = $this->normalizeCellValue($value);
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        $cleaned = preg_replace('/[^\d.\-+]/', '', $str) ?? '';
        if ($cleaned === '' || !is_numeric($cleaned)) {
            return null;
        }

        return (float) $cleaned;
    }

    /**
     * @return array<string, string>
     */
    protected function legacyEmployeeTaxColumnMap(): array
    {
        return [
            'federal_tax_data_id' => 'fed_tax_data',
            'state_tax_data_id' => 'state_tax_data',
            'local_tax_data_id' => 'local_withholding_allowance',
        ];
    }

    protected function mapTaxField(string $column, mixed $value): mixed
    {
        $value = $this->normalizeCellValue($value);
        if ($value === null || $value === '') {
            return null;
        }

        if (in_array($column, ['fed_tax_data', 'state_tax_data'], true)) {
            return $this->normalizeTaxStatusValue($value);
        }

        if ($column === 'resident') {
            $normalized = strtoupper(trim((string) $value));

            return in_array($normalized, ['Y', 'N'], true) ? $normalized : null;
        }

        if (in_array($column, [
            'fed_withholding_allowance',
            'state_withholding_allowance1',
            'local_withholding_allowance',
            'addl_withholding_percentage1',
            'addl_withholding_amount1',
            'addl_withholding_percentage2',
            'addl_withholding_amount2',
        ], true)) {
            return $this->parseDecimalImportValue($value);
        }

        if ($column === 'effdt') {
            return $this->convertExcelDate($value);
        }

        if ($column === 'effseq') {
            return (int) $value;
        }

        return $value;
    }

    protected function normalizeTaxStatusValue(mixed $value): ?string
    {
        if (is_numeric($value)) {
            $intValue = (int) $value;
            if (in_array($intValue, [1, 2], true)) {
                return (string) $intValue;
            }
            $option = SelectOption::find($intValue);
            if ($option) {
                $value = $option->name;
            }
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'single', 's'], true)) {
            return '1';
        }
        if (in_array($normalized, ['2', 'married', 'm'], true)) {
            return '2';
        }

        return null;
    }

    protected function buildTaxPayload(array $taxData, string $empId): array
    {
        $effdtRaw = $this->normalizeCellValue($taxData['effdt'] ?? null);
        $effdt = $effdtRaw ? $this->convertExcelDate($effdtRaw) : date('Y-m-d');
        if (!is_string($effdt) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $effdt)) {
            $effdt = date('Y-m-d');
        }

        $payload = [
            'employee_num' => $empId,
            'effdt' => $effdt,
            'effseq' => (int) $this->normalizeCellValue($taxData['effseq'] ?? 0),
            'resident_state' => $this->normalizeCellValue($taxData['resident_state'] ?? 'CA') ?: 'CA',
        ];

        foreach ([
            'fed_tax_data',
            'fed_withholding_allowance',
            'state_tax_data',
            'state_withholding_allowance1',
            'resident',
            'local_withholding_allowance',
            'locality',
            'county',
            'addl_withholding_percentage1',
            'addl_withholding_amount1',
            'addl_withholding_percentage2',
            'addl_withholding_amount2',
        ] as $column) {
            if (!array_key_exists($column, $taxData)) {
                continue;
            }
            $mapped = $this->mapTaxField($column, $taxData[$column]);
            if ($mapped !== null && $mapped !== '') {
                $payload[$column] = $mapped;
            }
        }

        return $payload;
    }

    protected function shouldUpsertTaxData(array $payload): bool
    {
        foreach ([
            'fed_tax_data',
            'fed_withholding_allowance',
            'state_tax_data',
            'state_withholding_allowance1',
            'resident',
            'local_withholding_allowance',
            'locality',
            'county',
            'addl_withholding_percentage1',
            'addl_withholding_amount1',
            'addl_withholding_percentage2',
            'addl_withholding_amount2',
        ] as $column) {
            if (!empty($payload[$column])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{tax: \App\Models\BPEmpTaxData, action: string}
     */
    protected function upsertEmployeeTaxData(array $payload): array
    {
        $existing = \App\Models\BPEmpTaxData::query()
            ->where('employee_num', $payload['employee_num'])
            ->where('effdt', $payload['effdt'])
            ->orderByDesc('effseq')
            ->first();

        if ($this->importLogRecorder->isActive()) {
            $tax = $this->importLogRecorder->trackUpsert(
                'bp_emp_tax_data',
                $existing,
                function () use ($existing, $payload) {
                    if ($existing) {
                        $updateData = $payload;
                        $updateData['effseq'] = $existing->effseq;
                        $existing->fill($updateData);
                        $existing->save();

                        return $existing;
                    }

                    $maxEffseq = \App\Models\BPEmpTaxData::query()
                        ->where('employee_num', $payload['employee_num'])
                        ->where('effdt', $payload['effdt'])
                        ->max('effseq');

                    $payload['effseq'] = $maxEffseq !== null ? ((int) $maxEffseq + 1) : 0;

                    return \App\Models\BPEmpTaxData::create($payload);
                },
                $payload['employee_num'] ?? null
            );

            return [
                'tax' => $tax,
                'action' => $existing ? 'updated' : 'inserted',
            ];
        }

        if ($existing) {
            $updateData = $payload;
            $updateData['effseq'] = $existing->effseq;
            $existing->fill($updateData);
            $existing->save();

            return ['tax' => $existing, 'action' => 'updated'];
        }

        $maxEffseq = \App\Models\BPEmpTaxData::query()
            ->where('employee_num', $payload['employee_num'])
            ->where('effdt', $payload['effdt'])
            ->max('effseq');

        $payload['effseq'] = $maxEffseq !== null ? ((int) $maxEffseq + 1) : 0;

        return [
            'tax' => \App\Models\BPEmpTaxData::create($payload),
            'action' => 'inserted',
        ];
    }

    protected function rowHasAssignmentColumns(array $row): bool
    {
        $columns = [
            ['Facility', 'Facilities'],
            ['Positions', 'Position', 'Job Title', 'Job'],
            ['Departments', 'Department', 'Dept'],
            ['Reports To', 'Report To', 'Supervisor'],
            ['Effective Date', 'EffDt', 'effdt'],
            ['Hourly Status'],
            ['Std. Hrs./Week', 'Std Hrs/Week', 'std_hrs_week'],
            ['Compensation Rate'],
            ['Amount'],
        ];
        foreach ($columns as $aliases) {
            $value = $this->getSpreadsheetColumnValueAliases($row, $aliases);
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    protected function shouldUpsertAssignment(array $assignmentData): bool
    {
        return !empty($assignmentData['position_id'])
            || !empty($assignmentData['facility_id'])
            || !empty($assignmentData['dept_id'])
            || !empty($assignmentData['hourly_status_id'])
            || !empty($assignmentData['std_hrs_week'])
            || !empty($assignmentData['compensation_rate_id'])
            || !empty($assignmentData['amount']);
    }

    protected function normalizeRegTemp($value)
    {
        if ($value === null || $value === '') {
            return 'r';
        }
        $normalized = strtolower(trim((string) $value));
        $compact = str_replace([' ', '-', '_'], '', $normalized);
        if (in_array($normalized, ['r', 'reg', 'regular'], true) || $compact === 'regtemp' || str_starts_with($compact, 'reg')) {
            return 'r';
        }
        if (in_array($normalized, ['t', 'temp', 'temporary'], true) || $compact === 'temporary' || str_starts_with($compact, 'temp')) {
            return 't';
        }

        return $normalized;
    }

    protected function normalizeFullPartTime($value)
    {
        if ($value === null || $value === '') {
            return 'ft';
        }
        $normalized = strtolower(trim((string) $value));
        $compact = str_replace([' ', '-', '_', '/'], '', $normalized);
        if (in_array($compact, ['ft', 'fulltime', 'full'], true)) {
            return 'ft';
        }
        if (in_array($compact, ['pt', 'parttime', 'part'], true)) {
            return 'pt';
        }
        if (in_array($compact, ['pd', 'perdiem'], true) || str_contains($compact, 'perdiem')) {
            return 'pd';
        }
        if (str_contains($compact, 'part')) {
            return 'pt';
        }

        return $compact;
    }

    protected function resolveCrossWorksheetValue(array $row, array $employeeData, array $map, array $worksheetDataMap)
    {
        if (!isset($worksheetDataMap[$map['worksheet']])) {
            return $this->getSpreadsheetColumnValue($row, $map['worksheet_column']);
        }

        $empId = $employeeData['employee_num']
            ?? $this->getSpreadsheetColumnValue($row, 'Employee Num')
            ?? $this->getSpreadsheetColumnValue($row, 'Employee ID')
            ?? $this->getSpreadsheetColumnValue($row, 'employee_num');
        foreach ($worksheetDataMap[$map['worksheet']] as $srcRow) {
            $srcEmpId = $this->getSpreadsheetColumnValue($srcRow, 'Employee Num')
                ?? $this->getSpreadsheetColumnValue($srcRow, 'Employee ID')
                ?? $this->getSpreadsheetColumnValue($srcRow, 'employee_num');
            if ($srcEmpId == $empId) {
                return $this->getSpreadsheetColumnValue($srcRow, $map['worksheet_column']);
            }
        }

        return null;
    }

    protected function mapEmployeeField(string $column, $value)
    {
        if (in_array($column, ['effdt_of_membership', 'dob', 'original_hire_dt', 'badge_eff_dt'], true) && $value !== null && $value !== '') {
            $value = $this->convertExcelDate($value);
        }
        if (is_null($value) || is_numeric($value)) {
            return $value;
        }
        try {
            $type = Schema::getColumnType('bp_employees', $column);
        } catch (\Throwable $e) {
            $type = null;
        }
        if ($type === 'integer' || $type === 'bigint' || $type === 'smallint') {
            $option = SelectOption::where('name', $value)->first();
            if ($option) {
                return $option->id;
            }

            return SelectOption::create([
                'name' => $value,
                'type_id' => 1,
                'isActive' => 1,
            ])->id;
        }

        return $value;
    }

    /**
     * Convert a date string in MM/DD/YYYY or M/D/YYYY to YYYY-MM-DD. Returns null if not matched.
     */
    protected function convertExcelDate($value)
    {
        $value = $this->normalizeCellValue($value);
        if ($value === null || $value === '') {
            return $value;
        }
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);

                return $date->format('Y-m-d');
            } catch (\Throwable $e) {
                return $value;
            }
        }
        if (is_string($value) && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }
        return $value;
    }

    /**
     * Import mapped data to bp_employees, check for duplicates, and handle upserts.
     * Expects: mappings, data, confirm_overwrite (bool), facility
     */
    public function importData(Request $request, $facility)
    {
        if (!ImportMappingPresetAccess::canUse()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to import facility data.',
            ], 403);
        }

        $mappings = $request->input('mappings', []);
        $dataRows = $request->input('data', []);
        $confirmOverwrite = $request->boolean('confirm_overwrite', false);

        if (empty($mappings) || !is_array($dataRows) || count($dataRows) === 0) {
            return response()->json([
                'success' => false,
                'error' => 'No column mappings or spreadsheet rows were provided.',
                'message' => 'Select a mapping preset (or create one) before importing, and ensure your Excel file contains data rows.',
            ], 422);
        }
        $userId = Auth::id() ?? 1;
        $routeFacilityId = is_numeric($facility) ? (int) $facility : null;
        $presetFacilityId = $this->importPresetFacilityId($request);
        $facilityMap = collect($mappings)->first(
            fn ($map) => ($map['table'] ?? '') === 'bp_emp_job_data' && ($map['table_column'] ?? '') === 'facility_id'
        ) ?? [];
        $preferredAssignmentWorksheets = $this->preferredAssignmentWorksheetsFromMappings($mappings);

        $this->importLogRecorder->begin($request, $routeFacilityId);

        $duplicates = [];
        $imported = [];
        $invalidRows = [];
        $allowedGenders = ['M', 'F', 'O', 'N', null, ''];
        $detailedFailures = [];
        $importResults = [];

        $worksheetDataMap = [];
        if (!empty($request->input('worksheets'))) {
            foreach ($request->input('worksheets') as $ws) {
                $worksheetDataMap[$ws['name']] = $ws['data'];
            }
        }

        $assignmentWorksheets = $this->findWorksheetsWithAssignmentColumns($worksheetDataMap);
        Log::info('Employee import worksheets detected', [
            'all_worksheets' => array_keys($worksheetDataMap),
            'worksheets_with_assignment_columns' => $assignmentWorksheets,
        ]);
        if (empty($assignmentWorksheets) && !empty($worksheetDataMap)) {
            Log::warning('Employee import: no worksheet contains assignment columns (Facility, Positions, Departments, Reports To). Assignment FK fields will only use route facility fallback.');
        }

        foreach ($dataRows as $idx => $row) {
            if ($this->isBlankImportRow($row)) {
                continue;
            }

            $rowFailures = [];
            $employeeData = [];
            $assignmentData = [];
            $addressData = [];
            $phoneData = [];
            $taxData = [];
            $legacyTaxColumns = $this->legacyEmployeeTaxColumnMap();
            $legacyJobColumns = $this->legacyEmployeeJobColumnMap();
            foreach ($mappings as $map) {
                $sourceVal = $this->getSpreadsheetColumnValue($row, $map['worksheet_column']);
                $targetTable = $map['table'];
                $targetCol = $map['table_column'];
                $sourceCol = $map['worksheet_column'];
                $sourceSheet = $map['worksheet'] ?? '';

                if ($targetTable === 'bp_employees') {
                    if (isset($legacyTaxColumns[$targetCol])) {
                        $taxCol = $legacyTaxColumns[$targetCol];
                        $taxData[$taxCol] = $this->mapTaxField($taxCol, $sourceVal);
                        continue;
                    }
                    if (isset($legacyJobColumns[$targetCol])) {
                        $jobCol = $legacyJobColumns[$targetCol];
                        $assignmentData[$jobCol] = $this->mapJobField($jobCol, $sourceVal);
                        continue;
                    }

                    $sourceVal = !empty($worksheetDataMap)
                        ? $this->resolveCrossWorksheetValue($row, $employeeData, $map, $worksheetDataMap)
                        : null;
                    if ($sourceVal === null || $sourceVal === '') {
                        $sourceVal = $this->getSpreadsheetColumnValue($row, $sourceCol);
                    }

                    try {
                        $employeeData[$targetCol] = $this->mapEmployeeField($targetCol, $sourceVal);
                    } catch (\Throwable $e) {
                        $rowFailures[] = [
                            'row' => $idx + 2,
                            'source_worksheet' => $sourceSheet,
                            'source_column' => $sourceCol,
                            'target_table' => $targetTable,
                            'target_column' => $targetCol,
                            'value' => $sourceVal,
                            'reason' => 'Failed to create/select option: ' . $e->getMessage(),
                        ];
                    }
                } elseif ($targetTable === 'bp_emp_job_data') {
                    $empNumForAssignment = $employeeData['employee_num'] ?? $this->getEmployeeNumFromRow($row);
                    if ($targetCol === 'facility_id') {
                        $enrichedForFacility = ($empNumForAssignment && !empty($worksheetDataMap))
                            ? $this->mergeRowsForAssignmentLookup(
                                $row,
                                (string) $empNumForAssignment,
                                $worksheetDataMap,
                                $preferredAssignmentWorksheets
                            )
                            : $row;
                        $resolvedFacilityId = $this->resolveAssignmentFacilityId(
                            $enrichedForFacility,
                            $assignmentData,
                            $map,
                            $worksheetDataMap,
                            $routeFacilityId,
                            $presetFacilityId,
                            $employeeData
                        );
                        if ($resolvedFacilityId !== null) {
                            $assignmentData[$targetCol] = $resolvedFacilityId;
                        }
                    } else {
                        $value = $sourceVal;
                        if ($empNumForAssignment && !empty($worksheetDataMap)) {
                            $enrichedRow = $this->mergeRowsForAssignmentLookup(
                                $row,
                                (string) $empNumForAssignment,
                                $worksheetDataMap,
                                $preferredAssignmentWorksheets
                            );
                            $value = $this->getSpreadsheetColumnValue($enrichedRow, $sourceCol)
                                ?? $this->resolveCrossWorksheetValue($enrichedRow, $employeeData, $map, $worksheetDataMap)
                                ?? $value;
                        }
                        if (in_array($targetCol, ['hourly_status_id', 'std_hrs_week', 'compensation_rate_id', 'amount'], true)) {
                            $assignmentData[$targetCol] = $this->mapJobField($targetCol, $value);
                        } else {
                            $assignmentData[$targetCol] = $value;
                        }
                    }
                } elseif ($targetTable === 'bp_emp_tax_data') {
                    $empNumForTax = $employeeData['employee_num'] ?? $this->getEmployeeNumFromRow($row);
                    $value = $sourceVal;
                    if ($empNumForTax && !empty($worksheetDataMap)) {
                        $enrichedRow = $this->mergeRowsForAssignmentLookup(
                            $row,
                            (string) $empNumForTax,
                            $worksheetDataMap,
                            $preferredAssignmentWorksheets
                        );
                        $value = $this->getSpreadsheetColumnValue($enrichedRow, $sourceCol)
                            ?? $this->resolveCrossWorksheetValue($enrichedRow, $employeeData, $map, $worksheetDataMap)
                            ?? $value;
                    }
                    $taxData[$targetCol] = $this->mapTaxField($targetCol, $value);
                } elseif ($targetTable === 'bp_emp_addresses') {
                    $addressData[$targetCol] = $this->resolveCrossWorksheetValue($row, $employeeData, $map, $worksheetDataMap);
                } elseif ($targetTable === 'bp_emp_phones') {
                    $phoneData[$targetCol] = $this->resolveCrossWorksheetValue($row, $employeeData, $map, $worksheetDataMap);
                }
            }

            $empNum = $this->resolveEmployeeNum($row, $employeeData);
            if ($empNum === null) {
                Log::info('Employee import skipped row without employee_num', [
                    'row' => $idx + 2,
                    'spreadsheet_columns' => array_keys($row),
                ]);
                $importResults[] = [
                    'row' => $idx + 2,
                    'employee_num' => null,
                    'action' => 'skipped',
                    'reason' => 'Missing or empty employee_num — row skipped',
                ];
                continue;
            }
            $employeeData['employee_num'] = $empNum;

            // Always set employee_num on assignment if present
            if (!empty($assignmentData)) {
                $assignmentData['employee_num'] = $empNum;
            }
            if (array_key_exists('gender', $employeeData)) {
                $gender = $employeeData['gender'];
                if (!in_array($gender, $allowedGenders, true)) {
                    $rowFailures[] = [
                        'row' => $idx + 2,
                        'reason' => 'Invalid gender value: ' . $gender,
                        'mappings' => $mappings,
                        'row_data' => $row
                    ];
                    $detailedFailures[] = $rowFailures;
                    $importResults[] = [
                        'row' => $idx + 2,
                        'employee_num' => $employeeData['employee_num'],
                        'action' => 'skipped',
                        'reason' => 'Invalid gender value: ' . $gender
                    ];
                    continue;
                }
            }
            if (!empty($rowFailures)) {
                $detailedFailures[] = $rowFailures;
                continue;
            }

            try {
                $empId = $empNum;
                $existing = \App\Models\BPEmployee::where('employee_num', $empId)->first();

                if ($existing && !$confirmOverwrite) {
                    $duplicates[] = $empId;
                } else {
                    $this->importLogRecorder->trackUpsert(
                        'bp_employees',
                        $existing,
                        fn () => \App\Models\BPEmployee::updateOrCreate(
                            ['employee_num' => $empId],
                            $employeeData
                        ),
                        $empId
                    );
                }

                $assignmentAction = null;
                $assignmentReason = null;
                $lookupDebug = [];

                $enrichedAssignmentRow = !empty($worksheetDataMap)
                    ? $this->mergeRowsForAssignmentLookup($row, $empId, $worksheetDataMap, $preferredAssignmentWorksheets)
                    : $row;

                if (!empty($assignmentData) || $this->rowHasAssignmentColumns($enrichedAssignmentRow)) {
                    $assignmentData['employee_num'] = $empId;
                    if ($enrichedAssignmentRow !== $row) {
                        Log::info('Employee import merged assignment columns from other worksheet(s)', [
                            'row' => $idx + 2,
                            'employee_num' => $empId,
                            'primary_columns' => array_keys($row),
                            'merged_assignment_columns' => array_values(array_filter(
                                array_keys($enrichedAssignmentRow),
                                fn ($key) => is_string($key) && $this->isAssignmentColumnName($key)
                            )),
                        ]);
                    }
                    $lookupDebug = $this->resolveAssignmentFromSpreadsheet(
                        $enrichedAssignmentRow,
                        $assignmentData,
                        $idx + 2,
                        $empId,
                        $routeFacilityId,
                        $presetFacilityId,
                        $facilityMap,
                        $worksheetDataMap,
                        $employeeData
                    );

                    if ($this->shouldUpsertAssignment($assignmentData)) {
                        $assignmentPayload = $this->buildAssignmentPayload(
                            $assignmentData,
                            $empId,
                            $userId,
                            $routeFacilityId,
                            $presetFacilityId
                        );
                        $assignmentUpsert = $this->upsertEmployeeAssignment($assignmentPayload);
                        $assignmentAction = $assignmentUpsert['action'];
                        $failedLookups = array_values(array_filter($lookupDebug, fn ($entry) => ($entry['status'] ?? '') === 'not_found'));
                        if (!empty($failedLookups)) {
                            $assignmentReason = 'Assignment saved but could not resolve: ' . collect($failedLookups)
                                ->map(fn ($entry) => $entry['field'] . '="' . ($entry['raw_value'] ?? '') . '"')
                                ->implode(', ');
                        }
                    } else {
                        $assignmentAction = 'skipped';
                        $failedLookups = array_values(array_filter($lookupDebug, fn ($entry) => in_array($entry['status'] ?? '', ['not_found', 'missing'], true)));
                        $assignmentReason = 'Could not resolve position/facility/department from spreadsheet values.';
                        if (!empty($failedLookups)) {
                            $assignmentReason .= ' ' . collect($failedLookups)
                                ->map(fn ($entry) => $entry['field'] . ': ' . ($entry['raw_value'] ?? '(empty)') . ' [' . ($entry['status'] ?? '') . ']')
                                ->implode('; ');
                        }
                        Log::warning('Import assignment skipped', [
                            'employee_num' => $empId,
                            'row' => $idx + 2,
                            'lookups' => $lookupDebug,
                            'assignment_data' => $assignmentData,
                        ]);
                    }
                }

                $existingAssignment = \App\Models\BPEmpJobData::where('employee_num', $empId)->exists();

                if (!empty($addressData)) {
                    $addressData['employee_num'] = $empId;
                    $addressData['address_type'] = strtoupper((string) ($addressData['address_type'] ?? 'H'));
                    if (!in_array($addressData['address_type'], ['H', 'W', 'O', 'M'], true)) {
                        $addressData['address_type'] = 'H';
                    }
                    $addressData['effdt'] = date('Y-m-d');
                    $addressData['effseq'] = 0;
                    $addressData['country'] = 'USA';
                    $addressData['is_primary'] = \App\Models\BPEmpAddress::PRIMARY_YES;
                    if (!empty($addressData['address1'])) {
                        $existingAddress = \App\Models\BPEmpAddress::query()
                            ->where('employee_num', $empId)
                            ->where('effdt', $addressData['effdt'])
                            ->where('effseq', $addressData['effseq'])
                            ->first();
                        $this->importLogRecorder->trackUpsert(
                            'bp_emp_addresses',
                            $existingAddress,
                            fn () => \App\Models\BPEmpAddress::updateOrCreate(
                                [
                                    'employee_num' => $empId,
                                    'effdt' => $addressData['effdt'],
                                    'effseq' => $addressData['effseq'],
                                ],
                                $addressData
                            ),
                            $empId
                        );
                    }
                }

                if (!empty($phoneData)) {
                    $phoneData['employee_num'] = $empId;
                    $phoneData['phone_type'] = $phoneData['phone_type'] ?? 'M';
                    $phoneData['effdt'] = $this->convertExcelDate($phoneData['effdt'] ?? null) ?? date('Y-m-d');
                    $phoneData['effseq'] = (int) ($phoneData['effseq'] ?? 0);
                    $phonePrimary = $phoneData['is_primary'] ?? null;
                    $phoneData['is_primary'] = in_array($phonePrimary, [\App\Models\BPEmpPhone::PRIMARY_NO, '0', 0, false, 'n', 'N'], true)
                        ? \App\Models\BPEmpPhone::PRIMARY_NO
                        : \App\Models\BPEmpPhone::PRIMARY_YES;
                    if (!empty($phoneData['phone_number'])) {
                        $existingPhone = \App\Models\BPEmpPhone::query()
                            ->where('employee_num', $empId)
                            ->where('phone_type', $phoneData['phone_type'])
                            ->where('effdt', $phoneData['effdt'])
                            ->where('effseq', $phoneData['effseq'])
                            ->first();
                        $this->importLogRecorder->trackUpsert(
                            'bp_emp_phones',
                            $existingPhone,
                            fn () => \App\Models\BPEmpPhone::updateOrCreate(
                                [
                                    'employee_num' => $empId,
                                    'phone_type' => $phoneData['phone_type'],
                                    'effdt' => $phoneData['effdt'],
                                    'effseq' => $phoneData['effseq'],
                                ],
                                $phoneData
                            ),
                            $empId
                        );
                    }
                }

                if (!empty($taxData)) {
                    $taxPayload = $this->buildTaxPayload($taxData, $empId);
                    if ($this->shouldUpsertTaxData($taxPayload)) {
                        $this->upsertEmployeeTaxData($taxPayload);
                    }
                }

                $imported[] = $empId;
                $importResults[] = [
                    'row' => $idx + 2,
                    'employee_num' => $empId,
                    'action' => $existing ? 'updated' : 'inserted',
                    'reason' => null,
                    'assignment_action' => $assignmentAction,
                    'assignment_reason' => $assignmentReason,
                    'has_assignment' => $existingAssignment,
                    'lookup_debug' => $lookupDebug,
                ];
            } catch (\Throwable $e) {
                $rowFailures[] = [
                    'row' => $idx + 2,
                    'reason' => 'DB error: ' . $e->getMessage(),
                    'mappings' => $mappings,
                    'row_data' => $row,
                ];
                $detailedFailures[] = $rowFailures;
                $importResults[] = [
                    'row' => $idx + 2,
                    'employee_num' => $employeeData['employee_num'],
                    'action' => 'error',
                    'reason' => 'DB error: ' . $e->getMessage(),
                ];
            }
        }

        if (!empty($detailedFailures)) {
            return $this->importDataResponse([
                'success' => false,
                'failures' => $detailedFailures,
                'importResults' => $importResults,
                'message' => 'Some rows failed to import. See details.',
            ], 422);
        }

        if (count($invalidRows) > 0) {
            return $this->importDataResponse([
                'success' => false,
                'invalid_rows' => $invalidRows,
                'message' => 'Invalid gender values found. Allowed: M, F, O, N.',
            ], 422);
        }

        if (count($duplicates) > 0 && !$confirmOverwrite) {
            return $this->importDataResponse([
                'success' => false,
                'duplicates' => array_values(array_unique($duplicates)),
                'message' => 'Duplicate employee IDs found. Confirm overwrite?',
            ], 409);
        }

        $unresolvedLookups = [];
        foreach ($importResults as $result) {
            foreach ($result['lookup_debug'] ?? [] as $entry) {
                if (($entry['status'] ?? '') === 'not_found') {
                    $unresolvedLookups[] = array_merge($entry, [
                        'row' => $result['row'] ?? null,
                        'employee_num' => $result['employee_num'] ?? null,
                    ]);
                }
            }
        }

        if (!empty($unresolvedLookups)) {
            Log::warning('Employee import completed with unresolved lookups', [
                'count' => count($unresolvedLookups),
                'unresolved' => $unresolvedLookups,
            ]);
        }

        return $this->importDataResponse([
            'success' => true,
            'imported' => $imported,
            'importResults' => $importResults,
            'unresolved_lookups' => $unresolvedLookups,
        ]);
    }

    protected function importDataResponse(array $data, int $status = 200): \Illuminate\Http\JsonResponse
    {
        if ($this->importLogRecorder->isActive()) {
            $log = $this->importLogRecorder->finalize(
                $status,
                (bool) ($data['success'] ?? $status < 400),
                $data
            );
            if ($log) {
                $data['import_log_id'] = $log->id;
                $data['import_log_url'] = route('admin.import-logs.show', $log);
            }
        }

        return response()->json($data, $status);
    }
}
