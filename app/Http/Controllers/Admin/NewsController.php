<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\News;
use App\Support\SelectedFacility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    protected function scopedFacilityId(Request $request): ?int
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && $user->facility_id) {
            return (int) $user->facility_id;
        }

        return null;
    }

    protected function facilitiesForUser(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if ($scopedFacilityId) {
            return Facility::where('id', $scopedFacilityId)->orderBy('name')->get();
        }

        return Facility::orderBy('name')->get();
    }

    protected function applyFacilityScopeToNewsQuery($query, Request $request, ?int $filterFacilityId = null)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilityId = $scopedFacilityId ?? $filterFacilityId;

        if (! $facilityId) {
            return $query;
        }

        return $query->where(function ($q) use ($facilityId) {
            $q->where('facility_id', $facilityId)
                ->orWhereHas('facilities', fn ($f) => $f->where('facilities.id', $facilityId));
        });
    }

    protected function authorizeNewsAccess(Request $request, News $news): void
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        if (! $scopedFacilityId) {
            return;
        }

        if ($news->is_global) {
            abort(403, 'You cannot modify global news items.');
        }

        $belongsToFacility = (int) $news->facility_id === $scopedFacilityId
            || $news->facilities()->where('facilities.id', $scopedFacilityId)->exists();

        if (! $belongsToFacility) {
            abort(403, 'You do not have access to this news item.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $canFilterFacilities = $scopedFacilityId === null;
        $filterFacilityId = $request->filled('facility_id')
            ? (int) $request->facility_id
            : SelectedFacility::id($request);

        $query = News::with(['facilities', 'facility'])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        $this->applyFacilityScopeToNewsQuery($query, $request, $filterFacilityId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'published') {
                $query->where('status', true);
            } elseif ($request->input('status') === 'draft') {
                $query->where('status', false);
            }
        }

        if ($request->filled('scope')) {
            if ($request->input('scope') === 'global') {
                $query->where('is_global', true);
            } elseif ($request->input('scope') === 'local') {
                $query->where('is_global', false);
            }
        }

        $statsQuery = News::query();
        $this->applyFacilityScopeToNewsQuery($statsQuery, $request, $filterFacilityId);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'published' => (clone $statsQuery)->where('status', true)->count(),
            'drafts' => (clone $statsQuery)->where('status', false)->count(),
        ];

        $news = $query->paginate(12)->withQueryString();
        $facilities = $this->facilitiesForUser($request);
        $scopedFacility = $scopedFacilityId ? Facility::find($scopedFacilityId) : null;

        return view('admin.news.index', compact(
            'news',
            'facilities',
            'scopedFacility',
            'scopedFacilityId',
            'canFilterFacilities',
            'stats',
            'filterFacilityId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);
        $facilityId = $scopedFacilityId ?? $request->query('facility_id');
        $facility = $facilityId ? Facility::find($facilityId) : null;
        $facilities = $this->facilitiesForUser($request);

        return view('admin.news.create', compact('facility', 'facilities', 'scopedFacilityId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $scopedFacilityId = $this->scopedFacilityId($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
            'status' => 'required|in:0,1',
            'is_global' => 'nullable|boolean',
            'facility_ids' => 'array',
            'facility_ids.*' => 'exists:facilities,id',
        ]);

        $news = new News($validated);
        $news->status = (bool) $request->input('status');
        $news->is_global = $scopedFacilityId ? false : $request->boolean('is_global');

        if ($scopedFacilityId) {
            $news->facility_id = $scopedFacilityId;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $news->image = $path;
        }

        $news->save();

        if ($scopedFacilityId) {
            $news->facilities()->sync([$scopedFacilityId]);
        } elseif ($request->filled('facility_ids')) {
            $news->facilities()->sync($request->facility_ids);
        }

        return redirect()->route('admin.news.index')->with('success', 'News article created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $news = News::with(['facilities', 'facility'])->findOrFail($id);

        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $news = News::with('facilities')->findOrFail($id);
        $this->authorizeNewsAccess($request, $news);
        $facilities = $this->facilitiesForUser($request);
        $scopedFacilityId = $this->scopedFacilityId($request);

        return view('admin.news.edit', compact('news', 'facilities', 'scopedFacilityId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $this->authorizeNewsAccess($request, $news);
        $scopedFacilityId = $this->scopedFacilityId($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
            'status' => 'required|in:0,1',
            'is_global' => 'nullable|boolean',
            'facility_ids' => 'array',
            'facility_ids.*' => 'exists:facilities,id',
        ]);

        $news->fill($validated);
        $news->status = (bool) $request->input('status');
        $news->is_global = $scopedFacilityId ? false : $request->boolean('is_global');

        if ($request->hasFile('image')) {
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }
            $path = $request->file('image')->store('news', 'public');
            $news->image = $path;
        }

        $news->save();

        if ($scopedFacilityId) {
            $news->facility_id = $scopedFacilityId;
            $news->save();
            $news->facilities()->sync([$scopedFacilityId]);
        } elseif ($request->filled('facility_ids')) {
            $news->facilities()->sync($request->facility_ids);
        }

        return redirect()->route('admin.news.index')->with('success', 'News article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $this->authorizeNewsAccess($request, $news);

        if ($news->image && Storage::disk('public')->exists($news->image)) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'News article deleted successfully.');
    }

    /**
     * Delete the uploaded image for a news item.
     */
    public function deleteImage(Request $request, News $news)
    {
        $this->authorizeNewsAccess($request, $news);

        if ($news->image && Storage::disk('public')->exists($news->image)) {
            Storage::disk('public')->delete($news->image);
        }
        $news->image = null;
        $news->save();

        session()->flash('notification', [
            'type' => 'success',
            'message' => 'Image deleted successfully.',
        ]);

        return redirect()->back();
    }
}
