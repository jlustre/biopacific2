<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailRecipient;
use App\Models\Facility;

class EmailRecipientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EmailRecipient::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('facility')) {
            $query->where('facility_id', $request->facility);
        }

        $emailRecipients = $query->paginate(10);
        $facilities = Facility::all();

        return view('admin.email-recipients.index', compact('emailRecipients', 'facilities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $facilities = Facility::all();
        return view('admin.email-recipients.create', compact('facilities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        EmailRecipient::create($validated);

        return redirect()->route('admin.email-recipients.index')->with('success', 'Email recipient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $emailRecipient = EmailRecipient::findOrFail($id);
        return view('admin.email-recipients.show', compact('emailRecipient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $emailRecipient = EmailRecipient::findOrFail($id);
        $facilities = Facility::all();
        return view('admin.email-recipients.edit', compact('emailRecipient', 'facilities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $emailRecipient = EmailRecipient::findOrFail($id);
        $emailRecipient->update($validated);

        return redirect()->route('admin.email-recipients.index')->with('success', 'Email recipient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $emailRecipient = EmailRecipient::findOrFail($id);
        $emailRecipient->delete();

        return redirect()->route('admin.email-recipients.index')->with('success', 'Email recipient deleted successfully.');
    }
}
