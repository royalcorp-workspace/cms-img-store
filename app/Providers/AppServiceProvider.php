<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (env('APP_ENV') === 'production' || env('FORCE_HTTPS', false)) {
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
