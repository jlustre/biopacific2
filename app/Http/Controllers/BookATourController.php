<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\BookATourMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\EmailRecipient;

class BookATourController extends Controller
{
    // ...existing methods...

    public function sendBookATourEmail(array $formData)
    {
        Log::info('Sending Book a Tour email', $formData); // Debugging line

        $recipients = EmailRecipient::where('category', 'book-a-tour')
                                    ->where('facility_id', 1) // Added facility_id condition
                                    ->pluck('email'); // Updated category

        Log::info('Recipients for Book a Tour email', ['recipients' => $recipients]); // Debugging recipients

        foreach ($recipients as $recipient) {
            Log::info('Sending email to recipient', ['email' => $recipient]); // Debugging recipient
            Mail::to($recipient)->send(new BookATourMail($formData));
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'preferred_date' => 'required|date',
            'message' => 'nullable|string',
        ]);

        // Call the email sending logic
        $this->sendBookATourEmail($validatedData);

        return response()->json(['message' => 'Your request has been sent successfully.'], 200);
    }
}