<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\BookATourMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Helpers\FacilityDataHelper;

class BookATourController extends Controller
{
    // ...existing methods...

    public function sendBookATourEmail(array $formData, $facilityId = 1)
    {
        Log::info('Sending Book a Tour email', $formData); // Debugging line

        // Get employee emails only (not public-facing emails)
        $recipientData = FacilityDataHelper::getAllRecipientsForCategory($facilityId, 'book-a-tour');
        $recipients = $recipientData['employee_emails'] ?? [];

        Log::info('Employee recipients for Book a Tour email', ['recipients' => $recipients]); // Debugging recipients

        foreach ($recipients as $recipient) {
            Log::info('Sending email to employee recipient', ['email' => $recipient]); // Debugging recipient
            Mail::to($recipient)->send(new BookATourMail($formData));
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'preferred_date' => 'required|date',
            'message' => 'nullable|string',
            'facility_id' => 'nullable|integer|exists:facilities,id',
        ]);

        $facilityId = $validatedData['facility_id'] ?? 1; // Default to facility 1

        // Call the email sending logic with facility ID
        $this->sendBookATourEmail($validatedData, $facilityId);

        return response()->json(['message' => 'Your request has been sent successfully.'], 200);
    }
}