<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\JobApplication;
use App\Policies\JobApplicationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JobApplication::class => JobApplicationPolicy::class,
        \App\Models\PreEmploymentApplication::class => \App\Policies\PreEmploymentApplicationPolicy::class,
        \App\Models\Backup::class => \App\Policies\BackupPolicy::class,
        \App\Models\PersonalTask::class => \App\Policies\PersonalTaskPolicy::class,
        // ... other policies ...
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
