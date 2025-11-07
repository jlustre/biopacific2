<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use App\Helpers\SecureAssetHelper;

class TestHttpsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:https {--test-env=local : Environment to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test HTTPS configuration and secure asset helpers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $env = $this->option('test-env');
        
        $this->info("Testing HTTPS configuration for environment: {$env}");
        $this->newLine();
        
        // Temporarily set environment for testing
        $originalEnv = app()->environment();
        if ($env !== $originalEnv) {
            app()->detectEnvironment(function () use ($env) {
                return $env;
            });
        }
        
        // Test secure asset helper
        $assetUrl = SecureAssetHelper::secureAsset('images/test.jpg');
        $this->line("Secure Asset URL: {$assetUrl}");
        
        // Test secure URL helper
        $url = SecureAssetHelper::secureUrl('/test-page');
        $this->line("Secure URL: {$url}");
        
        // Test config values
        $appUrl = config('app.url');
        $forceHttps = config('app.force_https');
        $this->line("App URL: {$appUrl}");
        $this->line("Force HTTPS: " . ($forceHttps ? 'true' : 'false'));
        
        // Test URL facade
        $routeUrl = url('/home');
        $this->line("Laravel URL: {$routeUrl}");
        
        $this->newLine();
        
        // Check for HTTPS enforcement
        if ($env === 'production') {
            if (str_starts_with($assetUrl, 'https://')) {
                $this->info('✅ Assets are being served over HTTPS in production');
            } else {
                $this->error('❌ Assets are NOT being served over HTTPS in production');
            }
            
            if (str_starts_with($url, 'https://')) {
                $this->info('✅ URLs are being generated with HTTPS in production');
            } else {
                $this->error('❌ URLs are NOT being generated with HTTPS in production');
            }
        } else {
            $this->line('ℹ️  HTTPS enforcement is disabled in non-production environment');
        }
        
        // Reset environment
        if ($env !== $originalEnv) {
            app()->detectEnvironment(function () use ($originalEnv) {
                return $originalEnv;
            });
        }
        
        return 0;
    }
}
