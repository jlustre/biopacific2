<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Models\Service;
use App\Models\Facility;

class TourController extends Controller
{
    public function showForm(Facility $facility, $view = 'book1')
    {
        // Fetch active services for dynamic interests
        $services = Service::orderBy('name')->get();
        return view('partials.book.' . $view, compact('services', 'facility'));
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
        try {
            // Example mail send (uncomment and replace with your real mailable)
            // Mail::to('admin@example.com')->send(new TourRequestMail($validated));

            // If using a driver that supports failures(), check it
                Log::error('Tour request mail failures', ['failures' => Mail::failures(), 'data' => $validated]);
                return redirect()->back()->withInput()->with('error', 'Unable to send your request. Please try again later.');
        } catch (\Exception $e) {
            if (config('app.debug')) {
                return redirect()->back()->withInput()->with('error', 'Unable to send your request. Please try again later.');
            }
            Log::error('Error sending tour request mail: '.$e->getMessage(), ['exception' => $e, 'data' => $validated]);
            return redirect()->back()->withInput()->with('error', 'An error occurred while sending your request. Please try again later.');
        }

        // TODO: Save to database or send email
        // Example: Mail::to('admin@example.com')->send(new TourRequestMail($validated));

        return redirect()->back()->with('success', 'Your tour request has been submitted!');
    }
}
