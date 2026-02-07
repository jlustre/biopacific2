<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TourRequestController extends Controller
{
    public function index()
    {
        // Return a view or JSON listing tour requests
        return view('tour-requests.index');
    }

    public function create()
    {
        // Return a view for creating a new tour request
        return view('tour-requests.create');
    }

    public function store(Request $request)
    {
        // Handle storing a new tour request
        // Example: TourRequest::create($request->all());
        return redirect()->route('tour-requests.index');
    }

    public function show($tourRequest)
    {
        // Return a view for showing a tour request
        return view('tour-requests.show', compact('tourRequest'));
    }

    public function edit($tourRequest)
    {
        // Return a view for editing a tour request
        return view('tour-requests.edit', compact('tourRequest'));
    }

    public function update(Request $request, $tourRequest)
    {
        // Handle updating a tour request
        // Example: $tourRequest->update($request->all());
        return redirect()->route('tour-requests.index');
    }

    public function destroy($tourRequest)
    {
        // Handle deleting a tour request
        // Example: $tourRequest->delete();
        return redirect()->route('tour-requests.index');
    }
}
