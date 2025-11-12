<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\BaaVendor;

class BaaRegistryController extends Controller
{
    public function index()
    {
    $vendors = BaaVendor::with('facility')->orderBy('vendor_service')->get();
        return view('admin.baa-registry.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.baa-registry.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'vendor_service' => 'required|string',
            'type' => 'required|string',
            'ephi_access' => 'required|string',
            'baa_status' => 'required|string',
            'notes' => 'nullable|string',
            'baa_form' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        if ($request->hasFile('baa_form')) {
            $path = $request->file('baa_form')->store('baa_forms', 'public');
            $data['baa_form_path'] = $path;
        }
        BaaVendor::create($data);
        return redirect()->route('admin.baa-registry.index')->with('success', 'Vendor added.');
    }

    public function edit(BaaVendor $vendor)
    {
        return view('admin.baa-registry.edit', compact('vendor'));
    }

    public function update(Request $request, BaaVendor $vendor)
    {
        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'vendor_service' => 'required|string',
            'type' => 'required|string',
            'ephi_access' => 'required|string',
            'baa_status' => 'required|string',
            'notes' => 'nullable|string',
            'baa_form' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        if ($request->hasFile('baa_form')) {
            $path = $request->file('baa_form')->store('baa_forms', 'public');
            $data['baa_form_path'] = $path;
        }
        $vendor->update($data);
        return redirect()->route('admin.baa-registry.index')->with('success', 'Vendor updated.');
    }

    public function destroy(BaaVendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('admin.baa-registry.index')->with('success', 'Vendor deleted.');
    }
}
