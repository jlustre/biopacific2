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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(PreEmploymentApplication::class, PreEmploymentApplicationPolicy::class);

        Route::bind('submission', fn (string $value) => \App\Models\WebmasterContact::findOrFail($value));
        Route::bind('helpRequest', fn (string $value) => \App\Models\PortalHelpRequest::findOrFail($value));
        Route::bind('portalHelpRequest', fn (string $value) => \App\Models\PortalHelpRequest::findOrFail($value));

        View::composer('layouts.dashboard', function ($view) {
            $useMemberPortalSidebar = MemberPortalLayout::shouldUseForCurrentRequest();
            $view->with('useMemberPortalSidebar', $useMemberPortalSidebar);

            if ($useMemberPortalSidebar) {
                $view->with(MemberPortalLayout::variablesForView());
            } elseif (auth()->check()) {
                $user = auth()->user();
                $view->with([
                    'selectedFacility' => \App\Support\SelectedFacility::model(),
                    'selectedFacilityId' => \App\Support\SelectedFacility::id(),
                    'selectedFacilityName' => \App\Support\SelectedFacility::name(),
                    'canChooseFacility' => \App\Support\SelectedFacility::userCanChooseFacility($user),
                ]);
            }
        });

        View::composer('layouts.member-portal', function ($view) {
            if (auth()->check()) {
                $view->with(MemberPortalLayout::variablesForView());
            }
        });
    }
}
