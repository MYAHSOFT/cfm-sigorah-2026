<?php

namespace App\Providers;

use App\Models\Association\Cycle;
use App\Models\Association\Group;
use App\Policies\CFDossierPolicy;
use App\Policies\GroupeSolidePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Group::class, GroupeSolidePolicy::class);
        Gate::policy(Cycle::class, CFDossierPolicy::class);
    }
}
