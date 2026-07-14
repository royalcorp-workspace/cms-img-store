<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Skip gate registration during migrations/seeding
        if ($this->app->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            if (count($argv) > 1 && in_array($argv[1], ['migrate', 'migrate:fresh', 'migrate:refresh', 'db:seed', 'db:wipe'])) {
                return;
            }
        }

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $query = DB::table('permissions');
        if (Schema::hasColumn('permissions', 'is_active')) {
            $query->where('is_active', true);
        }
        $permissions = $query->get();

        foreach ($permissions as $permission) {
            Gate::define($permission->name, function ($user) use ($permission) {
                if ($user->hasRole('super-admin')) {
                    return true;
                }

                return $user->hasPermission($permission->name);
            });
        }
    }
}
