<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $groupFilter = $request->query('group');

        $query = Permission::query()->orderBy('group')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('resource', 'ilike', "%{$search}%")
                  ->orWhere('action', 'ilike', "%{$search}%");
            });
        }

        if ($groupFilter) {
            $query->where('group', $groupFilter);
        }

        $permissions = $query->paginate(25)->appends($request->query());
        $groups = Permission::distinct()->pluck('group')->filter()->values();
        $totalPermissions = Permission::count();
        $activePermissions = Permission::where('is_active', true)->count();

        return view('pages.permissions.index', compact('permissions', 'groups', 'totalPermissions', 'activePermissions', 'search', 'groupFilter'));
    }

    public function sync()
    {
        try {
            Artisan::call('permission:create-permission-routes');
            $output = Artisan::output();
            return redirect()->route('permissions.index')->with('success', "Sinkronisasi route permissions berhasil. {$output}");
        } catch (\Exception $e) {
            return redirect()->route('permissions.index')->with('error', "Gagal melakukan sinkronisasi: {$e->getMessage()}");
        }
    }
}
