<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ImportMappingPreset;
use App\Models\User;
use App\Services\ExcelWorkbookParser;
use App\Services\ImportMappingPresetSeederExporter;
use App\Services\ImportMappingPresetValidator;
use App\Services\ImportPresetImportRunner;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ImportMappingPresetAdminController extends Controller
{
    public function __construct(
        protected ImportMappingPresetSeederExporter $seederExporter,
    ) {
        $this->middleware(['auth', 'role:admin|super-admin']);
    }

    protected function globalFacilityId(): int
    {
        return (int) config('import-mapping.global_facility_id', 99);
    }

    protected function targetTables(): array
    {
        return config('import-mapping.target_tables', []);
    }

    protected function validatePresetRequest(Request $request, ?int $presetId = null): array
    {
        $globalId = $this->globalFacilityId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'facility_id' => 'required|integer',
            'user_id' => 'required|exists:users,id',
            'mappings' => 'required|array|min:1',
            'mappings.*.worksheet' => 'required|string|max:255',
            'mappings.*.worksheet_column' => 'required|string|max:255',
            'mappings.*.table' => ['required', 'string', Rule::in($this->targetTables())],
            'mappings.*.table_column' => 'required|string|max:255',
        ]);

        $facilityId = (int) $validated['facility_id'];
        if ($facilityId !== $globalId && !Facility::whereKey($facilityId)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'facility_id' => 'The selected facility is invalid.',
            ]);
        }

        $validated['mappings'] = array_values($validated['mappings']);

        return $validated;
    }

    protected function redirectWithSeederSync(Request $request, \Illuminate\Http\RedirectResponse $redirect, string $successMessage): \Illuminate\Http\RedirectResponse
    {
        $sync = $this->seederExporter->syncFromRequest($request);
        $seederMessage = $this->seederExporter->seederSyncMessage($sync);

        if ($seederMessage && !empty($sync['error'])) {
            return $redirect->with('error', trim($successMessage . $seederMessage));
        }

        if ($seederMessage) {
            return $redirect->with('success', trim($successMessage . $seederMessage));
        }

        return $redirect->with('success', $successMessage);
    }

    public function index(Request $request)
    {
        $globalId = $this->globalFacilityId();
        $query = ImportMappingPreset::query()
            ->with(['user:id,name,email', 'facility:id,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('facility_id')) {
            if ($request->facility_id === 'global') {
                $query->where('facility_id', $globalId);
            } else {
                $query->where('facility_id', (int) $request->facility_id);
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        $sort = $request->get('sort', 'updated_at');
        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['name', 'facility_id', 'user_id', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'updated_at';
        }
        $query->orderBy($sort, $direction);

        $presets = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => ImportMappingPreset::count(),
            'global' => ImportMappingPreset::where('facility_id', $globalId)->count(),
            'facility_specific' => ImportMappingPreset::where('facility_id', '!=', $globalId)->count(),
        ];

        $facilities = Facility::orderBy('name')->get(['id', 'name']);
        $owners = User::query()
            ->whereIn('id', ImportMappingPreset::distinct()->pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.import-mapping-presets.index', array_merge(
            compact('presets', 'stats', 'facilities', 'owners', 'globalId'),
            $this->importModalData()
        ));
    }

    public function parseWorkbook(Request $request, ExcelWorkbookParser $parser)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            return response()->json($parser->parseUploadedFile($request->file('file')));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to read workbook: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function validatePresetMappings(
        Request $request,
        ImportMappingPreset $importMappingPreset,
        ExcelWorkbookParser $parser,
        ImportMappingPresetValidator $validator,
    ) {
        if (!ImportMappingPresetAccess::canUse()) {
            return response()->json([
                'valid' => false,
                'error' => 'Permission denied',
                'message' => 'You do not have permission to validate import presets.',
            ], 403);
        }

        return $this->validateMappingsAgainstWorkbook(
            $request,
            $parser,
            $validator,
            $importMappingPreset->mappings ?? []
        );
    }

    public function validateDraftMappings(
        Request $request,
        ExcelWorkbookParser $parser,
        ImportMappingPresetValidator $validator,
    ) {
        if (!ImportMappingPresetAccess::canCreate()) {
            return response()->json([
                'valid' => false,
                'error' => 'Permission denied',
                'message' => 'You do not have permission to validate import presets.',
            ], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'mappings' => 'required|array|min:1',
            'mappings.*.worksheet' => 'required|string|max:255',
            'mappings.*.worksheet_column' => 'required|string|max:255',
            'mappings.*.table' => ['required', 'string', Rule::in($this->targetTables())],
            'mappings.*.table_column' => 'required|string|max:255',
        ]);

        return $this->validateMappingsAgainstWorkbook(
            $request,
            $parser,
            $validator,
            array_values($validated['mappings'])
        );
    }

    /**
     * @param array<int, array<string, mixed>> $mappings
     */
    protected function validateMappingsAgainstWorkbook(
        Request $request,
        ExcelWorkbookParser $parser,
        ImportMappingPresetValidator $validator,
        array $mappings,
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        if ($mappings === []) {
            return response()->json([
                'valid' => false,
                'message' => 'No mappings to validate.',
                'summary' => ['total' => 0, 'passed' => 0, 'failed' => 0],
                'results' => [],
            ], 422);
        }

        try {
            $parsed = $parser->parseUploadedFile($request->file('file'));
        } catch (\Throwable $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Failed to read workbook',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json($validator->validate($parsed['worksheets'] ?? [], $mappings));
    }

    public function create()
    {
        return view('admin.import-mapping-presets.create', $this->formData());
    }

    public function store(Request $request)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return redirect()->route('admin.import-mapping-presets.index')
                ->with('error', 'You do not have permission to create import presets.');
        }

        $validated = $this->validatePresetRequest($request);

        ImportMappingPreset::create($validated);

        return $this->redirectWithSeederSync(
            $request,
            redirect()->route('admin.import-mapping-presets.index'),
            'Import preset created successfully.'
        );
    }

    public function show(ImportMappingPreset $importMappingPreset)
    {
        $importMappingPreset->load(['user:id,name,email', 'facility:id,name']);

        return view('admin.import-mapping-presets.show', array_merge(
            [
                'preset' => $importMappingPreset,
                'globalId' => $this->globalFacilityId(),
                'targetTables' => $this->targetTables(),
            ],
            $this->importModalData()
        ));
    }

    public function runImport(Request $request, ImportMappingPreset $importMappingPreset, ImportPresetImportRunner $runner)
    {
        if (!ImportMappingPresetAccess::canUse()) {
            return response()->json([
                'success' => false,
                'error' => 'Permission denied',
                'message' => 'You do not have permission to run imports.',
            ], 403);
        }

        $globalId = $this->globalFacilityId();
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'facility_id' => 'nullable|integer',
            'confirm_overwrite' => 'sometimes|boolean',
            'primary_worksheet' => 'nullable|string|max:255',
        ]);

        if ($importMappingPreset->isGlobal()) {
            $importFacilityId = (int) ($validated['facility_id'] ?? 0);
            if ($importFacilityId <= 0 || $importFacilityId === $globalId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Facility required',
                    'message' => 'Global presets require selecting a target facility for the import.',
                ], 422);
            }
            if (!Facility::whereKey($importFacilityId)->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid facility',
                    'message' => 'The selected target facility does not exist.',
                ], 422);
            }
        } else {
            $importFacilityId = (int) $importMappingPreset->facility_id;
        }

        return $runner->run(
            $importMappingPreset,
            $request->file('file'),
            $importFacilityId,
            $request->boolean('confirm_overwrite'),
            $validated['primary_worksheet'] ?? null,
        );
    }

    public function edit(ImportMappingPreset $importMappingPreset)
    {
        return view('admin.import-mapping-presets.edit', array_merge(
            $this->formData(),
            ['preset' => $importMappingPreset]
        ));
    }

    public function update(Request $request, ImportMappingPreset $importMappingPreset)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return redirect()->route('admin.import-mapping-presets.index')
                ->with('error', 'You do not have permission to update import presets.');
        }

        $validated = $this->validatePresetRequest($request, $importMappingPreset->id);
        $importMappingPreset->update($validated);

        return $this->redirectWithSeederSync(
            $request,
            redirect()->route('admin.import-mapping-presets.show', $importMappingPreset),
            'Import preset updated successfully.'
        );
    }

    public function destroy(Request $request, ImportMappingPreset $importMappingPreset)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return redirect()->route('admin.import-mapping-presets.index')
                ->with('error', 'You do not have permission to delete import presets.');
        }

        $name = $importMappingPreset->name;
        $importMappingPreset->delete();

        return $this->redirectWithSeederSync(
            $request,
            redirect()->route('admin.import-mapping-presets.index'),
            "Import preset \"{$name}\" deleted successfully."
        );
    }

    public function syncSeeder(ImportMappingPresetSeederExporter $exporter)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return redirect()->route('admin.import-mapping-presets.index')
                ->with('error', 'You do not have permission to update the import presets seeder.');
        }

        try {
            $result = $exporter->writeSeederFile();

            return redirect()->route('admin.import-mapping-presets.index')
                ->with(
                    'success',
                    'Seeder updated with ' . $result['count'] . ' preset(s). '
                    . 'File: database/seeders/ImportMappingPresetsTableSeeder.php. '
                    . 'Run php artisan db:seed --class=ImportMappingPresetsTableSeeder or migrate:fresh --seed to restore.'
                );
        } catch (\Throwable $e) {
            return redirect()->route('admin.import-mapping-presets.index')
                ->with('error', 'Failed to update seeder: ' . $e->getMessage());
        }
    }

    public function duplicate(Request $request, ImportMappingPreset $importMappingPreset)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return redirect()->route('admin.import-mapping-presets.index')
                ->with('error', 'You do not have permission to duplicate import presets.');
        }

        $globalId = $this->globalFacilityId();
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'facility_id' => 'nullable|integer',
        ]);

        $targetFacilityId = (int) ($validated['facility_id'] ?? $importMappingPreset->facility_id);
        if ($targetFacilityId !== $globalId && !Facility::whereKey($targetFacilityId)->exists()) {
            return back()->with('error', 'The selected facility is invalid.');
        }

        $copy = ImportMappingPreset::create([
            'user_id' => Auth::id(),
            'facility_id' => $targetFacilityId,
            'name' => $validated['name'] ?? ('Copy of ' . $importMappingPreset->name),
            'mappings' => $importMappingPreset->mappings,
        ]);

        return $this->redirectWithSeederSync(
            $request,
            redirect()->route('admin.import-mapping-presets.edit', $copy),
            'Preset duplicated. Review the copy and save any changes.'
        );
    }

    protected function formData(): array
    {
        return [
            'facilities' => Facility::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'globalId' => $this->globalFacilityId(),
            'targetTables' => $this->targetTables(),
            'defaultUserId' => Auth::id(),
            'parseWorkbookUrl' => route('admin.import-mapping-presets.parse-workbook'),
            'validateDraftMappingsUrl' => route('admin.import-mapping-presets.validate-draft'),
            'tableColumnsUrl' => route('admin.facility.files.table_columns'),
        ];
    }

    protected function importModalData(): array
    {
        $globalId = $this->globalFacilityId();

        return [
            'importFacilities' => Facility::query()
                ->where('id', '!=', $globalId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'canImport' => ImportMappingPresetAccess::canUse(),
            'parseWorkbookUrl' => route('admin.import-mapping-presets.parse-workbook'),
        ];
    }
}
