<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facility;
use App\Models\LayoutTemplate;
use App\Models\LayoutSection;

class LayoutBuilderStatus extends Command
{
    protected $signature = 'layout:status';
    protected $description = 'Check Layout Builder system status';

    public function handle()
    {
        $this->info('=== Layout Builder System Status ===');
        $this->newLine();

        // Check Facilities
        $facilities = Facility::count();
        $facilitiesWithTemplates = Facility::whereNotNull('layout_template')->count();
        $this->line("📋 Facilities: {$facilities} total, {$facilitiesWithTemplates} have templates");

        // Check Templates
        $templates = LayoutTemplate::where('is_active', true)->count();
        $this->line("🎨 Active Templates: {$templates}");

        // Check Sections
        $sections = LayoutSection::where('is_active', true)->count();
        $this->line("🧩 Active Sections: {$sections}");

        $this->newLine();

        // List Templates
        if ($templates > 0) {
            $this->info('Available Templates:');
            LayoutTemplate::where('is_active', true)->each(function($template) {
                $sectionCount = count($template->sections ?? []);
                $this->line("  • {$template->name} ({$template->slug}) - {$sectionCount} sections");
            });
            $this->newLine();
        }

        // List Sections
        if ($sections > 0) {
            $this->info('Available Sections:');
            LayoutSection::where('is_active', true)->each(function($section) {
                $variantCount = count($section->variants ?? []);
                $this->line("  • {$section->name} ({$section->slug}) - {$variantCount} variants");
            });
            $this->newLine();
        }

        // Sample Facility Check
        $sampleFacility = Facility::whereNotNull('layout_template')->first();
        if ($sampleFacility) {
            $this->info("Sample Facility: {$sampleFacility->name}");
            $this->line("  Template: {$sampleFacility->layout_template}");
            $configCount = count($sampleFacility->layout_config ?? []);
            $this->line("  Layout Config: {$configCount} sections configured");
            $this->newLine();
        }

        // URLs
        $this->info('Access URLs:');
        $this->line('  Layout Builder: http://127.0.0.1:8000/admin/layout-builder');
        $this->line('  Admin Dashboard: http://127.0.0.1:8000/admin');

        $this->newLine();
        $this->info('✅ Layout Builder is ready to use!');

        return 0;
    }
}
