<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Facility;
use App\Helpers\FacilityDataHelper;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Honeypot check
        if ($request->filled('website')) {
            return redirect()->back()->with('error', 'Form submission failed.');
        }

        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:2000',
            'consent' => 'required|accepted',
            'no_phi' => 'required|accepted'
        ], [
            'facility_id.required' => 'Facility information is required.',
            'full_name.required' => 'Full name is required.',
            'phone.required' => 'Phone number is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'message.required' => 'Message is required.',
            'consent.required' => 'You must consent to be contacted.',
            'consent.accepted' => 'You must consent to be contacted.',
            'no_phi.required' => 'You must confirm no PHI is included.',
            'no_phi.accepted' => 'You must confirm no PHI is included.'
        ]);

        try {
            // Save the inquiry to database
            $inquiry = Inquiry::create([
                'facility_id' => $validated['facility_id'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'message' => $validated['message'],
                'consent' => true,
                'no_phi' => true,
                'recipient' => 'inquiry' // This helps categorize the inquiry
            ]);

            // Get facility and send email using the existing system
            $facility = Facility::find($validated['facility_id']);
            
            if ($facility) {
                // Use FacilityDataHelper to get the correct recipients
                $recipientData = FacilityDataHelper::getAllRecipientsForCategory($facility, 'inquiry');
                
                // Extract ONLY employee email addresses (not public-facing emails)  
                $emailAddresses = $recipientData['employee_emails'] ?? [];
                
                if (!empty($emailAddresses)) {
                    // Prepare data for the existing ContactMail format
                    $emailData = [
                        'full_name' => $validated['full_name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'] ?? '',
                        'message' => $validated['message'],
                        'facility' => [
                            'name' => $facility->name,
                            'slug' => $facility->slug
                        ]
                    ];
                    
                    // Send email to each recipient
                    foreach ($emailAddresses as $emailAddress) {
                        Mail::to($emailAddress)->send(new ContactMail($emailData));
                    }
                    
                    Log::info('Contact form inquiry sent', [
                        'inquiry_id' => $inquiry->id,
                        'facility' => $facility->name,
                        'email_addresses' => $emailAddresses,
                        'warnings' => $recipientData['warnings'] ?? []
                    ]);
                } else {
                    Log::warning('No recipients found for inquiry', [
                        'facility_id' => $facility->id,
                        'facility_name' => $facility->name,
                        'recipient_data' => $recipientData
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Thank you for your message! We\'ll get back to you promptly.');
            
        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'There was an issue submitting your message. Please try again or contact us directly.');
        }
    }
}