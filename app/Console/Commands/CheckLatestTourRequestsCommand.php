<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TourRequest;

class CheckLatestTourRequestsCommand extends Command
{
    protected $signature = 'check:latest-tour-requests {--count=5}';
    protected $description = 'Check the latest tour requests';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Latest {$count} Tour Requests:");
        $this->line(str_repeat('-', 80));
        
        $tourRequests = TourRequest::with('facility')
            ->orderBy('created_at', 'desc')
            ->take($count)
            ->get();
            
        if ($tourRequests->isEmpty()) {
            $this->warn('No tour requests found');
            return;
        }
        
        foreach ($tourRequests as $request) {
            $this->line("ID: #{$request->id}");
            $this->line("Facility: {$request->facility->name}");
            $this->line("Created: {$request->created_at}");
            $this->line("Access Token: " . ($request->access_token ? 'Present (' . strlen($request->access_token) . ' chars)' : 'Missing'));
            $this->line("Expires At: " . ($request->expires_at ? $request->expires_at->format('Y-m-d H:i:s') : 'Not set'));
            if ($request->access_token) {
                $secureUrl = $request->getSecureAccessUrl();
                $this->line("Secure URL: {$secureUrl}");
            }
            $this->line(str_repeat('-', 40));
        }
    }
}