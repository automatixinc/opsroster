<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

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
        RateLimiter::for('create-tenant', function ($request) {
            if (app()->hasDebugModeEnabled()) {
                return Limit::none();
            } else {
                return [
                    Limit::perMinutes(5, 1),
                    Limit::perMinutes(30, 2),
                    Limit::perHour(3),
                ];
            }
        });
        //
    }
}
