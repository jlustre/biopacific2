<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecureAccessLog;
use App\Models\Inquiry;
use App\Models\TourRequest;
use App\Models\JobApplication;
use App\Models\AuditLog;

class CleanTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:clean-test-data 
                            {--confirm : Confirm deletion without prompting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all test data from security-related tables for fresh start';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Security Test Data Cleanup Tool');
        $this->info('===================================');

        // Show current counts
        $this->showCurrentCounts();

        // Confirm deletion
        if (!$this->option('confirm')) {
            if (!$this->confirm('This will DELETE ALL data from security tables. Are you sure?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('🗑️ Starting cleanup...');

        try {
            // Clean up in proper order to respect foreign key constraints
            $this->cleanSecureAccessLogs();
            $this->cleanAuditLogs();
            $this->cleanJobApplications();
            $this->cleanTourRequests();
            $this->cleanInquiries();

            $this->info('');
            $this->info('✅ Cleanup completed successfully!');
            $this->showCurrentCounts();

        } catch (\Exception $e) {
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function showCurrentCounts()
    {
        $this->info('');
        $this->info('📊 Current record counts:');
        $this->table(
            ['Table', 'Count'],
            [
                ['SecureAccessLog', SecureAccessLog::count()],
                ['AuditLog', AuditLog::count()],
                ['JobApplication', JobApplication::count()],
                ['TourRequest', TourRequest::count()],
                ['Inquiry', Inquiry::count()],
            ]
        );
    }

    private function cleanSecureAccessLogs()
    {
        $count = SecureAccessLog::count();
        if ($count > 0) {
            SecureAccessLog::truncate();
            $this->info("🗑️ Deleted {$count} SecureAccessLog records");
        } else {
            $this->info("✓ SecureAccessLog table already empty");
        }
    }

    private function cleanAuditLogs()
    {
        $count = AuditLog::count();
        if ($count > 0) {
            AuditLog::truncate();
            $this->info("🗑️ Deleted {$count} AuditLog records");
        } else {
            $this->info("✓ AuditLog table already empty");
        }
    }

    private function cleanJobApplications()
    {
        $count = JobApplication::count();
        if ($count > 0) {
            JobApplication::truncate();
            $this->info("🗑️ Deleted {$count} JobApplication records");
        } else {
            $this->info("✓ JobApplication table already empty");
        }
    }

    private function cleanTourRequests()
    {
        $count = TourRequest::count();
        if ($count > 0) {
            TourRequest::truncate();
            $this->info("🗑️ Deleted {$count} TourRequest records");
        } else {
            $this->info("✓ TourRequest table already empty");
        }
    }

    private function cleanInquiries()
    {
        $count = Inquiry::count();
        if ($count > 0) {
            Inquiry::truncate();
            $this->info("🗑️ Deleted {$count} Inquiry records");
        } else {
            $this->info("✓ Inquiry table already empty");
        }
    }
}