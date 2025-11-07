<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TourRequest;

class TestTourRequestViewCommand extends Command
{
    protected $signature = 'test:tour-request-view {tour_request_id}';
    protected $description = 'Test tour request view data';

    public function handle()
    {
        $tourRequestId = $this->argument('tour_request_id');
        
        $tourRequest = TourRequest::find($tourRequestId);
        
        if (!$tourRequest) {
            $this->error("Tour request #{$tourRequestId} not found");
            return 1;
        }
        
        $this->info("Tour Request #{$tourRequest->id} View Data:");
        $this->line("Created: {$tourRequest->created_at}");
        $this->line("Facility: {$tourRequest->facility->name}");
        $this->line("Access Token: " . ($tourRequest->access_token ? 'Present' : 'Missing'));
        $this->line("Full Name: " . ($tourRequest->full_name ? 'Present' : 'Missing'));
        $this->line("Email: " . ($tourRequest->email ? 'Present' : 'Missing'));
        $this->line("Phone: " . ($tourRequest->phone ? 'Present' : 'Missing'));
        $this->line("Preferred Date: {$tourRequest->preferred_date}");
        $this->line("Preferred Time: {$tourRequest->preferred_time}");
        $this->line("Interests: " . (is_array($tourRequest->interests) ? implode(', ', $tourRequest->interests) : 'None'));
        $this->line("Message: " . ($tourRequest->message ? 'Present' : 'None'));
        $this->line("Expires At: " . ($tourRequest->expires_at ? $tourRequest->expires_at->format('Y-m-d H:i:s') : 'Not set'));
        
        // Test if the secure URL method works
        try {
            $secureUrl = $tourRequest->getSecureAccessUrl();
            $this->line("Secure URL: {$secureUrl}");
        } catch (\Exception $e) {
            $this->error("Error generating secure URL: " . $e->getMessage());
        }
        
        // Test if the tour request is accessible
        $isAccessible = $tourRequest->isAccessible();
        $this->line("Is Accessible: " . ($isAccessible ? 'Yes' : 'No'));
        
        return 0;
    }
}