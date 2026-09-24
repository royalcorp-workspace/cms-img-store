<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of CMS Admin users.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Admin::with('roles')->where('deleted', false)->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->appends($request->query());
        $roles = Role::where('is_active', true)->orderBy('name')->get();

        return view('pages.users.index', compact('users', 'roles', 'search'));
    }

    /**
     * Store a newly created CMS admin user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:user_admin,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::transaction(function () use ($request) {
            $admin = Admin::create([
                'id' => (string) Str::uuid(),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password_hash' => bcrypt($request->input('password')),
                'is_active' => true,
                'email_verified' => true,
                'email_verified_at' => now(),
                'creator' => Auth::guard('admin')->user()?->name ?? 'Admin',
                'editor' => Auth::guard('admin')->user()?->name ?? 'Admin',
            ]);

            $role = Role::find($request->input('role_id'));
            if ($role) {
                $admin->assignRole($role);
            }
        });

        return redirect()->route('users.index')->with('success', "Pengguna admin '{$request->name}' berhasil ditambahkan.");
    }

    /**
     * Update the specified CMS admin user.
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:user_admin,email,' . $admin->id,
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $admin) {
            $data = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'is_active' => $request->boolean('is_active', true),
                'editor' => Auth::guard('admin')->user()?->name ?? 'Admin',
            ];

            if ($request->filled('password')) {
                $data['password_hash'] = bcrypt($request->input('password'));
            }

            $admin->update($data);

            $role = Role::find($request->input('role_id'));
            if ($role) {
                $admin->syncRoles([$role]);
            }
        });

        return redirect()->route('users.index')->with('success', "Data admin '{$admin->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified CMS admin user.
     */
    public function destroy($id)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if ($currentAdmin && $currentAdmin->id === $id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $admin = Admin::findOrFail($id);
        $name = $admin->name;

        DB::transaction(function () use ($admin) {
            $admin->roles()->detach();
            $admin->update(['deleted' => true, 'is_active' => false]);
        });

        return redirect()->route('users.index')->with('success', "Pengguna admin '{$name}' berhasil dinonaktifkan.");
    }
}
