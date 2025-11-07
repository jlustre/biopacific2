<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Facility;
use App\Mail\SecureJobApplicationMail;
use App\Support\HipaaWebsiteChecklist;
use App\Traits\EncryptsEphi;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TestSecureJobApplicationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:secure-job-application {--test-email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test secure ePHI job application implementation and HIPAA compliance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Secure Job Application ePHI Implementation');
        $this->line('================================================');

        // Test 1: Model Configuration
        $this->newLine();
        $this->info('1. Model Configuration Check:');
        
        $uses = class_uses(JobApplication::class);
        if (in_array(EncryptsEphi::class, $uses)) {
            $this->line("   ✅ JobApplication model uses EncryptsEphi trait");
        } else {
            $this->error("   ❌ JobApplication model missing EncryptsEphi trait");
        }

        // Test database schema
        $requiredColumns = ['access_token', 'expires_at', 'audit_log', 'viewed_at'];
        foreach ($requiredColumns as $column) {
            if (Schema::hasColumn('job_applications', $column)) {
                $this->line("   ✅ Database has '$column' column");
            } else {
                $this->error("   ❌ Database missing '$column' column");
            }
        }

        // Test 2: Create Test Application
        $this->newLine();
        $this->info('2. Create Test Job Application:');
        
        // Find a facility and job opening
        $facility = Facility::first();
        if (!$facility) {
            $this->error("   ❌ No facility found - cannot test");
            return;
        }

        $jobOpening = JobOpening::where('facility_id', $facility->id)->first();
        if (!$jobOpening) {
            $this->error("   ❌ No job opening found - cannot test");
            return;
        }

        $testData = [
            'job_opening_id' => $jobOpening->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '555-123-4567',
            'cover_letter' => 'I am very interested in this position and believe I would be a great fit.',
            'status' => 'pending'
        ];

        $jobApplication = JobApplication::create($testData);
        $this->line("   ✅ Test job application created with ID: {$jobApplication->id}");

        // Test 3: Encryption
        $this->newLine();
        $this->info('3. Encryption Test:');
        
        // Check if data is encrypted in database
        $rawRecord = DB::table('job_applications')->where('id', $jobApplication->id)->first();
        
        $ephiFields = ['first_name', 'last_name', 'email', 'phone', 'cover_letter'];
        foreach ($ephiFields as $field) {
            if ($rawRecord->$field !== $testData[$field]) {
                $this->line("   ✅ Field '$field' is encrypted in database");
            } else {
                $this->error("   ❌ Field '$field' is NOT encrypted in database");
            }
        }

        // Verify decryption works
        $decryptedApplication = JobApplication::find($jobApplication->id);
        foreach ($ephiFields as $field) {
            if ($decryptedApplication->$field === $testData[$field]) {
                $this->line("   ✅ Field '$field' decrypts correctly");
            } else {
                $this->error("   ❌ Field '$field' does NOT decrypt correctly");
            }
        }

        // Test 4: Token Generation
        $this->newLine();
        $this->info('4. Secure Token Test:');
        
        $jobApplication->generateSecureAccessToken();
        
        if (!empty($jobApplication->access_token)) {
            $this->line("   ✅ Access token generated: " . substr($jobApplication->access_token, 0, 8) . "...");
        } else {
            $this->error("   ❌ Access token not generated");
        }

        if ($jobApplication->expires_at && $jobApplication->expires_at->isFuture()) {
            $this->line("   ✅ Expiration time set: {$jobApplication->expires_at}");
        } else {
            $this->error("   ❌ Expiration time not set properly");
        }

        // Test 5: Secure Email
        $this->newLine();
        $this->info('5. Secure Email Test:');
        
        if (class_exists(\App\Mail\SecureJobApplicationMail::class)) {
            $this->line("   ✅ SecureJobApplicationMail class exists");
        } else {
            $this->error("   ❌ SecureJobApplicationMail class missing");
        }

        // Test email content doesn't contain PHI
        $mail = new SecureJobApplicationMail($jobApplication, $facility);
        $content = $mail->content();
        
        // Check that email doesn't contain sensitive data
        $sensitiveData = [$jobApplication->first_name, $jobApplication->last_name, $jobApplication->email, $jobApplication->phone];
        $containsPhi = false;
        
        foreach ($sensitiveData as $data) {
            if (str_contains($content->html, $data) || str_contains($content->subject ?? '', $data)) {
                $containsPhi = true;
                break;
            }
        }
        
        if (!$containsPhi) {
            $this->line("   ✅ Email content contains no PHI");
        } else {
            $this->error("   ❌ Email content contains PHI");
        }

        // Test secure URL generation
        $secureUrl = route('secure.job-application', ['token' => $jobApplication->access_token]);
        if (str_contains($secureUrl, '/secure/job-application/')) {
            $this->line("   ✅ Secure URL generated: " . parse_url($secureUrl, PHP_URL_PATH));
        } else {
            $this->error("   ❌ Secure URL not generated properly");
        }

        // Test 6: HIPAA Compliance Check
        $this->newLine();
        $this->info('6. HIPAA Compliance Check:');
        
        $checklist = HipaaWebsiteChecklist::forFacility([], []);
        $formsSecureItem = collect($checklist)->firstWhere('key', 'forms_secure');
        
        if ($formsSecureItem && $formsSecureItem['passed']) {
            $this->line("   ✅ Forms secure ePHI check: PASSED");
        } else {
            $this->error("   ❌ Forms secure ePHI check: FAILED");
        }

        // Test 7: Send Test Email (if email provided)
        $testEmail = $this->option('test-email');
        if ($testEmail) {
            $this->newLine();
            $this->info('7. Send Test Email:');
            
            try {
                Mail::to($testEmail)->send(new SecureJobApplicationMail($jobApplication, $facility));
                $this->line("   ✅ Test email sent to: $testEmail");
                $this->line("   📧 Check your email for the secure link");
            } catch (\Exception $e) {
                $this->error("   ❌ Email sending failed: " . $e->getMessage());
            }
        }

        // Test 8: Access Logging
        $this->newLine();
        $this->info('8. Access Logging Test:');
        
        $testAccessData = [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'accessed_at' => Carbon::now(),
            'access_type' => 'test_access'
        ];
        
        $jobApplication->logSecureAccess($testAccessData);
        
        if (!empty($jobApplication->audit_log)) {
            $this->line("   ✅ Access logging works");
        } else {
            $this->error("   ❌ Access logging failed");
        }

        // Test 9: Cleanup
        $this->newLine();
        $this->info('9. Cleanup:');
        
        $jobApplication->delete();
        $this->line("   ✅ Test job application deleted");

        // Summary
        $this->newLine();
        $this->info('🎉 Secure Job Application Test Complete!');
        $this->line('Key Features Tested:');
        $this->line('- ✅ Model encryption configuration');
        $this->line('- ✅ Database schema with secure fields');
        $this->line('- ✅ Automatic ePHI encryption/decryption');
        $this->line('- ✅ Secure token generation');
        $this->line('- ✅ PHI-free email notifications');
        $this->line('- ✅ Secure URL generation');
        $this->line('- ✅ HIPAA compliance validation');
        $this->line('- ✅ Access logging for audit trails');
        
        if ($testEmail) {
            $this->newLine();
            $this->warn('📧 Check your email and click the secure link to verify the full workflow!');
        } else {
            $this->newLine();
            $this->info('💡 Run with --test-email=your@email.com to test email delivery');
        }

        return 0;
    }
}
