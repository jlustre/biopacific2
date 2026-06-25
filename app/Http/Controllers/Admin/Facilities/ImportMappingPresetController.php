<?php
namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\ImportMappingPresetSeederExporter;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\Request;
use App\Models\ImportMappingPreset;
use Illuminate\Support\Facades\Auth;

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
