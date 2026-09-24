<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

        if (
            app()->environment('production') ||
            env('FORCE_HTTPS', false) ||
            str_starts_with(config('app.url', ''), 'https://') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Global Gate definition for RBAC & @can directives
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
            if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->hasRole('Super Admin'))) {
                return true;
            }
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ? true : null;
            }
            return null;
        });

        view()->composer('layouts.partials.sidebar', function ($view) {
            if (Schema::hasTable('menus')) {
                $view->with('menus', Menu::with(['childs.childs'])
                    ->parents()
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->get());
            } else {
                $view->with('menus', collect([]));
            }
        });
    }
}
