<?php
namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Support\ImportMappingPresetAccess;
use Illuminate\Http\Request;
use App\Models\ImportMappingPreset;
use Illuminate\Support\Facades\Auth;

class ImportMappingPresetController extends Controller
{
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

    public function store(Request $request)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'mappings' => 'required|array',
            'facility_id' => 'nullable|integer',
        ]);
        $preset = ImportMappingPreset::create([
            'user_id' => Auth::id(),
            'facility_id' => $request->input('facility_id', 99),
            'name' => $request->input('name'),
            'mappings' => $request->input('mappings'),
        ]);
        return response()->json(['success' => true, 'preset' => $preset]);
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
        $query = ImportMappingPreset::where('user_id', Auth::id());
        if ($facilityId) {
            $query->where(function($q) use ($facilityId) {
                $q->where('facility_id', $facilityId)
                  ->orWhere('facility_id', 99); // include global presets
            });
        }
        $presets = $query->get();
        return response()->json(['presets' => $presets]);
    }

    public function update(Request $request, $id)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $preset = ImportMappingPreset::where('user_id', Auth::id())->findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'mappings' => 'required|array',
            'facility_id' => 'nullable|integer',
        ]);
        $preset->update([
            'name' => $request->input('name'),
            'mappings' => $request->input('mappings'),
            'facility_id' => $request->input('facility_id', $preset->facility_id ?? 99),
        ]);
        return response()->json(['success' => true, 'preset' => $preset]);
    }

    public function destroy($id)
    {
        if (!ImportMappingPresetAccess::canCreate()) {
            return $this->denyPresetCreation();
        }

        $preset = ImportMappingPreset::where('user_id', Auth::id())->findOrFail($id);
        $preset->delete();
        return response()->json(['success' => true]);
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

        return response()->json(['success' => true, 'preset' => $preset->fresh()]);
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

        return response()->json(['success' => true, 'preset' => $preset]);
    }
}
