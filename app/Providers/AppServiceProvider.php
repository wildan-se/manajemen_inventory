<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Paksa HTTPS untuk semua URL yang di-generate Laravel di production
        // Ini mencegah mixed content error (http:// asset di halaman https://)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
