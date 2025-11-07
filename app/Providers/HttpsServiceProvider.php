<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use App\Helpers\SecureAssetHelper;

class HttpsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs in production
        if ($this->app->environment('production') || config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Register Blade directives for secure assets
        Blade::directive('secureAsset', function ($expression) {
            return "<?php echo App\Helpers\SecureAssetHelper::secureAsset($expression); ?>";
        });

        Blade::directive('secureUrl', function ($expression) {
            return "<?php echo App\Helpers\SecureAssetHelper::secureUrl($expression); ?>";
        });

        Blade::directive('secureRoute', function ($expression) {
            return "<?php echo App\Helpers\SecureAssetHelper::secureRoute($expression); ?>";
        });
    }
}