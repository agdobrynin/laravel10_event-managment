<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const RATE_LIMITING_EMAIL_NOTIFY = 'user-notify-event';
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
        RateLimiter::for(self::RATE_LIMITING_EMAIL_NOTIFY, function (object $job) {
            return Limit::perMinute(config('app.user_notify.per_minute'));
        });
    }
}
