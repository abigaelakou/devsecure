<?php

namespace App\Providers;

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
         // Laravel ne joue que les migrations landlord
        // quand on fait php artisan migrate
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}
