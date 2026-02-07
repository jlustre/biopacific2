<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        // Return a view or JSON listing events
        return view('events.index');
    }

    public function create()
    {
        // Return a view for creating a new event
        return view('events.create');
    }

    public function store(Request $request)
    {
        // Handle storing a new event
        // Example: Event::create($request->all());
        return redirect()->route('events.index');
    }

    public function show($event)
    {
        // Return a view for showing an event
        return view('events.show', compact('event'));
    }

    public function edit($event)
    {
        // Return a view for editing an event
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, $event)
    {
        // Handle updating an event
        // Example: $event->update($request->all());
        return redirect()->route('events.index');
    }

    public function destroy($event)
    {
        // Handle deleting an event
        // Example: $event->delete();
        return redirect()->route('events.index');
    }
}
