<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
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
        // Passport routes are auto-registered in recent versions
        //

        Passport::tokensCan([
            'admin' => 'Admin access',
            'alumni' => 'Alumni access',
        ]);

        Passport::setDefaultScope([
            'alumni',
        ]);
    }
}
