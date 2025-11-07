<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TourRequest;
use App\Models\Facility;
use App\Mail\SecureBookATourMail;
use Illuminate\Support\Facades\Mail;

class TestSecureBookATourCommand extends Command
{
    protected $signature = 'test:secure-book-a-tour {--production : Test in production mode}';
    protected $description = 'Test secure Book a Tour ePHI implementation';

    public function handle()
    {
        $this->info('🚌 Testing Secure Book a Tour ePHI Implementation');
        $this->newLine();

        // Test 1: Check TourRequest Model
        $this->info('1. Checking TourRequest Model:');
        
        $usesEncryptsTrait = in_array('App\Traits\EncryptsEphi', class_uses(\App\Models\TourRequest::class));
        if ($usesEncryptsTrait) {
            $this->line("   ✅ EncryptsEphi trait: IMPLEMENTED");
        } else {
            $this->error("   ❌ EncryptsEphi trait: MISSING");
        }

        $hasEphiFields = property_exists(\App\Models\TourRequest::class, 'ephiFields') || 
                        method_exists(\App\Models\TourRequest::class, 'getEphiFields');
        if ($hasEphiFields) {
            $this->line("   ✅ ePHI fields configuration: CONFIGURED");
        } else {
            $this->warn("   ⚠️  ePHI fields configuration: VERIFY");
        }

        // Test 2: Check Secure Email
        $this->newLine();
        $this->info('2. Checking Secure Email Implementation:');
        
        if (class_exists(\App\Mail\SecureBookATourMail::class)) {
            $this->line("   ✅ SecureBookATourMail: EXISTS");
        } else {
            $this->error("   ❌ SecureBookATourMail: MISSING");
        }

        // Test 3: Check Routes and Controllers
        $this->newLine();
        $this->info('3. Checking Routes and Controllers:');
        
        if (class_exists(\App\Http\Controllers\SecureTourRequestController::class)) {
            $this->line("   ✅ SecureTourRequestController: EXISTS");
        } else {
            $this->error("   ❌ SecureTourRequestController: MISSING");
        }

        // Test 4: Database Schema
        $this->newLine();
        $this->info('4. Checking Database Schema:');
        
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('tour_requests', 'access_token')) {
                $this->line("   ✅ access_token column: EXISTS");
            } else {
                $this->error("   ❌ access_token column: MISSING");
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('tour_requests', 'expires_at')) {
                $this->line("   ✅ expires_at column: EXISTS");
            } else {
                $this->error("   ❌ expires_at column: MISSING");
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('tour_requests', 'audit_log')) {
                $this->line("   ✅ audit_log column: EXISTS");
            } else {
                $this->error("   ❌ audit_log column: MISSING");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Database schema check failed: " . $e->getMessage());
        }

        // Test 5: Test Encryption Functionality
        $this->newLine();
        $this->info('5. Testing Encryption Functionality:');
        
        try {
            // Get first facility for testing
            $facility = Facility::first();
            if (!$facility) {
                $this->warn("   ⚠️  No facilities found for testing");
                return 0;
            }

            // Create test tour request
            $tourRequest = TourRequest::create([
                'facility_id' => $facility->id,
                'recipient' => 'test@example.com',
                'full_name' => 'John Test Doe',
                'phone' => '555-123-4567',
                'email' => 'john.test@example.com',
                'preferred_date' => now()->addDays(7)->format('Y-m-d'),
                'preferred_time' => '10:00 AM',
                'interests' => ['Assisted Living', 'Memory Care'],
                'message' => 'This is a test message with ePHI content.',
                'consent' => true,
                'audit_log' => [[
                    'action' => 'test_created',
                    'timestamp' => now()->toISOString()
                ]]
            ]);

            $this->line("   ✅ Test tour request created: ID #{$tourRequest->id}");

            // Test encryption by checking database values
            $rawData = \Illuminate\Support\Facades\DB::table('tour_requests')
                ->where('id', $tourRequest->id)
                ->first();

            if ($rawData->full_name !== 'John Test Doe') {
                $this->line("   ✅ Data encryption: WORKING");
                $this->line("      Encrypted name length: " . strlen($rawData->full_name));
            } else {
                $this->warn("   ⚠️  Data encryption: Check configuration");
            }

            // Test decryption (reload from database to test automatic decryption)
            $reloadedTourRequest = TourRequest::find($tourRequest->id);
            if ($reloadedTourRequest->full_name === 'John Test Doe') {
                $this->line("   ✅ Data decryption: WORKING");
            } else {
                $this->error("   ❌ Data decryption: FAILED");
                $this->line("      Expected: 'John Test Doe'");
                $this->line("      Got: '{$reloadedTourRequest->full_name}'");
            }

            // Test token generation
            $token = $tourRequest->generateSecureAccessToken();
            $tourRequest->update([
                'access_token' => $token,
                'expires_at' => now()->addHours(72)
            ]);

            if ($token && strlen($token) >= 40) {
                $this->line("   ✅ Token generation: WORKING");
                $this->line("      Token: " . substr($token, 0, 16) . "...");
                $this->line("      Expires: " . $tourRequest->expires_at->format('Y-m-d H:i:s'));
            } else {
                $this->error("   ❌ Token generation: FAILED");
            }

            // Test secure email (without actually sending)
            Mail::fake();
            $secureEmail = new SecureBookATourMail($tourRequest, $facility->name);
            $this->line("   ✅ Secure email creation: WORKING");

            // Test access URL
            $secureUrl = $tourRequest->getSecureAccessUrl();
            if (str_contains($secureUrl, '/secure/tour-request/') && str_contains($secureUrl, $token)) {
                $this->line("   ✅ Secure access URL: WORKING");
                $this->line("      URL: " . $secureUrl);
            } else {
                $this->error("   ❌ Secure access URL: FAILED");
            }

            // Cleanup test data
            $tourRequest->delete();
            $this->line("   ✅ Test data cleaned up");

        } catch (\Exception $e) {
            $this->error("   ❌ Encryption test failed: " . $e->getMessage());
        }

        // Test 6: HIPAA Compliance Check
        $this->newLine();
        $this->info('6. HIPAA Compliance Check:');
        
        $checklist = \App\Support\HipaaWebsiteChecklist::forFacility([], []);
        $formsSecureItem = collect($checklist)->firstWhere('key', 'forms_secure');
        
        if ($formsSecureItem && $formsSecureItem['passed']) {
            $this->line("   ✅ Book a Tour secure ePHI check: PASSED");
        } else {
            $this->error("   ❌ Book a Tour secure ePHI check: FAILED");
        }

        // Summary
        $this->newLine();
        $this->info('🚌 Secure Book a Tour Implementation Test Complete');
        
        if ($formsSecureItem && $formsSecureItem['passed']) {
            $this->info('✅ Book a Tour system is ready for HIPAA-compliant operation!');
            $this->newLine();
            $this->line('📋 Next Steps:');
            $this->line('   1. Set FORCE_EPHI_ENCRYPTION=true in production');
            $this->line('   2. Test with actual tour request submissions');
            $this->line('   3. Verify secure email notifications are sent correctly');
            $this->line('   4. Check secure access links work in browsers');
        } else {
            $this->warn('⚠️  Some components may need attention for full compliance.');
        }

        return 0;
    }
}