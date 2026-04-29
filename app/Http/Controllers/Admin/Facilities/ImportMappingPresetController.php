<?php
namespace App\Http\Controllers\Admin\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportMappingPreset;
use Illuminate\Support\Facades\Auth;

class ImportMappingPresetController extends Controller
{
    public function store(Request $request)
    {
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
        $preset = ImportMappingPreset::where('user_id', Auth::id())->findOrFail($id);
        $preset->delete();
        return response()->json(['success' => true]);
    }
}
