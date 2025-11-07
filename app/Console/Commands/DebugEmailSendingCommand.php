<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inquiry;
use App\Mail\SecureContactMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class DebugEmailSendingCommand extends Command
{
    protected $signature = 'debug:email-sending {inquiry_id}';
    protected $description = 'Debug email sending process';

    public function handle()
    {
        $inquiryId = $this->argument('inquiry_id');
        
        // Show mail configuration
        $this->info('Current Mail Configuration:');
        $this->line('Driver: ' . config('mail.default'));
        $this->line('Host: ' . config('mail.mailers.smtp.host'));
        $this->line('Port: ' . config('mail.mailers.smtp.port'));
        $this->line('From Address: ' . config('mail.from.address'));
        $this->line('From Name: ' . config('mail.from.name'));
        $this->newLine();
        
        // Get inquiry
        $inquiry = Inquiry::find($inquiryId);
        if (!$inquiry) {
            $this->error("Inquiry #{$inquiryId} not found");
            return 1;
        }
        
        $this->info("Testing email for Inquiry #{$inquiry->id}");
        
        // Test with Mail::fake() to see what would be sent
        Mail::fake();
        
        try {
            $testEmails = [
                'lisa.martinez@biopacific.com',
                'tom.wilson@biopacific.com',
                'jerrickl06@gmail.com'
            ];
            
            foreach ($testEmails as $email) {
                $this->line("Attempting to send to: {$email}");
                Mail::to($email)->send(new SecureContactMail($inquiry));
                $this->line("✅ Mail queued successfully");
            }
            
            // Check what was sent
            $this->newLine();
            $this->info('Mail Fake Results:');
            
            Mail::assertSent(SecureContactMail::class, function ($mail) use ($testEmails) {
                $this->line("Mail was prepared for sending");
                return true;
            });
            
            $this->line('✅ SecureContactMail class is working correctly');
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->line("Stack trace:");
            $this->line($e->getTraceAsString());
        }
        
        // Now test real sending
        $this->newLine();
        $this->info('Testing REAL email sending to your address...');
        
        // Restore real mail config
        Mail::swap(app('mail.manager'));
        
        try {
            Mail::to('jerrickl06@gmail.com')->send(new SecureContactMail($inquiry));
            $this->info('✅ Real email sent successfully');
            
        } catch (\Exception $e) {
            $this->error("❌ Real email failed: " . $e->getMessage());
        }
        
        return 0;
    }
}