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
    public function boot(): void
    {
        //
        // Ép Laravel tạo link CSS/JS/Ảnh bằng HTTPS để không bị vỡ giao diện
        if($this->app->environment('production') || true) {
            URL::forceScheme('https');
        }
    }
}
