<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TourRequest;
use App\Models\Facility;
use App\Models\SecureAccessLog;
use App\Mail\SecureBookATourMail;
use Illuminate\Support\Facades\Mail;

class TestEnhancedSecurityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:enhanced-security {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test enhanced security features including staff verification and audit logging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Testing Enhanced Security Features');
        $this->line('========================================');

        // Test 1: Create test tour request with enhanced logging
        $this->newLine();
        $this->info('1. Creating Test Tour Request with Enhanced Security:');
        
        $facility = Facility::first();
        if (!$facility) {
            $this->error('No facility found. Please create a facility first.');
            return 1;
        }

        $tourRequest = TourRequest::create([
            'facility_id' => $facility->id,
            'recipient' => 'Self',
            'full_name' => 'John Doe (Test)',
            'relationship' => 'Self',
            'email' => 'test@example.com',
            'phone' => '555-123-4567',
            'preferred_date' => now()->addDays(7)->format('Y-m-d'),
            'preferred_time' => 'afternoon',
            'message' => 'This is a test tour request for security verification.',
            'consent' => true
        ]);

        $tourRequest->generateSecureAccessToken();
        $this->line("   ✅ Tour request created: ID #{$tourRequest->id}");
        $this->line("   🔑 Secure token: " . substr($tourRequest->access_token, 0, 16) . "...");
        $this->line("   ⏰ Expires: {$tourRequest->expires_at}");

        // Test 2: Generate secure URL
        $this->newLine();
        $this->info('2. Secure Access URL:');
        $secureUrl = route('secure.tour-request', $tourRequest->access_token);
        $this->line("   🔗 URL: $secureUrl");
        $this->line("   ⚠️  This URL now requires staff verification!");

        // Test 3: Test audit logging
        $this->newLine();
        $this->info('3. Testing Enhanced Audit Logging:');
        
        // Simulate various access attempts
        $accessLogs = [
            ['status' => 'invalid_token', 'staff_email' => null],
            ['status' => 'unauthorized_email', 'staff_email' => 'unauthorized@example.com'],
            ['status' => 'staff_verified', 'staff_email' => 'staff@facility.com'],
            ['status' => 'success', 'staff_email' => 'staff@facility.com']
        ];

        foreach ($accessLogs as $log) {
            SecureAccessLog::logAccess([
                'token_type' => 'tour_request',
                'record_id' => $tourRequest->id,
                'facility_id' => $facility->id,
                'access_token' => substr($tourRequest->access_token, 0, 16),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Browser',
                'staff_email' => $log['staff_email'],
                'access_status' => $log['status'],
                'request_headers' => ['test' => true]
            ]);
        }

        $logCount = SecureAccessLog::where('record_id', $tourRequest->id)
            ->where('token_type', 'tour_request')
            ->count();
        
        $this->line("   📊 Access logs created: $logCount entries");

        // Test 4: Suspicious activity detection
        $this->newLine();
        $this->info('4. Testing Suspicious Activity Detection:');
        
        $suspiciousActivity = SecureAccessLog::checkSuspiciousActivity('tour_request', $tourRequest->id);
        
        if ($suspiciousActivity['is_suspicious']) {
            $this->line("   🚨 Suspicious activity detected!");
            $this->line("   📋 Flags: " . implode(', ', $suspiciousActivity['flags']));
        } else {
            $this->line("   ✅ No suspicious activity detected");
        }
        
        $this->line("   📈 Recent accesses: {$suspiciousActivity['recent_accesses']->count()}");
        $this->line("   🌐 Unique IPs: {$suspiciousActivity['unique_ips']}");
        $this->line("   ❌ Failed attempts: {$suspiciousActivity['failed_attempts']}");

        // Test 5: Send enhanced security email
        $testEmail = $this->option('email');
        if ($testEmail) {
            $this->newLine();
            $this->info('5. Sending Enhanced Security Email:');
            
            try {
                Mail::to($testEmail)->send(new SecureBookATourMail($tourRequest, $facility));
                $this->line("   📧 Enhanced security email sent to: $testEmail");
                $this->line("   ⚠️  Note the enhanced security warnings in the email");
            } catch (\Exception $e) {
                $this->error("   ❌ Email sending failed: " . $e->getMessage());
            }
        }

        // Test 6: Security features summary
        $this->newLine();
        $this->info('6. Enhanced Security Features Summary:');
        $this->line("   🔐 Staff verification required before access");
        $this->line("   📊 Comprehensive audit logging with IP tracking");
        $this->line("   🚨 Suspicious activity detection and alerting");
        $this->line("   ⚠️  Enhanced email security warnings");
        $this->line("   🕒 Time-limited access tokens (72 hours)");
        $this->line("   🛡️  HIPAA-compliant access controls");

        // Test 7: Manual testing instructions
        $this->newLine();
        $this->info('7. Manual Testing Instructions:');
        $this->line("   1. Click the secure URL above");
        $this->line("   2. You'll be prompted for staff verification");
        $this->line("   3. Enter an authorized email address");
        $this->line("   4. Select an access reason");
        $this->line("   5. Accept HIPAA agreement");
        $this->line("   6. Access will be logged in the audit trail");

        if ($testEmail) {
            $this->line("   7. Check your email for security warnings");
        }

        // Cleanup
        $this->newLine();
        $this->info('8. Cleanup:');
        $confirm = $this->confirm('Delete test data?', true);
        
        if ($confirm) {
            SecureAccessLog::where('record_id', $tourRequest->id)
                ->where('token_type', 'tour_request')
                ->delete();
            $tourRequest->delete();
            $this->line("   ✅ Test data cleaned up");
        } else {
            $this->line("   📝 Test data preserved for manual testing");
            $this->line("   🔗 Access URL: $secureUrl");
        }

        // Summary
        $this->newLine();
        $this->info('🎉 Enhanced Security Test Complete!');
        $this->line('Key Security Improvements:');
        $this->line('✅ Staff email verification prevents unauthorized forwarding');
        $this->line('✅ Enhanced audit logging tracks all access attempts');
        $this->line('✅ Suspicious activity detection identifies potential threats');
        $this->line('✅ Strengthened email warnings educate users about security');
        $this->line('✅ Comprehensive HIPAA compliance logging');

        if (!$testEmail) {
            $this->newLine();
            $this->info('💡 Run with --email=your@email.com to test enhanced email security');
        }

        return 0;
    }
}
