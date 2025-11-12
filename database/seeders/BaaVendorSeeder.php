<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BaaVendor;
use Illuminate\Support\Facades\File;

class BaaVendorSeeder extends Seeder
{
    public function run()
    {
        $path = base_path('docs/BAA_VENDOR_REGISTRY.md');
        $content = File::get($path);
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (preg_match('/^\|([^|]+)\|([^|]+)\|([^|]+)\|([^|]+)\|([^|]+)\|$/', trim($line), $matches)) {
                BaaVendor::create([
                    'vendor_service' => trim($matches[1]),
                    'type' => trim($matches[2]),
                    'ephi_access' => trim($matches[3]),
                    'baa_status' => trim($matches[4]),
                    'notes' => trim($matches[5]),
                ]);
            }
        }
    }
}
