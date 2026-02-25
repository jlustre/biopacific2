<?php

namespace App\Providers;

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
        // Register the JobOpeningsForm component
        // Using ::class to ensure proper namespace resolution
        Livewire::component('job-openings-form', \App\Livewire\JobOpeningsForm::class);
        
        // Register policies
        Gate::policy(PreEmploymentApplication::class, PreEmploymentApplicationPolicy::class);
    }
}
