<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LayoutTemplate;

class ListLayouts extends Command
{
    protected $signature = 'layout:list';
    protected $description = 'List all available layout templates';

    public function handle()
    {
        $this->info('Available Layout Templates:');

        $templates = LayoutTemplate::all();

        if ($templates->isEmpty()) {
            $this->warn('No layout templates found in database.');
            return;
        }

        foreach ($templates as $template) {
            $this->line("- {$template->slug} ({$template->name})");
        }
    }
}
