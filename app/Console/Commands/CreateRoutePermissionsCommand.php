<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Models\Permission;

class CreateRoutePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-permission-routes';

    /**
     * The aliases of the command.
     *
     * @var array<string>
     */
    protected $aliases = ['permissions:generate', 'permission:generate'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan registered routes and create corresponding permission records.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::getRoutes()->getRoutes();
        $count = 0;

        $ignoredPrefixes = [
            'sanctum.',
            'ignition.',
            'livewire.',
            'debugbar.',
            'storage.',
            'up',
        ];

        foreach ($routes as $route) {
            $name = $route->getName();

            if (empty($name)) {
                continue;
            }

            foreach ($ignoredPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    continue 2;
                }
            }

            $permission = Permission::where('name', $name)->first();

            if (is_null($permission)) {
                $parts = explode('.', $name);
                $resource = $parts[0] ?? 'general';
                $action = $parts[1] ?? 'access';
                $group = Str::headline($resource);

                Permission::create([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'guard_name' => 'web',
                    'resource' => $resource,
                    'action' => $action,
                    'group' => $group,
                    'description' => "Hak akses untuk route {$name}",
                    'is_active' => true,
                ]);

                $count++;
            }
        }

        $this->info("Permission routes added successfully. Created {$count} new permissions.");
        return Command::SUCCESS;
    }
}
