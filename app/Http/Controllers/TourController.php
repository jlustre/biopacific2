<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Models\Service;

class TourController extends Controller
{
    public function showForm()
    {
        // Fetch active services for dynamic interests
        $services = Service::where('is_active', true)->orderBy('order')->get();
        return view('partials.book.book1', compact('services'));
    }

    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:32',
            'email' => 'required|email|max:255',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|string',
            'guests' => 'nullable|integer|min:1|max:6',
            'relationship' => 'nullable|string|max:64',
            'interests' => 'nullable|array',
            'access' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'consent' => 'accepted',
        ]);

        // TODO: Save to database or send email
        // Example: Mail::to('admin@example.com')->send(new TourRequestMail($validated));

        return redirect()->back()->with('success', 'Your tour request has been submitted!');
    }
}
