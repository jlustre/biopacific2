<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncidentContact;

class IncidentContactController extends Controller
{
    public function index()
    {
        $contacts = IncidentContact::all();
        return view('admin.incident-contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('admin.incident-contacts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required',
            'name' => 'required',
            'title' => 'nullable',
            'email' => 'required|email',
            'phone' => 'nullable',
        ]);
        IncidentContact::create($request->all());
        return redirect()->route('admin.incident-contacts.index')->with('success', 'Contact added.');
    }

    public function edit(IncidentContact $incidentContact)
    {
        return view('admin.incident-contacts.edit', compact('incidentContact'));
    }

    public function update(Request $request, IncidentContact $incidentContact)
    {
        $request->validate([
            'role' => 'required',
            'name' => 'required',
            'title' => 'nullable',
            'email' => 'required|email',
            'phone' => 'nullable',
        ]);
        $incidentContact->update($request->all());
        return redirect()->route('admin.incident-contacts.index')->with('success', 'Contact updated.');
    }

    public function destroy(IncidentContact $incidentContact)
    {
        $incidentContact->delete();
        return redirect()->route('admin.incident-contacts.index')->with('success', 'Contact deleted.');
    }
}
