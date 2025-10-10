<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Facility;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $facilityId = $request->query('facility_id');
        if ($facilityId) {
            $news = News::where('facility_id', $facilityId)->orderByDesc('published_at')->get();
        } else {
            $news = News::orderByDesc('published_at')->get();
        }
        return view('admin.news.index', compact('news', 'facilityId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
    $facilityId = $request->query('facility_id');
    $facility = $facilityId ? Facility::find($facilityId) : null;
    $facilities = Facility::all();
    return view('admin.news.create', compact('facility', 'facilities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        $news->is_global = $request->has('is_global');
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $news->image = $path;
        }
        $news->save();
        // Sync facilities
        if ($request->filled('facility_ids')) {
            $news->facilities()->sync($request->facility_ids);
        }
        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
    $news = News::findOrFail($id);
    $facilities = \App\Models\Facility::all();
    return view('admin.news.edit', compact('news', 'facilities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
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
        $news->is_global = $request->has('is_global');
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $news->image = $path;
        }
        $news->save();
        // Sync facilities
        if ($request->filled('facility_ids')) {
            $news->facilities()->sync($request->facility_ids);
        }
        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
    }

    /**
     * Delete the uploaded image for a news item.
     */
    public function deleteImage(News $news)
    {
        if ($news->image && Storage::disk('public')->exists($news->image)) {
            Storage::disk('public')->delete($news->image);
        }
        $news->image = null;
        $news->save();
        session()->flash('notification', [
            'type' => 'success',
            'message' => 'Image deleted successfully.'
        ]);
        return redirect()->back();
    }
}
