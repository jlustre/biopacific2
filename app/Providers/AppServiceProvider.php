<?php

namespace App\Providers;

use App\Support\MemberPortalLayout;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Models\PreEmploymentApplication;
use App\Policies\PreEmploymentApplicationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $helpers = app_path('helpers.php');
        if (is_file($helpers)) {
            require_once $helpers;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(PreEmploymentApplication::class, PreEmploymentApplicationPolicy::class);
        Gate::policy(\App\Models\Gallery::class, \App\Policies\FacilityGalleryPolicy::class);

        Route::bind('submission', fn (string $value) => \App\Models\WebmasterContact::findOrFail($value));
        Route::bind('helpRequest', fn (string $value) => \App\Models\PortalHelpRequest::findOrFail($value));
        Route::bind('portalHelpRequest', fn (string $value) => \App\Models\PortalHelpRequest::findOrFail($value));

        View::composer('layouts.dashboard', function ($view) {
            // Always use the member portal chrome (sidebar/topbar). Legacy admin nav is retired.
            $view->with('useMemberPortalSidebar', true);

            if (auth()->check()) {
                $view->with(MemberPortalLayout::variablesForView());
            }
        });

        View::composer('layouts.member-portal', function ($view) {
            if (auth()->check()) {
                $view->with(MemberPortalLayout::variablesForView());
            }
        });
    }
}
