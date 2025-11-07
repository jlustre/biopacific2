<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inquiry;
use App\Models\Facility;
use App\Support\HipaaWebsiteChecklist;

class TestSecureEphiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:secure-ephi {--production : Test in production mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test secure ePHI implementation and HIPAA compliance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Secure ePHI Implementation');
        $this->newLine();

        // Test 1: Check if classes exist
        $this->info('1. Checking Implementation Classes:');
        $classes = [
            'EncryptsEphi Trait' => \App\Traits\EncryptsEphi::class,
            'SecureContactMail' => \App\Mail\SecureContactMail::class,
            'SecureInquiryController' => \App\Http\Controllers\SecureInquiryController::class,
        ];

        foreach ($classes as $name => $class) {
            if (class_exists($class)) {
                $this->line("   ✅ {$name}: EXISTS");
            } else {
                $this->error("   ❌ {$name}: MISSING");
            }
        }

        // Test 2: Check routes
        $this->newLine();
        $this->info('2. Checking Secure Routes:');
        
        try {
            $secureRoute = route('secure.inquiry.view', ['token' => 'test-token']);
            $this->line("   ✅ Secure inquiry route: CONFIGURED");
        } catch (\Exception $e) {
            $this->error("   ❌ Secure inquiry route: MISSING");
        }

        try {
            $adminRoute = route('admin.secure-inquiries.index');
            $this->line("   ✅ Admin secure inquiries route: CONFIGURED");
        } catch (\Exception $e) {
            $this->error("   ❌ Admin secure inquiries route: MISSING");
        }

        // Test 3: Test encryption functionality
        $this->newLine();
        $this->info('3. Testing Encryption Functionality:');
        
        if ($this->option('production')) {
            app()->detectEnvironment(function () {
                return 'production';
            });
        }

        // Create a test facility if none exists
        $facility = Facility::first();
        if (!$facility) {
            $this->line("   ⚠️  No facilities found, skipping encryption test");
        } else {
            try {
                // Create test inquiry
                $testData = [
                    'facility_id' => $facility->id,
                    'recipient' => 'inquiry',
                    'full_name' => 'Test User',
                    'email' => 'test@example.com',
                    'phone' => '555-123-4567',
                    'message' => 'This is a test message',
                    'consent' => true,
                    'no_phi' => true,
                ];

                $inquiry = Inquiry::create($testData);
                
                if ($inquiry->isEncrypted()) {
                    $this->line("   ✅ Data encryption: WORKING");
                    $this->line("      Encryption key hint: " . $inquiry->encryption_key_hint);
                } else {
                    $this->line("   ℹ️  Data encryption: DISABLED (environment: " . app()->environment() . ")");
                }

                // Test token generation
                $token = $inquiry->generateSecureAccessToken();
                if ($token && $inquiry->access_token) {
                    $this->line("   ✅ Token generation: WORKING");
                    $this->line("      Token expires: " . $inquiry->token_expires_at);
                } else {
                    $this->error("   ❌ Token generation: FAILED");
                }

                // Test safe data extraction
                $safeData = $inquiry->getSafeDataForEmail();
                if (is_array($safeData) && isset($safeData['facility_name'])) {
                    $this->line("   ✅ Safe data extraction: WORKING");
                } else {
                    $this->error("   ❌ Safe data extraction: FAILED");
                }

                // Clean up test data
                $inquiry->delete();
                
            } catch (\Exception $e) {
                $this->error("   ❌ Encryption test failed: " . $e->getMessage());
            }
        }

        // Test 4: HIPAA Compliance Check
        $this->newLine();
        $this->info('4. HIPAA Compliance Check:');
        
        $checklist = HipaaWebsiteChecklist::forFacility([], []);
        $formsSecureItem = collect($checklist)->firstWhere('key', 'forms_secure');
        
        if ($formsSecureItem && $formsSecureItem['passed']) {
            $this->line("   ✅ Forms secure ePHI check: PASSED");
        } else {
            $this->error("   ❌ Forms secure ePHI check: FAILED");
        }

        // Test 5: Configuration
        $this->newLine();
        $this->info('5. Configuration Check:');
        
        $appKey = config('app.key');
        if ($appKey && !empty($appKey)) {
            $this->line("   ✅ App encryption key: CONFIGURED");
        } else {
            $this->error("   ❌ App encryption key: MISSING");
        }

        $forceEncryption = config('app.force_ephi_encryption');
        $this->line("   ℹ️  Force ePHI encryption: " . ($forceEncryption ? 'ENABLED' : 'DISABLED'));

        // Summary
        $this->newLine();
        $this->info('🛡️  Secure ePHI Implementation Test Complete');
        
        if ($facility && $formsSecureItem && $formsSecureItem['passed']) {
            $this->info('✅ System is ready for HIPAA-compliant form handling!');
        } else {
            $this->warn('⚠️  Some components may need attention for full compliance.');
        }

        return 0;
    }
}
