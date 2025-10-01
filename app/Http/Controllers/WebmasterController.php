<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\WebmasterContact;

class WebmasterController extends Controller
{
    public function submit(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
            'urgent' => 'nullable|boolean',
            'screenshots' => 'nullable|array|max:5',
            'screenshots.*' => 'image|mimes:jpg,jpeg,png,gif|max:5120', // 5MB per file
        ]);

        // Handle file uploads
        $screenshotPaths = [];
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('webmaster_screenshots');
                    $screenshotPaths[] = $path;
                }
            }
        }

        // Store in database
        $contact = WebmasterContact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'urgent' => $request->has('urgent'),
            'screenshots' => $screenshotPaths,
        ]);

        // Send email to webmaster (set in .env or config)
        $webmasterEmail = config('mail.webmaster_address', env('WEBMASTER_EMAIL', 'webmaster@example.com'));
        try {
            $body = "Webmaster Contact Form\n"
                . "Name: {$validated['name']}\n"
                . "Email: {$validated['email']}\n"
                . "Subject: {$validated['subject']}\n"
                . "Urgent: " . ($request->has('urgent') ? 'Yes' : 'No') . "\n"
                . "Message:\n{$validated['message']}\n";
            if (count($screenshotPaths)) {
                $body .= "\nScreenshots attached in admin panel.";
            }
            Mail::raw($body, function ($message) use ($webmasterEmail, $validated) {
                $message->to($webmasterEmail)
                    ->subject('[Webmaster Contact] ' . $validated['subject']);
            });
        } catch (\Exception $e) {
            Log::error('Webmaster contact form failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to send message. Please try again later.']);
        }

        return back()->with('success', 'Your message has been sent to the webmaster and stored for review. Thank you!');
    }
}
