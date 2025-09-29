<?php

namespace App\Providers;

use App\Models\SchoolSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        // Share school settings data with all Inertia responses
        Inertia::share('schoolSettings', function () {
            return SchoolSettings::first() ?? new SchoolSettings();
        });
    }
}
