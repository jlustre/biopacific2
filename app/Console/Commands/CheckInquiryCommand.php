<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inquiry;

class CheckInquiryCommand extends Command
{
    protected $signature = 'check:inquiry {inquiry_id}';
    protected $description = 'Check inquiry details';

    public function handle()
    {
        $inquiryId = $this->argument('inquiry_id');
        $inquiry = Inquiry::find($inquiryId);
        
        if (!$inquiry) {
            $this->error("Inquiry #{$inquiryId} not found");
            return;
        }
        
        $this->info("Inquiry #{$inquiry->id} Details:");
        $this->line("Facility: {$inquiry->facility->name}");
        $this->line("Created: {$inquiry->created_at}");
        $this->line("Access Token: " . ($inquiry->access_token ? 'Present (' . strlen($inquiry->access_token) . ' chars)' : 'Missing'));
        $this->line("Token Expires: " . ($inquiry->token_expires_at ? $inquiry->token_expires_at->format('Y-m-d H:i:s') : 'Not set'));
        $this->line("Is Encrypted: " . ($inquiry->is_encrypted ? 'Yes' : 'No'));
        $this->line("Full Name: " . ($inquiry->full_name ? 'Present' : 'Missing'));
        $this->line("Email: " . ($inquiry->email ? 'Present' : 'Missing'));
        $this->line("Message: " . ($inquiry->message ? 'Present' : 'Missing'));
        
        // Test secure URL
        if ($inquiry->access_token) {
            $secureUrl = $inquiry->getSecureAccessUrl();
            $this->line("Secure URL: {$secureUrl}");
        }
    }
}