<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::all();
        return view('facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('facilities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:facilities',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'beds' => 'nullable|integer|min:0',
            'ranking_position' => 'nullable|integer|min:1',
            'ranking_total' => 'nullable|integer|min:1',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            $imagePath = $request->file('hero_image')->store('facilities', 'public');
            $data['hero_image_url'] = '/storage/' . $imagePath;
        }

        Facility::create($data);

        return redirect()->route('facilities.index')->with('success', 'Facility created successfully!');
    }

    public function show(Facility $facility)
    {
        return view('facilities.show', compact('facility'));
    }

    public function edit(Facility $facility)
    {
        return view('facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:facilities,slug,' . $facility->id,
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'beds' => 'nullable|integer|min:0',
            'ranking_position' => 'nullable|integer|min:1',
            'ranking_total' => 'nullable|integer|min:1',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            $imagePath = $request->file('hero_image')->store('facilities', 'public');
            $data['hero_image_url'] = '/storage/' . $imagePath;
        }

        $facility->update($data);

        return redirect()->route('facilities.index')->with('success', 'Facility updated successfully!');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('facilities.index')->with('success', 'Facility deleted successfully!');
    }
}
