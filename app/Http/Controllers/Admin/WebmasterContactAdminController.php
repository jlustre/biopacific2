<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebmasterContact;

class WebmasterContactAdminController extends Controller
{
    public function destroy(WebmasterContact $contact)
    {
        $contact->delete();
        return redirect()->route('admin.webmaster.contacts.index')->with('success', 'Submission deleted successfully.');
    }
    public function update(Request $request, WebmasterContact $contact)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,resolved',
        ]);
        $contact->status = $validated['status'];
        if ($validated['status'] === 'resolved' && !$contact->resolved_at) {
            $contact->resolved_at = now();
        } elseif ($validated['status'] !== 'resolved') {
            $contact->resolved_at = null;
        }
        $contact->save();
        return back()->with('success', 'Status updated successfully.');
    }
    public function index()
    {
        $query = WebmasterContact::query()->with('facility');

        // Filtering
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('urgent') !== null && request('urgent') !== '') {
            $query->where('urgent', (bool)request('urgent'));
        }
        if (request('facility_id')) {
            $query->where('facility_id', request('facility_id'));
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('subject', 'like', "%$search%")
                  ->orWhere('message', 'like', "%$search%") ;
            });
        }

        $contacts = $query->orderByDesc('created_at')->paginate(20)->appends(request()->query());

        // For filter dropdowns
    $facilities = \App\Models\Facility::orderBy('name')->get(['id','name']);

        return view('admin.webmaster_contacts.index', compact('contacts', 'facilities'));
    }

    public function show(WebmasterContact $contact)
    {
        if (!$contact->is_read) {
            $contact->is_read = true;
            $contact->save();
        }
        return view('admin.webmaster_contacts.show', compact('contact'));
    }
}
