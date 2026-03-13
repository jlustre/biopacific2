<?php
namespace App\Http\Controllers;

use App\Models\ConfidentialReferenceCheck;
use Illuminate\Http\Request;

class ConfidentialReferenceCheckController extends Controller
{
    public function index()
    {
        $checks = ConfidentialReferenceCheck::with(['user', 'facility'])->paginate(20);
        return view('confidential-reference-checks.index', compact('checks'));
    }

    public function create()
    {
        return view('confidential-reference-checks.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'facility_id' => 'nullable|exists:facilities,id',
            'reference_name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'comments' => 'nullable|string',
            'reference_phone' => 'nullable|string|max:255',
            'reference_email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'signed' => 'sometimes|boolean',
            'signed_date' => 'nullable|date',
        ]);
        $data['signed'] = $request->has('signed');
        ConfidentialReferenceCheck::create($data);
        return redirect()->route('confidential-reference-checks.index')->with('success', 'Reference check created.');
    }

    public function show(ConfidentialReferenceCheck $confidentialReferenceCheck)
    {
        return view('confidential-reference-checks.show', compact('confidentialReferenceCheck'));
    }

    public function edit(ConfidentialReferenceCheck $confidentialReferenceCheck)
    {
        return view('confidential-reference-checks.edit', compact('confidentialReferenceCheck'));
    }

    public function update(Request $request, ConfidentialReferenceCheck $confidentialReferenceCheck)
    {
        $data = $request->validate([
            'reference_name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'comments' => 'nullable|string',
            'reference_phone' => 'nullable|string|max:255',
            'reference_email' => 'nullable|email|max:255',
            'company' => 'nullable|string|max:255',
            'signed' => 'sometimes|boolean',
            'signed_date' => 'nullable|date',
        ]);
        $data['signed'] = $request->has('signed');
        $confidentialReferenceCheck->update($data);
        return redirect()->route('confidential-reference-checks.index')->with('success', 'Reference check updated.');
    }

    public function destroy(ConfidentialReferenceCheck $confidentialReferenceCheck)
    {
        $confidentialReferenceCheck->delete();
        return redirect()->route('confidential-reference-checks.index')->with('success', 'Reference check deleted.');
    }
}
