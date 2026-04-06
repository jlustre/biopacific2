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
        ]);
        $preset = ImportMappingPreset::create([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'mappings' => $request->input('mappings'),
        ]);
        return response()->json(['success' => true, 'preset' => $preset]);
    }

    public function index()
    {
        $presets = ImportMappingPreset::where('user_id', Auth::id())->get();
        return response()->json(['presets' => $presets]);
    }

    public function update(Request $request, $id)
    {
        $preset = ImportMappingPreset::where('user_id', Auth::id())->findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'mappings' => 'required|array',
        ]);
        $preset->update([
            'name' => $request->input('name'),
            'mappings' => $request->input('mappings'),
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
