<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSimpleEmailCommand extends Command
{
    protected $signature = 'test:simple-email {email}';
    protected $description = 'Send a simple test email';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('This is a simple test email from Laravel to verify Mailpit connection.', function($message) use ($email) {
                $message->to($email)
                        ->subject('Laravel Simple Test Email - ' . now());
            });
            
            $this->info("✅ Simple test email sent to: {$email}");
            $this->line("Check Mailpit at: http://127.0.0.1:8025");
            
        } catch (\Exception $e) {
            $this->error("❌ Error sending email: " . $e->getMessage());
        }
    }
}