<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('login');
        }

        $roles = $admin->roles()->with('permissions')->get();
        $isSuperAdmin = method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin();
        $permissions = $admin->getAllPermissions();
        $permissionsCount = $permissions->count();

        return view('pages.profile.show', compact('admin', 'roles', 'isSuperAdmin', 'permissions', 'permissionsCount'));
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:user_admin,email,' . $admin->id . ',id',
            'phone' => 'nullable|string|max:30',
        ]);

        $admin->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.min'              => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $admin->password_hash)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak sesuai.'])->withInput();
        }

        $admin->password = $request->password;
        $admin->save();

        return redirect()->route('profile.show')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
