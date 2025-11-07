<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inquiry;

class CheckLatestInquiriesCommand extends Command
{
    protected $signature = 'check:latest-inquiries {--count=5}';
    protected $description = 'Check the latest inquiries';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Latest {$count} Inquiries:");
        $this->line(str_repeat('-', 80));
        
        $inquiries = Inquiry::with('facility')
            ->orderBy('created_at', 'desc')
            ->take($count)
            ->get();
            
        if ($inquiries->isEmpty()) {
            $this->warn('No inquiries found');
            return;
        }
        
        foreach ($inquiries as $inquiry) {
            $this->line("ID: #{$inquiry->id}");
            $this->line("Facility: {$inquiry->facility->name}");
            $this->line("Created: {$inquiry->created_at}");
            $this->line("Access Token: " . ($inquiry->access_token ? 'Present' : 'Missing'));
            $this->line("Is Encrypted: " . ($inquiry->is_encrypted ? 'Yes' : 'No'));
            $this->line(str_repeat('-', 40));
        }
    }
}