<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facility;
use Illuminate\Support\Str;

class FixFacilityDomains extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'facility:fix-domains';

    /**
     * The console command description.
     */
    protected $description = 'Fix facilities with null or empty domain values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for facilities with null or empty domain values...');

        $facilitiesWithoutDomain = Facility::whereNull('domain')
            ->orWhere('domain', '')
            ->get();

        if ($facilitiesWithoutDomain->isEmpty()) {
            $this->info('All facilities have valid domain values.');
            return 0;
        }

        $this->info("Found {$facilitiesWithoutDomain->count()} facilities without proper domain values.");

        foreach ($facilitiesWithoutDomain as $index => $facility) {
            $slug = $facility->slug ?: Str::slug($facility->name) ?: 'facility' . ($index + 1);
            $domain = $slug . '.example.com';

            $facility->update(['domain' => $domain]);

            $this->line("Updated facility '{$facility->name}' with domain: {$domain}");
        }

        $this->info('Successfully updated all facilities with proper domain values.');
        return 0;
    }
}
