<?php
namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\RunScheduledReports::class,
        \App\Console\Commands\CleanupOrphanedUploads::class,
    ];

    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
    {
        // Laravel 11+ loads schedules from routes/console.php.
        // Keep this empty so report + backup jobs are not double-registered.
    }
}
