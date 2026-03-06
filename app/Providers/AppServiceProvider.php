<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
    }
}
