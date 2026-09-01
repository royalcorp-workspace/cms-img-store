<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        view()->composer('layouts.partials.sidebar', function ($view) {
            if (Schema::hasTable('menus')) {
                $view->with('menus', Menu::with('children')
                    ->parents()
                    ->orderBy('order')
                    ->get());
            } else {
                $view->with('menus', collect([]));
            }
        });
    }
}
