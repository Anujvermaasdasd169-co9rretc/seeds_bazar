<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        RateLimiter::for('login', fn ($request) => [
            Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
        ]);
        RateLimiter::for('password-reset', fn ($request) => [
            Limit::perMinute(3)->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
        ]);
        RateLimiter::for('password-change', fn ($request) => [
            Limit::perMinute(5)->by((string) $request->user()?->getAuthIdentifier().'|'.$request->ip()),
        ]);
        RateLimiter::for('verification', fn ($request) => [
            Limit::perMinute(3)->by((string) $request->user()?->getAuthIdentifier().'|'.$request->ip()),
        ]);
        RateLimiter::for('reviews', fn ($request) => [
            Limit::perMinute(5)->by((string) $request->user()?->getAuthIdentifier().'|'.$request->ip()),
        ]);

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('components.storefront-header', function ($view): void {
            if (! $view->offsetExists('navCategories')) {
                $view->with('navCategories', Category::headerTree());
            }

            if (! $view->offsetExists('storefront')) {
                $view->with('storefront', Setting::storefront());
            }
        });
    }
}
