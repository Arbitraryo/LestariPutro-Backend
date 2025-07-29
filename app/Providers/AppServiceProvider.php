<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
    // public function boot(): void
    // {
    //     if ($this->app->environment('production')) {
    //         URL::forceScheme('https');
    //     }
    // }
    public function boot()
    {
    if (env('APP_ENV') !== 'local') {
        $this->app['request']->server->set('HTTPS', true);
    }

    Schema::defaultStringLength(191);
    }

}