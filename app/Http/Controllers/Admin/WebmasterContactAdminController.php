<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MemberPortalLayout;
use Illuminate\Http\Request;
use App\Models\WebmasterContact;

class WebmasterContactAdminController extends Controller
{
    protected function authorizeWebmasterAccess(): void
    {
        if (! MemberPortalLayout::userIsSystemAdmin(auth()->user())) {
            abort(403, 'Only system administrators can manage webmaster contact submissions.');
        }
    }

    public function destroy(WebmasterContact $contact)
    {
        $this->authorizeWebmasterAccess();
        $contact->delete();
        return redirect()->route('admin.webmaster.contacts.index')->with('success', 'Submission deleted successfully.');
    }
    public function update(Request $request, WebmasterContact $contact)
    {
        $this->authorizeWebmasterAccess();
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
        $this->authorizeWebmasterAccess();
        $query = WebmasterContact::query()->with(['facility', 'user']);

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('category')) {
            $query->where('category', request('category'));
        }
        if (request('source')) {
            $query->where('source', request('source'));
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

        $facilities = \App\Models\Facility::orderBy('name')->get(['id', 'name']);
        $categoryOptions = app(\App\Services\WebmasterContactService::class)->categoryOptions();

        return view('admin.webmaster_contacts.index', compact('contacts', 'facilities', 'categoryOptions'));
    }

    public function show(WebmasterContact $contact)
    {
        $this->authorizeWebmasterAccess();
        $contact = $contact->load(['facility', 'user', 'comments.user']);

        if (! $contact->is_read) {
            $contact->is_read = true;
            $contact->save();
        }

        return view('admin.webmaster_contacts.show', compact('contact'));
    }

    public function storeComment(Request $request, WebmasterContact $contact, \App\Services\WebmasterContactService $service)
    {
        $this->authorizeWebmasterAccess();

        $validated = $request->validate($service->commentValidationRules());

        $service->addComment(
            $contact,
            $validated['body'],
            $request->user(),
            \App\Models\WebmasterContactComment::AUTHOR_ADMIN
        );

        return back()->with('success', 'Reply posted.');
    }
}
