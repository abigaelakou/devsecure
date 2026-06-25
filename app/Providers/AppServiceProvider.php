<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;  
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
          Paginator::useBootstrapFive();
    }
}
