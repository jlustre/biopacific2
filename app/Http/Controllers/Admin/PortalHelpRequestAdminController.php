<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortalHelpRequest;
use App\Support\MemberPortalLayout;
use Illuminate\Http\Request;

class PortalHelpRequestAdminController extends Controller
{
    protected function authorizeAccess(): void
    {
        if (! MemberPortalLayout::userIsSystemAdmin(auth()->user()) && ! auth()->user()?->hasRole('rdhr')) {
            abort(403, 'You do not have permission to manage portal help requests.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $query = PortalHelpRequest::query()->with(['facility', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($scope) use ($search) {
                $scope->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $facilities = \App\Models\Facility::orderBy('name')->get(['id', 'name']);

        return view('admin.portal_help_requests.index', compact('requests', 'facilities'));
    }

    public function show(PortalHelpRequest $portalHelpRequest)
    {
        $this->authorizeAccess();

        $portalHelpRequest->load(['facility', 'user']);

        if (! $portalHelpRequest->is_read) {
            $portalHelpRequest->is_read = true;
            $portalHelpRequest->save();
        }

        return view('admin.portal_help_requests.show', ['request' => $portalHelpRequest]);
    }

    public function update(Request $request, PortalHelpRequest $portalHelpRequest)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,resolved',
        ]);

        $portalHelpRequest->status = $validated['status'];
        if ($validated['status'] === 'resolved' && ! $portalHelpRequest->resolved_at) {
            $portalHelpRequest->resolved_at = now();
        } elseif ($validated['status'] !== 'resolved') {
            $portalHelpRequest->resolved_at = null;
        }
        $portalHelpRequest->save();

        return back()->with('success', 'Status updated.');
    }
}
