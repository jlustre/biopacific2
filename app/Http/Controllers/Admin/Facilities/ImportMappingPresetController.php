<?php
namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessEmployeeImport;
use App\Models\Facility;
use App\Models\ImportLog;
use App\Services\ExcelWorkbookParser;
use App\Services\ImportMappingPresetSeederExporter;
use App\Services\ImportMappingPresetValidator;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\Request;
use App\Models\ImportMappingPreset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportMappingPresetController extends Controller
{
    public function __construct(
        protected ImportMappingPresetSeederExporter $seederExporter,
    ) {}

    protected function globalFacilityId(): int
    {
        return (int) config('import-mapping.global_facility_id', 99);
    }

    protected function findAccessiblePreset(int $id, ?int $contextFacilityId = null): ImportMappingPreset
    {
        $query = ImportMappingPreset::where('user_id', Auth::id());

        if ($contextFacilityId) {
            $globalId = $this->globalFacilityId();
            $query->where(function ($q) use ($contextFacilityId, $globalId) {
                $q->where('facility_id', $contextFacilityId)
                    ->orWhere('facility_id', $globalId);
            });
        }

        return $query->findOrFail($id);
    }

    protected function findUsablePreset(int $id, int $targetFacilityId): ImportMappingPreset
    {
        $globalId = $this->globalFacilityId();
        $user = Auth::user();

        $query = ImportMappingPreset::query()
            ->whereKey($id)
            ->where(function ($preset) use ($globalId, $targetFacilityId) {
                $preset->where('facility_id', $globalId)
                    ->orWhere('facility_id', $targetFacilityId);
            });

        if (! $user?->hasRole(['admin', 'super-admin', 'rdhr'])) {
            $query->where(function ($preset) use ($globalId) {
                $preset->where('facility_id', $globalId)
                    ->orWhere('user_id', Auth::id());
            });
        }

        return $query->firstOrFail();
    }

    protected function authorizeTargetFacility(int $targetFacilityId): Facility
    {
        $facility = Facility::query()->findOrFail($targetFacilityId);
        $user = Auth::user();

        if ($user?->hasRole(['admin', 'super-admin', 'rdhr'])) {
            return $facility;
        }

        $userFacilityId = (int) ($user?->facility_id ?? 0);
        if ($userFacilityId <= 0) {
            $userFacilityId = (int) ($user?->resolvedBpEmployee(['currentAssignment'])
                ?->currentAssignment?->facility_id ?? 0);
        }

        abort_unless($userFacilityId > 0 && $userFacilityId === $targetFacilityId, 403);

        return $facility;
    }

    protected function denyPresetCreation()
    {
        $roleLabel = ImportMappingPresetAccess::restrictedRoleLabel() ?? 'your role';

        return response()->json([
            'success' => false,
            'error' => "Creating mapping presets is not available yet for {$roleLabel}. Please contact a Super Administrator.",
        ], 403);
    }

    protected function jsonWithSeederSync(Request $request, array $payload, int $status = 200)
    {
        return response()->json(array_merge(
            $payload,
            $this->seederExporter->seederSyncResponsePayload(
                $this->seederExporter->syncFromRequest($request)
            )
        ), $status);
    }

    public function store(Request $request)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'mappings' => 'required|array|min:1',
                'mappings.*.worksheet' => 'required|string|max:255',
                'mappings.*.worksheet_column' => 'required|string|max:255',
                'mappings.*.table' => 'required|string|max:255',
                'mappings.*.table_column' => 'required|string|max:255',
                'facility_id' => 'nullable|integer',
            ]);

            $preset = ImportMappingPreset::create([
                'user_id' => Auth::id(),
                'facility_id' => $validated['facility_id'] ?? $this->globalFacilityId(),
                'name' => $validated['name'],
                'mappings' => array_values($validated['mappings']),
            ]);

            return $this->jsonWithSeederSync($request, ['success' => true, 'preset' => $preset]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->validator->errors()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'Could not save preset: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        if (!ImportMappingPresetAccess::canUse()) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to use import mapping presets.',
                'presets' => [],
            ], 403);
        }

        $facilityId = $request->input('facility_id');
        $globalId = $this->globalFacilityId();
        $userId = Auth::id();

        $query = ImportMappingPreset::query()->where(function ($q) use ($userId, $globalId, $facilityId) {
            // Global presets are shared with everyone who can import.
            $q->where('facility_id', $globalId)
                ->orWhere(function ($sub) use ($userId, $globalId, $facilityId) {
                    $sub->where('user_id', $userId);
                    if ($facilityId) {
                        $sub->where(function ($fac) use ($facilityId, $globalId) {
                            $fac->where('facility_id', (int) $facilityId)
                                ->orWhere('facility_id', $globalId);
                        });
                    }
                });
        });

        $presets = $query->orderBy('name')->get();

        return response()->json(['presets' => $presets]);
    }

    public function parseWorkbook(Request $request, ExcelWorkbookParser $parser)
    {
        abort_unless(ImportMappingPresetAccess::canUse(), 403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            return response()->json($parser->parseUploadedFile($request->file('file')));
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to read workbook: '.$exception->getMessage(),
            ], 422);
        }
    }

    public function validatePreset(
        Request $request,
        int $id,
        ExcelWorkbookParser $parser,
        ImportMappingPresetValidator $validator,
    ) {
        abort_unless(ImportMappingPresetAccess::canUse(), 403);

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'facility_id' => 'required|integer',
        ]);

        $targetFacilityId = (int) $validated['facility_id'];
        $this->authorizeTargetFacility($targetFacilityId);
        $preset = $this->findUsablePreset($id, $targetFacilityId);

        try {
            $parsed = $parser->parseUploadedFile($request->file('file'));
        } catch (\Throwable $exception) {
            return response()->json([
                'valid' => false,
                'error' => 'Failed to read workbook',
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json(
            $validator->validate($parsed['worksheets'] ?? [], $preset->mappings ?? [])
        );
    }

    public function runImport(
        Request $request,
        int $id,
    ) {
        abort_unless(ImportMappingPresetAccess::canUse(), 403);

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'facility_id' => 'required|integer',
            'confirm_overwrite' => 'sometimes|boolean',
            'primary_worksheet' => 'nullable|string|max:255',
        ]);

        $targetFacilityId = (int) $validated['facility_id'];
        $this->authorizeTargetFacility($targetFacilityId);
        $preset = $this->findUsablePreset($id, $targetFacilityId);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $fileName = Str::uuid().'.'.$extension;
        $storedPath = $file->storeAs('employee-imports', $fileName, 'local');

        $log = ImportLog::query()->create([
            'user_id' => Auth::id(),
            'facility_id' => $targetFacilityId,
            'import_mapping_preset_id' => $preset->id,
            'source' => 'admin_preset',
            'source_filename' => $file->getClientOriginalName(),
            'import_file_path' => $storedPath,
            'status' => ImportLog::STATUS_QUEUED,
            'started_at' => now(),
        ]);

        ProcessEmployeeImport::dispatch(
            $log->id,
            $request->boolean('confirm_overwrite')
        );

        return response()->json([
            'success' => true,
            'queued' => true,
            'message' => 'Employee import queued.',
            'import' => $this->importProgressPayload($log->fresh()),
        ], 202);
    }

    public function importStatus(Request $request, ImportLog $importLog)
    {
        $this->authorizeImportLog($request, $importLog);

        return response()->json([
            'success' => true,
            'import' => $this->importProgressPayload($importLog->fresh()),
        ]);
    }

    public function cancelImport(Request $request, ImportLog $importLog)
    {
        $this->authorizeImportLog($request, $importLog);

        if (in_array($importLog->status, [
            ImportLog::STATUS_COMPLETED,
            ImportLog::STATUS_PARTIAL,
            ImportLog::STATUS_FAILED,
            ImportLog::STATUS_CANCELLED,
            ImportLog::STATUS_REVERTED,
        ], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This import has already finished.',
                'import' => $this->importProgressPayload($importLog),
            ], 409);
        }

        $updates = ['cancel_requested_at' => now()];
        if (in_array($importLog->status, [ImportLog::STATUS_QUEUED, ImportLog::STATUS_AWAITING_CONFIRMATION], true)) {
            $updates += [
                'status' => ImportLog::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'completed_at' => now(),
                'error_message' => 'Import cancelled by the user. Completed employee records were retained.',
            ];
        }
        $importLog->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Cancellation requested. The current employee will finish first.',
            'import' => $this->importProgressPayload($importLog->fresh()),
        ], 202);
    }

    public function confirmImportOverwrite(Request $request, ImportLog $importLog)
    {
        $this->authorizeImportLog($request, $importLog);
        abort_unless($importLog->status === ImportLog::STATUS_AWAITING_CONFIRMATION, 409);
        abort_unless($importLog->import_file_path && Storage::disk('local')->exists($importLog->import_file_path), 410);

        $importLog->update([
            'status' => ImportLog::STATUS_QUEUED,
            'cancel_requested_at' => null,
            'cancelled_at' => null,
            'completed_at' => null,
            'error_message' => null,
        ]);
        ProcessEmployeeImport::dispatch($importLog->id, true);

        return response()->json([
            'success' => true,
            'queued' => true,
            'message' => 'Overwrite confirmed. Import resumed.',
            'import' => $this->importProgressPayload($importLog->fresh()),
        ], 202);
    }

    private function authorizeImportLog(Request $request, ImportLog $importLog): void
    {
        abort_unless((int) $importLog->user_id === (int) $request->user()->id, 403);
        $this->authorizeTargetFacility((int) $importLog->facility_id);
    }

    private function importProgressPayload(ImportLog $log): array
    {
        $summary = $log->summary ?? [];
        $total = (int) $log->total_rows;
        $processed = (int) $log->processed_rows;

        return [
            'id' => $log->id,
            'status' => $log->status,
            'status_label' => $log->statusLabel(),
            'total' => $total,
            'processed' => $processed,
            'imported' => (int) $log->imported_rows,
            'skipped' => (int) $log->skipped_rows,
            'failed' => (int) $log->failed_rows,
            'percent' => $total > 0 ? min(100, (int) floor(($processed / $total) * 100)) : 0,
            'duplicates' => array_values($summary['duplicates'] ?? []),
            'message' => $log->error_message,
            'cancel_requested' => $log->cancel_requested_at !== null,
            'terminal' => in_array($log->status, [
                ImportLog::STATUS_COMPLETED,
                ImportLog::STATUS_PARTIAL,
                ImportLog::STATUS_FAILED,
                ImportLog::STATUS_CANCELLED,
                ImportLog::STATUS_REVERTED,
            ], true),
            'status_url' => route('admin.facility.mapping-presets.import-status', $log),
            'cancel_url' => route('admin.facility.mapping-presets.cancel-import', $log),
            'confirm_url' => route('admin.facility.mapping-presets.confirm-import', $log),
        ];
    }

    public function update(Request $request, $id)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        try {
            $preset = ImportMappingPreset::where('user_id', Auth::id())->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'mappings' => 'required|array|min:1',
                'mappings.*.worksheet' => 'required|string|max:255',
                'mappings.*.worksheet_column' => 'required|string|max:255',
                'mappings.*.table' => 'required|string|max:255',
                'mappings.*.table_column' => 'required|string|max:255',
                'facility_id' => 'nullable|integer',
            ]);

            $preset->update([
                'name' => $validated['name'],
                'mappings' => array_values($validated['mappings']),
                'facility_id' => $validated['facility_id'] ?? $preset->facility_id ?? $this->globalFacilityId(),
            ]);

            return $this->jsonWithSeederSync($request, ['success' => true, 'preset' => $preset->fresh()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->validator->errors()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'Could not save preset: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $preset = ImportMappingPreset::where('user_id', Auth::id())->findOrFail($id);
        $preset->delete();

        return $this->jsonWithSeederSync($request, ['success' => true]);
    }

    public function updateDetails(Request $request, $id)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $contextFacilityId = $request->input('context_facility_id');
        $preset = $this->findAccessiblePreset((int) $id, $contextFacilityId ? (int) $contextFacilityId : null);

        $globalId = $this->globalFacilityId();
        $request->validate([
            'name' => 'required|string|max:255',
            'facility_id' => 'required|integer',
        ]);

        $targetFacilityId = (int) $request->input('facility_id');
        if ($targetFacilityId !== $globalId && !Facility::whereKey($targetFacilityId)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'The selected facility is invalid.',
            ], 422);
        }

        $preset->update([
            'name' => $request->input('name'),
            'facility_id' => $targetFacilityId,
        ]);

        return $this->jsonWithSeederSync($request, ['success' => true, 'preset' => $preset->fresh()]);
    }

    public function duplicate(Request $request, $id)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $contextFacilityId = $request->input('context_facility_id');
        $source = $this->findAccessiblePreset((int) $id, $contextFacilityId ? (int) $contextFacilityId : null);

        $globalId = $this->globalFacilityId();
        $request->validate([
            'name' => 'required|string|max:255',
            'facility_id' => 'required|integer',
        ]);

        $targetFacilityId = (int) $request->input('facility_id');
        if ($targetFacilityId !== $globalId && !Facility::whereKey($targetFacilityId)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'The selected facility is invalid.',
            ], 422);
        }

        $preset = ImportMappingPreset::create([
            'user_id' => Auth::id(),
            'facility_id' => $targetFacilityId,
            'name' => $request->input('name'),
            'mappings' => $source->mappings,
        ]);

        return $this->jsonWithSeederSync($request, ['success' => true, 'preset' => $preset]);
    }

    public function syncSeeder(Request $request)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $sync = $this->seederExporter->syncFromRequest($request->merge(['update_seeder' => true]));

        if (!empty($sync['error'])) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update seeder: ' . $sync['error'],
                'seeder' => $sync,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Seeder updated with ' . ($sync['count'] ?? 0) . ' preset(s).',
            'seeder' => $sync,
        ]);
    }
}
