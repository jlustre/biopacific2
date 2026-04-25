<?php
// Run this script with: php artisan orphaned:uploads

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Upload;

class CleanupOrphanedUploads extends Command
{
    protected $signature = 'orphaned:uploads';
    protected $description = 'Delete files in storage/app/public/uploads with no matching Upload record.';

    public function handle()
    {
        $disk = Storage::disk('public');
        $allFiles = $disk->files('uploads');
        $dbFiles = Upload::pluck('file_path')->toArray();
        $orphans = array_diff($allFiles, $dbFiles);
        if (empty($orphans)) {
            $this->info('No orphaned files found.');
            return 0;
        }
        foreach ($orphans as $file) {
            $disk->delete($file);
            $this->info("Deleted orphaned file: $file");
        }
        $this->info('Cleanup complete.');
        return 0;
    }
}
