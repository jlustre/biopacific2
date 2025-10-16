<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $facilityId = request()->query('facility_id');
        if ($facilityId) {
            $blogs = Blog::where('facility_id', $facilityId)->orWhere('is_global', true)->get();
        } else {
            $blogs = Blog::where('is_global', true)->get();
        }
        return view('blogs.index', compact('blogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.blogs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'is_global' => 'boolean',
            'facility_ids' => 'array',
            'facility_ids.*' => 'exists:facilities,id',
            'author' => 'required|exists:users,id',
            'status' => 'nullable|string|max:50',
            'photo1' => 'nullable|image|max:2048',
            'photo2' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'version' => 'nullable|string|max:20',
            'published_at' => 'nullable|date',
        ]);

        // Handle file uploads
        if ($request->hasFile('photo1')) {
            $data['photo1'] = $request->file('photo1')->store('blog_photos', 'public');
        } else {
            unset($data['photo1']);
        }
        if ($request->hasFile('photo2')) {
            $data['photo2'] = $request->file('photo2')->store('blog_photos', 'public');
        } else {
            unset($data['photo2']);
        }

        // Remove facility_ids for mass assignment
        $facilityIds = $data['facility_ids'] ?? [];
        unset($data['facility_ids']);

        // Create blog
        $blog = \App\Models\Blog::create($data);
        // Sync facilities if not global
        if (!$request->input('is_global', true) && !empty($facilityIds)) {
            $blog->facilities()->sync($facilityIds);
        }

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog created successfully.');
    // If validation fails, Laravel will automatically redirect back with errors.
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    $blog = Blog::findOrFail($id);
    return view('blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $blog = Blog::with('facilities')->findOrFail($id);
        return view('admin.blogs.create', [
            'blog' => $blog,
            'editMode' => true
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $blog = Blog::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'is_global' => 'boolean',
            'facility_ids' => 'array',
            'facility_ids.*' => 'exists:facilities,id',
            'author' => 'required|exists:users,id',
            'status' => 'nullable|string|max:50',
            'photo1' => 'nullable|image|max:2048',
            'photo2' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'version' => 'nullable|string|max:20',
            'published_at' => 'nullable|date',
        ]);

        // Handle file uploads
        if ($request->hasFile('photo1')) {
            $data['photo1'] = $request->file('photo1')->store('blog_photos', 'public');
        } else {
            unset($data['photo1']);
        }
        if ($request->hasFile('photo2')) {
            $data['photo2'] = $request->file('photo2')->store('blog_photos', 'public');
        } else {
            unset($data['photo2']);
        }

        // Remove facility_ids for mass assignment
        $facilityIds = $data['facility_ids'] ?? [];
        unset($data['facility_ids']);

        // If new_version is set, create a new blog instead of updating
        if ($request->input('new_version') == '1') {
            $newBlog = Blog::create($data);
            if (!$request->input('is_global', true) && !empty($facilityIds)) {
                $newBlog->facilities()->sync($facilityIds);
            }
            return redirect()->route('admin.blogs.index')->with('success', 'New blog version created successfully!');
        } else {
            $blog->update($data);
            // Sync facilities if not global
            if (!$request->input('is_global', true) && !empty($facilityIds)) {
                $blog->facilities()->sync($facilityIds);
            } else {
                $blog->facilities()->detach();
            }
            return redirect()->route('admin.blogs.index')->with('success', 'Blog updated successfully!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog deleted successfully.');
    }
}
