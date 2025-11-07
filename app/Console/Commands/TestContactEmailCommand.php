<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inquiry;
use App\Mail\SecureContactMail;
use Illuminate\Support\Facades\Mail;

class TestContactEmailCommand extends Command
{
    protected $signature = 'test:contact-email {inquiry_id}';
    protected $description = 'Test contact form email sending';

    public function handle()
    {
        $inquiryId = $this->argument('inquiry_id');
        
        try {
            $inquiry = Inquiry::find($inquiryId);
            
            if (!$inquiry) {
                $this->error("Inquiry #{$inquiryId} not found");
                return 1;
            }
            
            $this->info("Testing SecureContactMail for Inquiry #{$inquiry->id}");
            $this->line("Facility: {$inquiry->facility->name}");
            $this->line("Created: {$inquiry->created_at}");
            
            // Test email to your address
            $testEmail = 'jerrickl06@gmail.com';
            
            $this->info("Sending test email to: {$testEmail}");
            
            Mail::to($testEmail)->send(new SecureContactMail($inquiry));
            
            $this->info("✅ Email sent successfully!");
            $this->line("Check Mailpit at: http://127.0.0.1:8025");
            
            // Show the secure URL that should be in the email
            $secureUrl = $inquiry->getSecureAccessUrl();
            $this->line("Secure URL: {$secureUrl}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error sending email: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}