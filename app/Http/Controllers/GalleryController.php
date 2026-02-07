<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        // Return a view or JSON listing gallery images
        return view('gallery.index');
    }

    public function create()
    {
        // Return a view for creating a new gallery image
        return view('gallery.create');
    }

    public function store(Request $request)
    {
        // Handle storing a new gallery image
        // Example: Gallery::create($request->all());
        return redirect()->route('gallery.index');
    }

    public function show($gallery)
    {
        // Return a view for showing a gallery image
        return view('gallery.show', compact('gallery'));
    }

    public function edit($gallery)
    {
        // Return a view for editing a gallery image
        return view('gallery.edit', compact('gallery'));
    }

    public function update(Request $request, $gallery)
    {
        // Handle updating a gallery image
        // Example: $gallery->update($request->all());
        return redirect()->route('gallery.index');
    }

    public function destroy($gallery)
    {
        // Handle deleting a gallery image
        // Example: $gallery->delete();
        return redirect()->route('gallery.index');
    }

    public function upload(Request $request, $facility)
    {
        // Handle uploading a gallery image for a facility
        // Example: $facility->gallery()->create($request->all());
        return redirect()->route('facilities.galleries.index', ['facility' => $facility]);
    }

    public function delete($image)
    {
        // Handle deleting a specific gallery image
        // Example: $image->delete();
        return redirect()->route('gallery.index');
    }

    public function move($image, $direction)
    {
        // Handle moving a gallery image up/down
        // Example: $image->move($direction);
        return redirect()->route('gallery.index');
    }

    public function clearFacility($facility)
    {
        // Handle clearing all gallery images for a facility
        // Example: $facility->gallery()->delete();
        return redirect()->route('facilities.galleries.index', ['facility' => $facility]);
    }
}
