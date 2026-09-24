<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Role::withCount(['users', 'admins', 'permissions'])->orderBy('level', 'desc')->orderBy('name', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        $roles = $query->paginate(15)->appends($request->query());
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $totalUsers = DB::table('model_has_roles')->distinct('model_id')->count('model_id');

        return view('pages.roles.index', compact('roles', 'totalRoles', 'totalPermissions', 'totalUsers', 'search'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $menus = Menu::with(['childs' => function ($q) {
            $q->orderBy('order');
        }, 'childs.childs' => function ($q) {
            $q->orderBy('order');
        }])
        ->whereNull('parent_id')
        ->orderBy('order')
        ->get();

        $permissions = Permission::where('is_active', true)->orderBy('group')->orderBy('name')->get();

        return view('pages.roles.create', compact('menus', 'permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'level' => 'nullable|integer|min:1|max:100',
            'permission' => 'nullable|array',
        ]);

        $role = DB::transaction(function () use ($request) {
            $role = Role::create([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
                'level' => $request->input('level', 10),
                'guard_name' => 'web',
                'is_system' => false,
                'is_active' => true,
            ]);

            $permissionsInput = $request->input('permission', []);
            $flattenedPermissions = [];
            foreach ($permissionsInput as $permKey => $permVal) {
                if (is_array($permVal)) {
                    foreach ($permVal as $subVal) {
                        $flattenedPermissions = array_merge($flattenedPermissions, explode('|', (string) $subVal));
                    }
                } else {
                    $flattenedPermissions = array_merge($flattenedPermissions, explode('|', (string) $permVal));
                }
            }

            $role->syncPermissions(array_unique(array_filter($flattenedPermissions)));

            return $role;
        });

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' berhasil dibuat beserta hak aksesnya.");
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $menus = Menu::with(['childs' => function ($q) {
            $q->orderBy('order');
        }, 'childs.childs' => function ($q) {
            $q->orderBy('order');
        }])
        ->whereNull('parent_id')
        ->orderBy('order')
        ->get();

        return view('pages.roles.show', compact('role', 'menus', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $menus = Menu::with(['childs' => function ($q) {
            $q->orderBy('order');
        }, 'childs.childs' => function ($q) {
            $q->orderBy('order');
        }])
        ->whereNull('parent_id')
        ->orderBy('order')
        ->get();

        return view('pages.roles.edit', compact('role', 'menus', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'level' => 'nullable|integer|min:1|max:100',
            'permission' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $role) {
            $role->update([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
                'level' => $request->input('level', $role->level),
            ]);

            $permissionsInput = $request->input('permission', []);
            $flattenedPermissions = [];
            foreach ($permissionsInput as $permKey => $permVal) {
                if (is_array($permVal)) {
                    foreach ($permVal as $subVal) {
                        $flattenedPermissions = array_merge($flattenedPermissions, explode('|', (string) $subVal));
                    }
                } else {
                    $flattenedPermissions = array_merge($flattenedPermissions, explode('|', (string) $permVal));
                }
            }

            $role->syncPermissions(array_unique(array_filter($flattenedPermissions)));
        });

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system || in_array($role->slug, ['admin', 'super-admin', 'superadmin'])) {
            return redirect()->route('roles.index')->with('error', 'Role sistem tidak dapat dihapus.');
        }

        $name = $role->name;
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role '{$name}' berhasil dihapus.");
    }
}
