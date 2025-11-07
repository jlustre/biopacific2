<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecureContactMail;
use App\Models\Inquiry;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email';
    protected $description = 'Test email sending to Mailpit';

    public function handle()
    {
        $this->info('Testing email sending...');

        try {
            // Test 1: Simple raw email
            $this->info('1. Sending simple test email...');
            Mail::raw('This is a test email to verify Mailpit is working.', function($message) {
                $message->to('test@example.com')
                        ->subject('Laravel Test Email - ' . now());
            });
            $this->line('✅ Simple email sent');

            // Test 2: Test SecureContactMail
            $this->info('2. Testing SecureContactMail (like from contact form)...');
            
            // Create a test inquiry
            $inquiry = Inquiry::create([
                'facility_id' => 1,
                'full_name' => 'Test User',
                'phone' => '555-123-4567',
                'email' => 'testuser@example.com',
                'message' => 'This is a test inquiry message.',
                'consent' => true,
                'no_phi' => true,
                'recipient' => 'inquiry'
            ]);

            // Send secure email
            Mail::to('staff@example.com')->send(new SecureContactMail($inquiry));
            $this->line('✅ SecureContactMail sent');

            // Clean up test data
            $inquiry->delete();
            $this->line('✅ Test data cleaned up');

            $this->newLine();
            $this->info('📧 Test emails sent successfully!');
            $this->line('Check Mailpit at: http://127.0.0.1:8025');

        } catch (\Exception $e) {
            $this->error('❌ Email test failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}